<?php

declare(strict_types=1);

namespace Database\Seeders;

final class SampleVehicleDataset
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function wajibPajakAccounts(): array
    {
        return [
            [
                'key' => 'budi',
                'name' => 'Budi Santoso',
                'email' => 'budi@pajakin.local',
                'nik_npwp' => '3273011508900001',
                'jenis_entitas' => 'pribadi',
                'provinsi' => 'JAWA BARAT',
                'kota' => 'KOTA BANDUNG',
                'alamat' => 'Jl. Dipatiukur No. 45, Coblong, Bandung',
                'no_telepon' => '081234567890',
            ],
            [
                'key' => 'ptmaju',
                'name' => 'PT Maju Logistik Nusantara',
                'email' => 'ptmaju@pajakin.local',
                'nik_npwp' => '013456789012000',
                'jenis_entitas' => 'usaha',
                'provinsi' => 'DKI JAKARTA',
                'kota' => 'JAKARTA PUSAT',
                'alamat' => 'Jl. Jend. Sudirman Kav. 21, Jakarta Pusat',
                'no_telepon' => '0215551234',
            ],
            [
                'key' => 'rina',
                'name' => 'Rina Kusuma',
                'email' => 'rina@pajakin.local',
                'nik_npwp' => '3578045209930004',
                'jenis_entitas' => 'pribadi',
                'provinsi' => 'JAWA TIMUR',
                'kota' => 'KOTA SURABAYA',
                'alamat' => 'Jl. Raya Darmo No. 112, Wonokromo, Surabaya',
                'no_telepon' => '081398765432',
            ],
            [
                'key' => 'andi',
                'name' => 'Andi Wijaya',
                'email' => 'andi@pajakin.local',
                'nik_npwp' => '5171021804880002',
                'jenis_entitas' => 'pribadi',
                'provinsi' => 'BALI',
                'kota' => 'KOTA DENPASAR',
                'alamat' => 'Jl. Teuku Umar Barat No. 88, Denpasar',
                'no_telepon' => '081911223344',
            ],
            [
                'key' => 'cvsejahtera',
                'name' => 'CV Sejahtera Abadi',
                'email' => 'cvsejahtera@pajakin.local',
                'nik_npwp' => '028812345678000',
                'jenis_entitas' => 'usaha',
                'provinsi' => 'JAWA TENGAH',
                'kota' => 'KOTA SEMARANG',
                'alamat' => 'Jl. Pemuda No. 74, Semarang Tengah',
                'no_telepon' => '0248312290',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function vehicles(): array
    {
        return [
            [
                'owner' => 'budi', 'verifier' => 0, 'jenis_kendaraan' => 'motor',
                'plat_nomor' => 'D 4512 ABC', 'merek' => 'Honda', 'model_kendaraan' => 'Vario 160 ABS',
                'tahun_pembuatan' => 2023, 'kapasitas_cc' => 160, 'njkb' => 21000000,
                'provinsi' => 'JAWA BARAT', 'due_months' => -3, 'urutan_kepemilikan' => 1,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Berkas KTP dan STNK lengkap dan sesuai.',
                'verified_days_ago' => 120, 'paid' => false,
            ],
            [
                'owner' => 'budi', 'verifier' => 0, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'D 1829 XYZ', 'merek' => 'Toyota', 'model_kendaraan' => 'Avanza 1.5 G CVT',
                'tahun_pembuatan' => 2022, 'kapasitas_cc' => 1500, 'njkb' => 185000000,
                'provinsi' => 'JAWA BARAT', 'due_months' => 1, 'urutan_kepemilikan' => 2,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Kendaraan ke-2 atas nama wajib pajak pribadi.',
                'verified_days_ago' => 14, 'paid' => false,
            ],
            [
                'owner' => 'budi', 'verifier' => null, 'jenis_kendaraan' => 'motor',
                'plat_nomor' => 'D 6789 NMX', 'merek' => 'Yamaha', 'model_kendaraan' => 'NMAX 155 Connected',
                'tahun_pembuatan' => 2024, 'kapasitas_cc' => 155, 'njkb' => 25500000,
                'provinsi' => 'JAWA BARAT', 'due_months' => 2, 'urutan_kepemilikan' => 3,
                'status_verifikasi' => 'pending', 'catatan_petugas' => null,
                'verified_days_ago' => null, 'paid' => false,
            ],
            [
                'owner' => 'budi', 'verifier' => 1, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'D 2045 HRV', 'merek' => 'Honda', 'model_kendaraan' => 'HR-V SE 1.5',
                'tahun_pembuatan' => 2023, 'kapasitas_cc' => 1500, 'njkb' => 268000000,
                'provinsi' => 'JAWA BARAT', 'due_months' => 3, 'urutan_kepemilikan' => 4,
                'status_verifikasi' => 'rejected',
                'catatan_petugas' => 'Foto STNK kurang jelas pada bagian masa berlaku, mohon unggah ulang.',
                'verified_days_ago' => 3, 'paid' => false,
            ],
            [
                'owner' => 'budi', 'verifier' => 0, 'jenis_kendaraan' => 'motor',
                'plat_nomor' => 'D 3310 VSP', 'merek' => 'Vespa', 'model_kendaraan' => 'Sprint 150 i-get',
                'tahun_pembuatan' => 2022, 'kapasitas_cc' => 150, 'njkb' => 36500000,
                'provinsi' => 'JAWA BARAT', 'due_months' => 5, 'urutan_kepemilikan' => 5,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Dokumen terverifikasi sesuai data kepemilikan.',
                'verified_days_ago' => 25,
                'paid' => ['kode' => 'TRX-20261112-BUDIVSP', 'etbpkp' => 'ETBPKP-2026-00014210', 'metode' => 'QRIS Simulasi', 'days_ago' => 12],
            ],
            [
                'owner' => 'ptmaju', 'verifier' => 0, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'B 9012 KLM', 'merek' => 'Daihatsu', 'model_kendaraan' => 'Gran Max 1.5 PU',
                'tahun_pembuatan' => 2021, 'kapasitas_cc' => 1500, 'njkb' => 132000000,
                'provinsi' => 'DKI JAKARTA', 'due_months' => 4, 'urutan_kepemilikan' => 1,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Dokumen armada badan usaha tervalidasi.',
                'verified_days_ago' => 10,
                'paid' => ['kode' => 'TRX-20261120-PTMAJU1', 'etbpkp' => 'ETBPKP-2026-00018291', 'metode' => 'Virtual Account Bank', 'days_ago' => 5],
            ],
            [
                'owner' => 'ptmaju', 'verifier' => 1, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'B 9441 TRK', 'merek' => 'Mitsubishi', 'model_kendaraan' => 'Colt L300 Pick Up',
                'tahun_pembuatan' => 2020, 'kapasitas_cc' => 2268, 'njkb' => 158000000,
                'provinsi' => 'DKI JAKARTA', 'due_months' => -2, 'urutan_kepemilikan' => 2,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Armada operasional ke-2 badan usaha.',
                'verified_days_ago' => 75, 'paid' => false,
            ],
            [
                'owner' => 'ptmaju', 'verifier' => null, 'jenis_kendaraan' => 'motor',
                'plat_nomor' => 'B 3190 KUR', 'merek' => 'Honda', 'model_kendaraan' => 'Beat Street Kurir',
                'tahun_pembuatan' => 2024, 'kapasitas_cc' => 110, 'njkb' => 14500000,
                'provinsi' => 'DKI JAKARTA', 'due_months' => 6, 'urutan_kepemilikan' => 1,
                'status_verifikasi' => 'pending', 'catatan_petugas' => null,
                'verified_days_ago' => null, 'paid' => false,
            ],
            [
                'owner' => 'rina', 'verifier' => 1, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'L 1420 RKM', 'merek' => 'Suzuki', 'model_kendaraan' => 'All New Ertiga GX',
                'tahun_pembuatan' => 2021, 'kapasitas_cc' => 1462, 'njkb' => 165000000,
                'provinsi' => 'JAWA TIMUR', 'due_months' => -5, 'urutan_kepemilikan' => 1,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Berkas lengkap, terbit tagihan tahun berjalan.',
                'verified_days_ago' => 160, 'paid' => false,
            ],
            [
                'owner' => 'rina', 'verifier' => null, 'jenis_kendaraan' => 'motor',
                'plat_nomor' => 'L 5502 NJN', 'merek' => 'Kawasaki', 'model_kendaraan' => 'Ninja 250 SL',
                'tahun_pembuatan' => 2022, 'kapasitas_cc' => 249, 'njkb' => 34000000,
                'provinsi' => 'JAWA TIMUR', 'due_months' => 3, 'urutan_kepemilikan' => 2,
                'status_verifikasi' => 'pending', 'catatan_petugas' => null,
                'verified_days_ago' => null, 'paid' => false,
            ],
            [
                'owner' => 'andi', 'verifier' => 0, 'jenis_kendaraan' => 'motor',
                'plat_nomor' => 'DK 4128 AX', 'merek' => 'Yamaha', 'model_kendaraan' => 'Aerox 155 Connected',
                'tahun_pembuatan' => 2023, 'kapasitas_cc' => 155, 'njkb' => 22800000,
                'provinsi' => 'BALI', 'due_months' => 7, 'urutan_kepemilikan' => 1,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Disetujui sesuai STNK wilayah Bali.',
                'verified_days_ago' => 30,
                'paid' => ['kode' => 'TRX-20261118-ANDIAX1', 'etbpkp' => 'ETBPKP-2026-00016745', 'metode' => 'Loket Samsat', 'days_ago' => 7],
            ],
            [
                'owner' => 'andi', 'verifier' => 1, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'DK 1789 ZNX', 'merek' => 'Toyota', 'model_kendaraan' => 'Kijang Innova Zenix G',
                'tahun_pembuatan' => 2023, 'kapasitas_cc' => 1987, 'njkb' => 315000000,
                'provinsi' => 'BALI', 'due_months' => 2, 'urutan_kepemilikan' => 2,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Kendaraan roda empat urutan ke-2.',
                'verified_days_ago' => 8, 'paid' => false,
            ],
            [
                'owner' => 'cvsejahtera', 'verifier' => 0, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'H 8821 SJA', 'merek' => 'Isuzu', 'model_kendaraan' => 'Traga Pick Up FD',
                'tahun_pembuatan' => 2022, 'kapasitas_cc' => 2499, 'njkb' => 176000000,
                'provinsi' => 'JAWA TENGAH', 'due_months' => 1, 'urutan_kepemilikan' => 1,
                'status_verifikasi' => 'approved', 'catatan_petugas' => 'Kendaraan niaga CV terverifikasi.',
                'verified_days_ago' => 18, 'paid' => false,
            ],
            [
                'owner' => 'cvsejahtera', 'verifier' => 1, 'jenis_kendaraan' => 'mobil',
                'plat_nomor' => 'H 7703 SJA', 'merek' => 'Toyota', 'model_kendaraan' => 'HiAce Commuter',
                'tahun_pembuatan' => 2021, 'kapasitas_cc' => 2755, 'njkb' => 342000000,
                'provinsi' => 'JAWA TENGAH', 'due_months' => 4, 'urutan_kepemilikan' => 2,
                'status_verifikasi' => 'rejected',
                'catatan_petugas' => 'Nomor polisi pada formulir tidak sesuai dengan dokumen STNK yang diunggah.',
                'verified_days_ago' => 2, 'paid' => false,
            ],
        ];
    }
}
