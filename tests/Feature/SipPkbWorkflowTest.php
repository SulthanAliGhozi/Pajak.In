<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BerkasKendaraan;
use App\Models\Kendaraan;
use App\Models\Pembayaran;
use App\Models\TagihanPajak;
use App\Models\User;
use App\Models\WajibPajakProfile;
use App\Services\DocumentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SipPkbWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Http::fake(['*' => Http::response([], 503)]);
    }

    public function test_public_home_kalkulator_and_security_headers(): void
    {
        $home = $this->get('/');
        $home->assertOk();
        $home->assertHeader('X-Content-Type-Options', 'nosniff');
        $home->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $home->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $this->get('/kalkulator')->assertOk();

        $this->postJson('/kalkulator/hitung', [
            'jenis_kendaraan' => 'motor',
            'jenis_entitas' => 'pribadi',
            'provinsi' => 'JAWA BARAT',
            'merek' => 'Honda',
            'tahun_pembuatan' => 2023,
            'kapasitas_cc' => 160,
            'njkb' => 20000000,
            'urutan_kepemilikan' => 1,
            'jatuh_tempo_stnk' => now()->addMonth()->toDateString(),
        ])->assertOk()->assertJsonStructure(['data' => ['pkb_pokok', 'swdkllj', 'total_tagihan'], 'api_source']);

        $this->post('/kalkulator/hitung', [
            'jenis_kendaraan' => 'mobil',
            'jenis_entitas' => 'usaha',
            'provinsi' => 'DKI JAKARTA',
            'merek' => 'Toyota',
            'tahun_pembuatan' => 2022,
            'kapasitas_cc' => 1500,
            'urutan_kepemilikan' => 2,
            'jatuh_tempo_stnk' => now()->addMonth()->toDateString(),
        ])->assertOk();
    }

    public function test_registration_and_portal_role_login_enforcement(): void
    {
        $this->post('/register', [
            'name' => 'Siti Aminah',
            'email' => 'siti@pajakin.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik_npwp' => '3273011508900099',
            'jenis_entitas' => 'pribadi',
            'provinsi' => 'JAWA BARAT',
            'kota' => 'KOTA BANDUNG',
            'alamat' => 'Jl. Asia Afrika No. 10',
            'no_telepon' => '081234567899',
        ])->assertRedirect(route('wp.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('wajib_pajak_profiles', ['nik_npwp' => '3273011508900099']);

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();

        $petugas = $this->createUser('petugas@pajakin.local', 'petugas');
        $wp = User::query()->where('email', 'siti@pajakin.local')->firstOrFail();

        $this->from('/login')->post('/login', [
            'email' => $petugas->email,
            'password' => 'password123',
            'portal' => 'wajib_pajak',
        ])->assertSessionHasErrors('portal');
        $this->assertGuest();

        $this->from('/login')->post('/login', [
            'email' => $wp->email,
            'password' => 'password123',
            'portal' => 'petugas',
        ])->assertSessionHasErrors('portal');
        $this->assertGuest();

        $this->post('/login', [
            'email' => $petugas->email,
            'password' => 'password123',
            'portal' => 'petugas',
        ])->assertRedirect(route('petugas.dashboard'));
    }

    public function test_rbac_and_idor_protection_on_routes_vehicles_and_private_berkas(): void
    {
        Storage::fake('local');

        $petugas = $this->createUser('petugas.rbac@pajakin.local', 'petugas');
        $wpA = $this->createUser('wpa@pajakin.local', 'wajib_pajak', '3273010101900001');
        $wpB = $this->createUser('wpb@pajakin.local', 'wajib_pajak', '3273010101900002');

        $kendaraanB = Kendaraan::query()->create([
            'user_id' => $wpB->id,
            'jenis_kendaraan' => 'motor',
            'plat_nomor' => 'B 4321 IDOR',
            'merek' => 'Yamaha',
            'model_kendaraan' => 'NMAX',
            'tahun_pembuatan' => 2024,
            'kapasitas_cc' => 155,
            'njkb' => 25000000,
            'provinsi' => 'DKI JAKARTA',
            'jatuh_tempo_stnk' => now()->addMonths(2)->toDateString(),
            'urutan_kepemilikan' => 1,
            'status_verifikasi' => 'pending',
        ]);

        $berkasB = (new DocumentStorageService())->store(
            $kendaraanB,
            UploadedFile::fake()->image('ktp-wpb.png'),
            'ktp',
        );

        $this->actingAs($wpA)->get('/petugas/dashboard')->assertForbidden();
        $this->actingAs($wpA)->get('/petugas/verifikasi')->assertForbidden();
        $this->actingAs($petugas)->get('/wajib-pajak/dashboard')->assertForbidden();

        $this->actingAs($wpA)->get(route('wp.kendaraan.show', $kendaraanB))->assertForbidden();
        $this->actingAs($wpA)->get(route('berkas.show', $berkasB))->assertForbidden();
        $this->actingAs($wpB)->get(route('berkas.show', $berkasB))->assertOk();
        $this->actingAs($petugas)->get(route('berkas.show', $berkasB))->assertOk();
    }

    public function test_full_end_to_end_vehicle_verification_payment_pdf_and_double_pay_block(): void
    {
        Storage::fake('local');

        $petugas = $this->createUser('petugas.e2e@pajakin.local', 'petugas');
        $wp = $this->createUser('wp.e2e@pajakin.local', 'wajib_pajak', '3273010101900009');

        $storeResponse = $this->actingAs($wp)->post(route('wp.kendaraan.store'), [
            'jenis_kendaraan' => 'mobil',
            'plat_nomor' => 'd 8888 e2e',
            'merek' => 'Toyota',
            'model_kendaraan' => 'Avanza',
            'tahun_pembuatan' => 2023,
            'kapasitas_cc' => 1500,
            'provinsi' => 'JAWA BARAT',
            'jatuh_tempo_stnk' => now()->addMonth()->toDateString(),
            'file_ktp' => UploadedFile::fake()->image('ktp.jpg'),
            'file_stnk' => UploadedFile::fake()->create('stnk.pdf', 120, 'application/pdf'),
        ]);

        $kendaraan = Kendaraan::query()->where('plat_nomor', 'D 8888 E2E')->firstOrFail();
        $storeResponse->assertRedirect(route('wp.kendaraan.show', $kendaraan));
        $this->assertSame('pending', $kendaraan->status_verifikasi);
        $this->assertSame(2, BerkasKendaraan::query()->where('kendaraan_id', $kendaraan->id)->count());

        $this->actingAs($petugas)->post(route('petugas.verifikasi.approve', $kendaraan), [
            'njkb' => 190000000,
            'catatan_petugas' => 'Dokumen valid dan sesuai.',
        ])->assertRedirect(route('petugas.verifikasi.show', $kendaraan));
        $tagihan = TagihanPajak::query()->where('kendaraan_id', $kendaraan->id)->firstOrFail();
        $this->assertSame('belum_lunas', $tagihan->status);
        $this->actingAs($wp)->post(route('wp.tagihan.bayar', $tagihan), ['metode_pembayaran' => 'QRIS'])
            ->assertRedirect(route('wp.tagihan.show', $tagihan));
        $pembayaran = Pembayaran::query()->where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->assertTrue($tagihan->fresh()->isLunas());
        $pdfResponse = $this->actingAs($wp)->get(route('wp.pembayaran.etbpkp', $pembayaran));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('Content-Type'));
        $this->actingAs($wp)
            ->from(route('wp.tagihan.show', $tagihan))
            ->post(route('wp.tagihan.bayar', $tagihan), ['metode_pembayaran' => 'QRIS'])
            ->assertRedirect(route('wp.tagihan.show', $tagihan))
            ->assertSessionHasErrors('payment');
        $this->assertSame(1, Pembayaran::query()->where('tagihan_id', $tagihan->id)->count());
    }

    private function createUser(string $email, string $role, ?string $nikNpwp = null): User
    {
        $user = User::query()->create([
            'name' => $role === 'petugas' ? 'Petugas Test' : 'Wajib Pajak Test',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);

        if ($role === 'wajib_pajak') {
            WajibPajakProfile::query()->create([
                'user_id' => $user->id,
                'nik_npwp' => $nikNpwp ?? '327301'.random_int(1000000000, 9999999999),
                'jenis_entitas' => 'pribadi',
                'provinsi' => 'JAWA BARAT',
                'kota' => 'KOTA BANDUNG',
                'alamat' => 'Jl. Merdeka No. 1',
                'no_telepon' => '08123456789',
            ]);
        }

        return $user;
    }
}
