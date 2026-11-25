# Pajak.In

Sistem Informasi Pengecekan Pajak Kendaraan Terpadu berbasis web yang dibangun menggunakan **Laravel** dengan penerapan **Pemrograman Berbasis Objek (OOP)**. Aplikasi ini mengelola pendaftaran aset kendaraan bermotor (Motor dan Mobil), verifikasi berkas legalitas (KTP/STNK), kalkulasi PKB pokok, pajak progresif, Opsen PKB daerah, denda keterlambatan otomatis, simulasi pembayaran, hingga penerbitan bukti bayar **e-TBPKP (PDF)**.

## Fitur Utama

1. **Autentikasi & Profil (RBAC):** Login terpisah untuk portal **Wajib Pajak** dan **Petugas**, registrasi wajib pajak, serta manajemen profil identitas (NIK/NPWP, kategori Pribadi atau Badan Usaha, domisili provinsi).
2. **Manajemen Aset Kendaraan:** Pendaftaran dan pengelolaan data kendaraan (Motor/Mobil, plat nomor, merek, model, tahun pembuatan, kapasitas CC, NJKB, dan jatuh tempo STNK).
3. **Upload Berkas Lokal Privat:** Penyimpanan dokumen KTP dan STNK pada direktori privat server (`storage/app/private`) dengan validasi kepemilikan berbasis policy agar tidak dapat diakses publik.
4. **Verifikasi Petugas:** Panel petugas untuk meninjau antrean berkas, menyesuaikan NJKB referensi, menyetujui (*Approve*) untuk menerbitkan tagihan otomatis, atau menolak (*Reject*) dengan catatan revisi.
5. **Kalkulator Pajak, Opsen Daerah, & Denda Otomatis:** Perhitungan tarif PKB per provinsi (Perda & UU HKPD + Opsen PKB 66%, Non-Opsen DKI Jakarta, Non-Progresif Bali), pajak progresif kepemilikan pribadi, penyesuaian hari libur nasional, dan denda keterlambatan bulanan.
6. **REST API Referensi Internal & Konsumsi API Wilayah:** Menyediakan endpoint REST API (`/api/health`, `/api/v1/tarif`, `/api/v1/njkb`, `/api/v1/libur-nasional`) yang dikonsumsi via HTTP Client dengan mekanisme caching & fallback lokal, serta konsumsi API Wilayah Indonesia.
7. **Simulasi Pembayaran & Cetak e-TBPKP (PDF):** Pelunasan tagihan secara atomik dengan penyimpanan snapshot nominal transaksi dan ekspor struk e-TBPKP berformat PDF (`barryvdh/laravel-dompdf`).

## Penerapan Konsep OOP

* **Kelas & Object:** Entitas model dan domain (`User`, `WajibPajakProfile`, `Motor`, `Mobil`, `TagihanPajak`, `Pembayaran`) serta kelas layanan (`TaxCalculatorService`, `PaymentService`, `DocumentStorageService`, `PdfReceiptGenerator`).
* **Relasi Antar Kelas:** One-to-One (`User` - `WajibPajakProfile`, `TagihanPajak` - `Pembayaran`), Composition (`Kendaraan` - `BerkasKendaraan`, `Kendaraan` - `TagihanPajak`), dan Dependency Injection antar service.
* **Abstract Class & Interface:** `App\Domain\Kendaraan\AbstractKendaraan`, `App\Domain\Rules\AbstractTaxRule`, `App\Contracts\TaxRuleInterface`, `App\Contracts\TaxReferenceGatewayInterface`, `App\Contracts\RegionProviderInterface`, dan `App\TaxReferenceApi\Contracts\ReferenceProviderInterface`.
* **Pewarisan (*Inheritance*):** `Motor` dan `Mobil` mewarisi `AbstractKendaraan`; `PribadiTaxRule` dan `UsahaTaxRule` mewarisi `AbstractTaxRule`; hierarki custom exception mewarisi `PajakException`.
* **Polimorfisme:** `TaxCalculatorService` menghitung kewajiban secara dinamis melalui `AbstractKendaraan` (`Motor` vs `Mobil`) dan `TaxRuleInterface` (`PribadiTaxRule` vs `UsahaTaxRule`).
* **Penanganan Eksepsi (*Exception Handling*):** Custom exception di `app/Exceptions/` (`DokumenBelumTerverifikasiException`, `TagihanSudahLunasException`, `AksesBerkasDitolakException`, `TaxApiConnectionException`) yang ditangani menggunakan blok `try-catch`.

## Instalasi & Menjalankan Lokal

### Prasyarat
* PHP >= 8.3 (disarankan PHP 8.4 / 8.5)
* Composer & Node.js + NPM
* SQLite (bawaan) atau MySQL/MariaDB

### Langkah Setup
```bash
git clone https://github.com/SulthanAliGhozi/Pajak.In.git
cd Pajak.In

composer install
npm install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate:fresh --seed
npm run build
```

### Menjalankan Aplikasi
Jika menggunakan **Laravel Valet**:
```bash
valet link pajakin
valet secure pajakin
```
Akses melalui `https://pajakin.test`.

Jika menggunakan **PHP Built-in Server**:
Sesuaikan `APP_URL=http://127.0.0.1:8000` dan `TAX_REFERENCE_API_URL=http://127.0.0.1:8000/api` di `.env`, lalu jalankan:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## Akun Demo (Hasil Seeder)

Semua akun bawaan menggunakan kata sandi `password123`:

| Peran | Email | Keterangan |
| :--- | :--- | :--- |
| Petugas | `petugas@pajakin.local` | Panel verifikasi antrean & pantau tagihan |
| Petugas | `petugas2@pajakin.local` | Akun petugas verifikator kedua |
| Wajib Pajak (Pribadi) | `budi@pajakin.local` | 5 kendaraan di Jawa Barat (terlambat, progresif, pending, rejected, lunas) |
| Wajib Pajak (Usaha) | `ptmaju@pajakin.local` | 3 armada badan usaha di DKI Jakarta |
| Wajib Pajak (Pribadi) | `rina@pajakin.local` | 2 kendaraan di Jawa Timur |
| Wajib Pajak (Pribadi) | `andi@pajakin.local` | 2 kendaraan di Bali |
| Wajib Pajak (Usaha) | `cvsejahtera@pajakin.local` | 2 armada badan usaha di Jawa Tengah |

## Endpoint REST API

* `GET /api/health` : Cek status layanan API referensi
* `GET /api/v1/tarif` : Daftar tarif PKB & Opsen seluruh provinsi referensi
* `GET /api/v1/tarif?provinsi=JAWA%20BARAT` : Detail tarif PKB & Opsen untuk provinsi tertentu
* `GET /api/v1/njkb?jenis=mobil&merek=toyota&tahun=2022&cc=1500` : Estimasi NJKB kendaraan
* `GET /api/v1/libur-nasional?tahun=2026` : Daftar hari libur nasional

## Pengujian

```bash
php artisan test
```
