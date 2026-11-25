<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\Pembayaran;
use App\Models\TagihanPajak;
use App\Models\User;
use App\Models\WajibPajakProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'services.midtrans.is_production' => false,
            'services.midtrans.server_key' => 'SB-Mid-server-test-key',
            'services.midtrans.client_key' => 'SB-Mid-client-test-key',
        ]);
    }

    public function test_wajib_pajak_can_create_snap_token_and_sync_settlement(): void
    {
        [$owner, $other, $tagihan] = $this->seedApprovedUnpaidBill();

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-abc-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token-abc-123',
            ], 201),
            'https://api.sandbox.midtrans.com/v2/*/status' => Http::response([
                'order_id' => 'PAJAKIN-ORDER',
                'transaction_status' => 'settlement',
                'payment_type' => 'qris',
                'fraud_status' => 'accept',
                'gross_amount' => '500000.00',
            ], 200),
            '*' => Http::response([], 503),
        ]);

        // IDOR check: another user cannot request token or sync status
        $this->actingAs($other)
            ->postJson(route('wp.tagihan.midtrans.token', $tagihan))
            ->assertForbidden();

        $tokenRes = $this->actingAs($owner)
            ->postJson(route('wp.tagihan.midtrans.token', $tagihan))
            ->assertOk()
            ->assertJsonPath('data.snap_token', 'snap-token-abc-123');

        $orderId = (string) $tokenRes->json('data.order_id');
        $this->assertNotEmpty($orderId);
        $this->assertSame($orderId, $tagihan->fresh()->midtrans_order_id);

        $this->actingAs($owner)
            ->postJson(route('wp.tagihan.midtrans.sync', $tagihan))
            ->assertOk()
            ->assertJsonPath('status', 'lunas');

        $this->assertTrue($tagihan->fresh()->isLunas());
        $this->assertDatabaseHas('pembayaran', [
            'tagihan_id' => $tagihan->id,
            'kode_transaksi' => $orderId,
            'metode_pembayaran' => 'Midtrans (QRIS)',
        ]);
    }

    public function test_midtrans_webhook_verifies_sha512_signature_and_settles_bill(): void
    {
        [, , $tagihan] = $this->seedApprovedUnpaidBill();
        $orderId = 'PAJAKIN-WEBHOOK-001';
        $tagihan->update(['midtrans_order_id' => $orderId, 'midtrans_status' => 'pending']);

        Http::fake(['*' => Http::response([], 503)]);

        $statusCode = '200';
        $grossAmount = '500000.00';

        // Invalid signature rejected with 403
        $this->postJson('/api/midtrans/callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'signature_key' => 'invalid-signature',
        ])->assertForbidden();

        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . 'SB-Mid-server-test-key');

        $this->postJson('/api/midtrans/callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
            'fraud_status' => 'accept',
            'signature_key' => $validSignature,
        ])->assertOk()->assertJsonPath('status', 'lunas');

        $this->assertTrue($tagihan->fresh()->isLunas());
        $this->assertSame(1, Pembayaran::query()->where('tagihan_id', $tagihan->id)->count());
    }

    /**
     * @return array{0: User, 1: User, 2: TagihanPajak}
     */
    private function seedApprovedUnpaidBill(): array
    {
        $owner = User::query()->create([
            'name' => 'Pemilik Midtrans',
            'email' => 'owner.midtrans@pajakin.local',
            'password' => Hash::make('password123'),
            'role' => 'wajib_pajak',
        ]);
        WajibPajakProfile::query()->create([
            'user_id' => $owner->id,
            'nik_npwp' => '3273010101908888',
            'jenis_entitas' => 'pribadi',
            'provinsi' => 'JAWA BARAT',
            'kota' => 'KOTA BANDUNG',
            'alamat' => 'Jl. Braga No. 1',
            'no_telepon' => '081234567890',
        ]);
        $other = User::query()->create([
            'name' => 'User Lain',
            'email' => 'other.midtrans@pajakin.local',
            'password' => Hash::make('password123'),
            'role' => 'wajib_pajak',
        ]);

        $kendaraan = Kendaraan::query()->create([
            'user_id' => $owner->id,
            'jenis_kendaraan' => 'motor',
            'plat_nomor' => 'D 9911 MID',
            'merek' => 'Honda',
            'model_kendaraan' => 'Vario 160',
            'tahun_pembuatan' => 2023,
            'kapasitas_cc' => 160,
            'njkb' => 21000000,
            'provinsi' => 'JAWA BARAT',
            'jatuh_tempo_stnk' => now()->addMonth()->toDateString(),
            'urutan_kepemilikan' => 1,
            'status_verifikasi' => 'approved',
        ]);

        $tagihan = TagihanPajak::query()->create([
            'kendaraan_id' => $kendaraan->id,
            'tahun_pajak' => (int) now()->format('Y'),
            'jatuh_tempo' => now()->addMonth()->toDateString(),
            'pkb_pokok' => 235200,
            'pajak_progresif' => 0,
            'opsen_pkb' => 155232,
            'swdkllj' => 35000,
            'denda_keterlambatan' => 0,
            'total_tagihan' => 425432,
            'tarif_sumber' => 'api',
            'status' => 'belum_lunas',
        ]);

        return [$owner, $other, $tagihan];
    }
}
