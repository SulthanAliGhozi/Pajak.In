<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\TaxReferenceGatewayInterface;
use App\Domain\Kendaraan\AbstractKendaraan;
use App\Domain\Kendaraan\KendaraanFactory;
use App\Domain\Kendaraan\Mobil;
use App\Domain\Kendaraan\Motor;
use App\Domain\Rules\PribadiTaxRule;
use App\Domain\Rules\UsahaTaxRule;
use App\Exceptions\AksesBerkasDitolakException;
use App\Exceptions\DokumenBelumTerverifikasiException;
use App\Exceptions\TagihanSudahLunasException;
use App\Models\Kendaraan;
use App\Models\User;
use App\Models\WajibPajakProfile;
use App\Services\DocumentStorageService;
use App\Services\HttpTaxReferenceClient;
use App\Services\LocalFallbackTaxClient;
use App\Services\PaymentService;
use App\Services\PdfReceiptGenerator;
use App\Services\RegionApiClient;
use App\Services\ResilientTaxReferenceGateway;
use App\Services\TaxCalculatorService;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class OopTaxDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_polymorphic_motor_and_mobil_entities_and_encapsulated_state(): void
    {
        $motor = KendaraanFactory::fromArray([
            'jenis_kendaraan' => 'motor',
            'plat_nomor' => 'D 1111 AA',
            'merek' => 'Honda',
            'model_kendaraan' => 'Beat',
            'tahun_pembuatan' => 2023,
            'kapasitas_cc' => 110,
            'njkb' => 15000000,
            'provinsi' => 'JAWA BARAT',
            'jatuh_tempo_stnk' => '2026-12-01',
        ]);
        $mobil = KendaraanFactory::fromArray([
            'jenis_kendaraan' => 'mobil',
            'plat_nomor' => 'B 2222 BB',
            'merek' => 'Toyota',
            'model_kendaraan' => 'Avanza',
            'tahun_pembuatan' => 2022,
            'kapasitas_cc' => 1500,
            'njkb' => 180000000,
            'provinsi' => 'DKI JAKARTA',
            'jatuh_tempo_stnk' => '2026-12-01',
        ]);

        $this->assertInstanceOf(AbstractKendaraan::class, $motor);
        $this->assertInstanceOf(Motor::class, $motor);
        $this->assertInstanceOf(Mobil::class, $mobil);
        $this->assertSame(35000.0, $motor->hitungSwdkllj());
        $this->assertSame(143000.0, $mobil->hitungSwdkllj());
        $this->assertSame(262500.0, $motor->hitungPajakPokok(0.0175));
        $this->assertSame(3600000.0, $mobil->hitungPajakPokok(0.02));

        $motor->approve(99, 'Tervalidasi');
        $this->assertTrue($motor->isApproved());
        $this->assertSame(99, $motor->getVerifiedBy());

        $this->expectException(InvalidArgumentException::class);
        $mobil->reject(99, '   ');
    }

    public function test_tax_rule_strategies_and_holiday_weekend_grace_with_penalty_cap(): void
    {
        $tarif = (new LocalFallbackTaxClient())->getTarifByProvinsi('JAWA BARAT');
        $holidays = [['tanggal' => '2026-08-17', 'nama' => 'Kemerdekaan RI', 'kategori' => 'nasional']];

        // 2026-08-15 is Saturday -> 2026-08-16 Sunday -> 2026-08-17 Holiday -> effective due 2026-08-18
        $mobilPribadi = KendaraanFactory::fromArray([
            'jenis_kendaraan' => 'mobil',
            'plat_nomor' => 'D 9999 CC',
            'merek' => 'Toyota',
            'model_kendaraan' => 'Innova',
            'tahun_pembuatan' => 2022,
            'kapasitas_cc' => 2000,
            'njkb' => 200000000,
            'provinsi' => 'JAWA BARAT',
            'jatuh_tempo_stnk' => '2026-08-15',
            'urutan_kepemilikan' => 2,
        ]);

        $pribadiRule = new PribadiTaxRule();
        $onGraceDay = $pribadiRule->hitungPajak($mobilPribadi, $tarif, new DateTimeImmutable('2026-08-18'), $holidays);
        $this->assertSame('2026-08-18', $onGraceDay['effective_due_date']);
        $this->assertSame(0, $onGraceDay['bulan_terlambat']);
        $this->assertSame(0.0, $onGraceDay['denda_keterlambatan']);
        $this->assertSame(2240000.0, $onGraceDay['pkb_pokok']);
        $this->assertSame(1000000.0, $onGraceDay['pajak_progresif']);
        $this->assertSame(round((2240000 + 1000000) * 0.66, 2), $onGraceDay['opsen_pkb']);
        $this->assertSame(5521400.0, $onGraceDay['total_tagihan']);

        // 30 months overdue must cap at 24 months
        $veryLate = $pribadiRule->hitungPajak($mobilPribadi, $tarif, new DateTimeImmutable('2029-03-01'), $holidays);
        $this->assertSame(24, $veryLate['bulan_terlambat']);
        $this->assertSame(round((2240000 + 1000000) * 0.02 * 24, 2), $veryLate['denda_keterlambatan']);

        // Usaha strategy applies flat rate and zero progressive tax regardless of ownership order
        $usahaRule = new UsahaTaxRule();
        $usahaResult = $usahaRule->hitungPajak($mobilPribadi, $tarif, new DateTimeImmutable('2026-08-18'), $holidays);
        $this->assertSame(0.0, $usahaResult['pajak_progresif']);
        $this->assertSame(2240000.0, $usahaResult['pkb_pokok']);
        $this->assertSame(round(2240000 * 0.66, 2), $usahaResult['opsen_pkb']);

        // Bali applies >200cc tier without progressive surcharge; DKI Jakarta has 0% opsen
        $baliTarif = (new LocalFallbackTaxClient())->getTarifByProvinsi('BALI');
        $baliResult = $pribadiRule->hitungPajak($mobilPribadi, $baliTarif, new DateTimeImmutable('2026-08-18'), $holidays);
        $this->assertSame(0.012, $baliResult['tarif_dasar']);
        $this->assertSame(0.0, $baliResult['pajak_progresif']);

        $dkiTarif = (new LocalFallbackTaxClient())->getTarifByProvinsi('DKI JAKARTA');
        $dkiResult = $pribadiRule->hitungPajak($mobilPribadi, $dkiTarif, new DateTimeImmutable('2026-08-18'), $holidays);
        $this->assertSame(0.0, $dkiResult['opsen_pkb']);
    }

    public function test_resilient_gateway_and_region_client_handle_api_success_and_fallback(): void
    {
        Cache::flush();
        $gateway = new ResilientTaxReferenceGateway(new HttpTaxReferenceClient(), new LocalFallbackTaxClient());

        Http::fake([
            '*/v1/tarif*' => Http::response([
                'data' => [
                    'province' => 'DKI JAKARTA',
                    'pkb_base_rate' => 0.02,
                    'progressive_increment' => 0.005,
                    'enterprise_flat_rate' => 0.02,
                    'late_penalty_rate_per_month' => 0.02,
                    'max_penalty_months' => 24,
                    'swdkllj_motor' => 35000,
                    'swdkllj_mobil' => 143000,
                ],
            ], 200),
        ]);

        $apiTarif = $gateway->getTarifByProvinsi('DKI JAKARTA');
        $this->assertSame('api', $gateway->lastSource());
        $this->assertSame(0.02, $apiTarif['pkb_base_rate']);

        Cache::flush();
        Http::fake([
            '*/v1/tarif*' => fn () => throw new ConnectionException('Connection refused'),
            '*/provinces.json' => Http::response([], 500),
        ]);

        $fallbackTarif = $gateway->getTarifByProvinsi('JAWA BARAT');
        $this->assertSame('fallback', $gateway->lastSource());
        $this->assertSame(0.0112, $fallbackTarif['pkb_base_rate']);

        $regionClient = new RegionApiClient();
        $provinces = $regionClient->getProvinces();
        $this->assertSame('fallback', $regionClient->lastSource());
        $this->assertGreaterThanOrEqual(34, count($provinces));
        $this->assertInstanceOf(ResilientTaxReferenceGateway::class, app(TaxReferenceGatewayInterface::class));
    }

    public function test_services_enforce_verification_payment_transactions_document_acl_and_pdf(): void
    {
        Storage::fake('local');
        Cache::flush();

        $petugas = User::query()->create([
            'name' => 'Petugas Uji',
            'email' => 'petugas.test@sippkb.local',
            'password' => 'password123',
            'role' => 'petugas',
        ]);
        $owner = User::query()->create([
            'name' => 'Pemilik Uji',
            'email' => 'owner.test@sippkb.local',
            'password' => 'password123',
            'role' => 'wajib_pajak',
        ]);
        WajibPajakProfile::query()->create([
            'user_id' => $owner->id,
            'nik_npwp' => '3273010101900099',
            'jenis_entitas' => 'pribadi',
            'provinsi' => 'JAWA BARAT',
            'kota' => 'KOTA BANDUNG',
            'alamat' => 'Jl. Merdeka 1',
            'no_telepon' => '0812000000',
        ]);
        $otherUser = User::query()->create([
            'name' => 'User Lain',
            'email' => 'other.test@sippkb.local',
            'password' => 'password123',
            'role' => 'wajib_pajak',
        ]);

        $kendaraan = Kendaraan::query()->create([
            'user_id' => $owner->id,
            'jenis_kendaraan' => 'motor',
            'plat_nomor' => 'D 7777 UJI',
            'merek' => 'Honda',
            'model_kendaraan' => 'PCX 160',
            'tahun_pembuatan' => 2024,
            'kapasitas_cc' => 160,
            'njkb' => 24000000,
            'provinsi' => 'JAWA BARAT',
            'jatuh_tempo_stnk' => now()->addMonth()->toDateString(),
            'urutan_kepemilikan' => 1,
            'status_verifikasi' => 'pending',
        ]);

        $docService = new DocumentStorageService();
        $berkas = $docService->store($kendaraan, UploadedFile::fake()->image('ktp.png'), 'ktp');
        Storage::disk('local')->assertExists($berkas->file_path);
        $this->assertNotEmpty($docService->getAbsolutePath($berkas, $owner));
        $this->assertNotEmpty($docService->getAbsolutePath($berkas, $petugas));

        try {
            $docService->authorizeAccess($berkas, $otherUser);
            $this->fail('Expected AksesBerkasDitolakException was not thrown.');
        } catch (AksesBerkasDitolakException $e) {
            $this->assertSame(403, $e->getCode());
        }

        $calculator = new TaxCalculatorService(new LocalFallbackTaxClient());

        try {
            $calculator->generateAndSaveTagihan($kendaraan);
            $this->fail('Expected DokumenBelumTerverifikasiException was not thrown.');
        } catch (DokumenBelumTerverifikasiException $e) {
            $this->assertSame(422, $e->getCode());
        }

        $kendaraan->update(['status_verifikasi' => 'approved', 'verified_by' => $petugas->id, 'verified_at' => now()]);
        $tagihan = $calculator->generateAndSaveTagihan($kendaraan);
        $this->assertSame('belum_lunas', $tagihan->status);

        $paymentService = new PaymentService($calculator);
        $pembayaran = $paymentService->bayarTagihan($tagihan, 'QRIS');
        $this->assertTrue($tagihan->fresh()->isLunas());
        $this->assertStringStartsWith('ETBPKP-', $pembayaran->no_etbpkp);

        $pdfBytes = (new PdfReceiptGenerator())->renderEtbpkp($pembayaran)->output();
        $this->assertStringStartsWith('%PDF', $pdfBytes);

        $this->expectException(TagihanSudahLunasException::class);
        $paymentService->bayarTagihan($tagihan->fresh(), 'QRIS');
    }
}
