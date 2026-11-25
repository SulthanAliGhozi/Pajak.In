<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BerkasKendaraan;
use App\Models\Kendaraan;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\WajibPajakProfile;
use App\Services\LocalFallbackTaxClient;
use App\Services\TaxCalculatorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('local')->deleteDirectory('berkas');

        $calculator = new TaxCalculatorService(new LocalFallbackTaxClient());

        $petugasList = [
            $this->createPetugas('Raka Pratama (Petugas Samsat)', 'petugas@pajakin.local'),
            $this->createPetugas('Sari Wulandari (Petugas Verifikator)', 'petugas2@pajakin.local'),
        ];

        $wajibPajakMap = [];
        foreach (SampleVehicleDataset::wajibPajakAccounts() as $account) {
            $wajibPajakMap[$account['key']] = $this->createWajibPajak($account);
        }

        foreach (SampleVehicleDataset::vehicles() as $spec) {
            $verifierId = $spec['verifier'] !== null ? $petugasList[$spec['verifier']]->id : null;
            $dueDate = $spec['due_months'] < 0
                ? now()->subMonths(abs((int) $spec['due_months']))->toDateString()
                : now()->addMonths((int) $spec['due_months'])->toDateString();

            $kendaraan = Kendaraan::query()->create([
                'user_id' => $wajibPajakMap[$spec['owner']]->id,
                'verified_by' => $verifierId,
                'jenis_kendaraan' => $spec['jenis_kendaraan'],
                'plat_nomor' => $spec['plat_nomor'],
                'merek' => $spec['merek'],
                'model_kendaraan' => $spec['model_kendaraan'],
                'tahun_pembuatan' => $spec['tahun_pembuatan'],
                'kapasitas_cc' => $spec['kapasitas_cc'],
                'njkb' => $spec['njkb'],
                'provinsi' => $spec['provinsi'],
                'jatuh_tempo_stnk' => $dueDate,
                'urutan_kepemilikan' => $spec['urutan_kepemilikan'],
                'status_verifikasi' => $spec['status_verifikasi'],
                'catatan_petugas' => $spec['catatan_petugas'],
                'verified_at' => $spec['verified_days_ago'] !== null
                    ? now()->subDays((int) $spec['verified_days_ago'])
                    : null,
            ]);

            $this->seedVehicleDocuments($kendaraan);

            if ($kendaraan->isApproved()) {
                $tagihan = $calculator->generateAndSaveTagihan($kendaraan);
                if (is_array($spec['paid'])) {
                    Pembayaran::query()->create([
                        'tagihan_id' => $tagihan->id,
                        'kode_transaksi' => $spec['paid']['kode'],
                        'no_etbpkp' => $spec['paid']['etbpkp'],
                        'metode_pembayaran' => $spec['paid']['metode'],
                        'nominal_dibayar' => $tagihan->total_tagihan,
                        'tanggal_bayar' => now()->subDays((int) $spec['paid']['days_ago']),
                    ]);
                    $tagihan->tandaiLunas();
                }
            }
        }
    }

    private function createPetugas(string $name, string $email): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'petugas',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @param array<string, mixed> $account
     */
    private function createWajibPajak(array $account): User
    {
        $user = User::query()->create([
            'name' => $account['name'],
            'email' => $account['email'],
            'password' => Hash::make('password123'),
            'role' => 'wajib_pajak',
            'email_verified_at' => now(),
        ]);

        WajibPajakProfile::query()->create([
            'user_id' => $user->id,
            'nik_npwp' => $account['nik_npwp'],
            'jenis_entitas' => $account['jenis_entitas'],
            'provinsi' => $account['provinsi'],
            'kota' => $account['kota'],
            'alamat' => $account['alamat'],
            'no_telepon' => $account['no_telepon'],
        ]);

        return $user;
    }

    private function seedVehicleDocuments(Kendaraan $kendaraan): void
    {
        $disk = Storage::disk('local');
        $pngContent = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );
        $pdfContent = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n";

        $ktpPath = "berkas/{$kendaraan->id}/ktp-sample.png";
        $stnkPath = "berkas/{$kendaraan->id}/stnk-sample.pdf";

        $disk->put($ktpPath, (string) $pngContent);
        $disk->put($stnkPath, $pdfContent);

        BerkasKendaraan::query()->create([
            'kendaraan_id' => $kendaraan->id,
            'jenis_berkas' => 'ktp',
            'file_path' => $ktpPath,
            'original_name' => 'ktp-pemilik.png',
            'mime_type' => 'image/png',
            'size_bytes' => strlen((string) $pngContent),
            'uploaded_at' => now(),
        ]);

        BerkasKendaraan::query()->create([
            'kendaraan_id' => $kendaraan->id,
            'jenis_berkas' => 'stnk',
            'file_path' => $stnkPath,
            'original_name' => 'stnk-kendaraan.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => strlen($pdfContent),
            'uploaded_at' => now(),
        ]);
    }
}
