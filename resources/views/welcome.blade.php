@extends('layouts.public')

@section('title', 'Portal Pajak Kendaraan Bermotor')

@section('content')
    @php
        $provinceList = collect($provinces ?? []);
    @endphp

    <section class="surface-navy bg-[#0F2942] text-white border-b border-[#1E3A5F]">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-7">
                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-[2.5rem] lg:leading-[1.15]">
                        Administrasi Pajak Kendaraan Bermotor yang Jelas dan Terverifikasi
                    </h1>
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-[#E2E8F0] sm:text-lg">
                        Pajak.In melayani pendaftaran kendaraan bermotor wajib pajak pribadi maupun badan usaha, pemeriksaan berkas KTP dan STNK oleh petugas, perhitungan tarif progresif serta denda keterlambatan, hingga penerbitan e-TBPKP dalam format PDF.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                        <a href="{{ route('kalkulator.index') }}" class="civic-btn-primary">
                            Hitung Simulasi PKB
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#CBD5E1] bg-white px-5 py-2.5 text-sm font-semibold text-[#0F2942] transition-colors hover:bg-[#F4F6F9]">
                            Masuk ke Portal
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-md px-4 py-2.5 text-sm font-semibold text-[#E2E8F0] underline underline-offset-4 hover:text-white">
                            Daftar Wajib Pajak Baru
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-lg border border-[#335170] bg-[#163A5C] p-6">
                        <h2 class="text-base font-semibold text-white">Acuan Perhitungan Tarif PKB</h2>
                        <p class="mt-1 text-sm text-[#CBD5E1]">
                            Komponen resmi yang diterapkan pada kalkulator publik dan penerbitan tagihan portal:
                        </p>
                        <dl class="mt-5 divide-y divide-[#335170] text-sm">
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-[#CBD5E1]">Tarif Dasar Provinsi</dt>
                                <dd class="font-semibold text-white text-right">0,9% s.d. 2,0% NJKB</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-[#CBD5E1]">Opsen PKB Kab/Kota</dt>
                                <dd class="font-semibold text-white text-right">66% PKB (0% DKI Jakarta)</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-[#CBD5E1]">Pajak Progresif Pribadi</dt>
                                <dd class="font-semibold text-white text-right">+0,5% s.d. +1,0% per urutan</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-[#CBD5E1]">SWDKLLJ Motor / Mobil</dt>
                                <dd class="font-semibold text-white text-right tabular-nums">Rp 35.000 / Rp 143.000</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-[#CBD5E1]">Denda Keterlambatan</dt>
                                <dd class="font-semibold text-white text-right">2% per bulan (maks. 24 bln)</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-[#E2E8F0] bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-14">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <h2 class="text-2xl font-bold tracking-tight text-[#0F172A]">Alur Layanan Pajak.In</h2>
                    <p class="mt-2 text-sm text-[#475569] leading-relaxed">
                        Setiap pengajuan kendaraan melalui empat tahap tercatat agar nominal tagihan sesuai dengan kondisi kepemilikan dan berkas kendaraan.
                    </p>

                    <ol class="mt-6 divide-y divide-[#E2E8F0] border-t border-b border-[#E2E8F0]">
                        <li class="flex gap-4 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-[#0F2942] text-xs font-bold text-white">01</span>
                            <div>
                                <h3 class="text-base font-semibold text-[#0F172A]">Pendaftaran Aset dan Unggah Berkas Privat</h3>
                                <p class="mt-1 text-sm text-[#475569]">
                                    Wajib pajak mengisi spesifikasi kendaraan, estimasi NJKB dari API referensi, serta mengunggah salinan KTP dan STNK ke penyimpanan privat yang hanya dapat diakses pemilik dan petugas.
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-4 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-[#0F2942] text-xs font-bold text-white">02</span>
                            <div>
                                <h3 class="text-base font-semibold text-[#0F172A]">Verifikasi Berkas oleh Petugas Pajak</h3>
                                <p class="mt-1 text-sm text-[#475569]">
                                    Petugas memeriksa kesesuaian dokumen KTP/STNK dengan data kendaraan dan membandingkan NJKB pengajuan terhadap referensi wilayah sebelum menyetujui atau meminta perbaikan.
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-4 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-[#0F2942] text-xs font-bold text-white">03</span>
                            <div>
                                <h3 class="text-base font-semibold text-[#0F172A]">Kalkulasi Progresif, Hari Libur, dan Denda</h3>
                                <p class="mt-1 text-sm text-[#475569]">
                                    Sistem menghitung PKB pokok, tarif progresif kepemilikan pribadi atau tarif rata badan usaha, serta menyesuaikan tanggal jatuh tempo efektif jika bertepatan dengan hari libur nasional.
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-4 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-[#0F2942] text-xs font-bold text-white">04</span>
                            <div>
                                <h3 class="text-base font-semibold text-[#0F172A]">Pelunasan Tagihan dan Penerbitan e-TBPKP PDF</h3>
                                <p class="mt-1 text-sm text-[#475569]">
                                    Setelah tagihan dilunasi melalui kanal pembayaran yang tersedia, wajib pajak maupun petugas dapat langsung mengunduh Tanda Bukti Pelunasan Kewajiban Pembayaran (e-TBPKP) dalam format PDF.
                                </p>
                            </div>
                        </li>
                    </ol>
                </div>

                <div class="lg:col-span-5 space-y-5">
                    <div class="civic-card p-6">
                        <h2 class="text-lg font-bold text-[#0F172A]">Akses Portal Wajib Pajak</h2>
                        <p class="mt-1.5 text-sm text-[#475569] leading-relaxed">
                            Untuk pemilik kendaraan pribadi maupun badan usaha. Kelola daftar kendaraan, pantau status verifikasi, selesaikan pembayaran, dan unduh e-TBPKP.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('login', ['portal' => 'wajib_pajak']) }}" class="civic-btn-primary">
                                Masuk Wajib Pajak
                            </a>
                            <a href="{{ route('register') }}" class="civic-btn-secondary">
                                Registrasi Akun
                            </a>
                        </div>
                    </div>

                    <div class="civic-card p-6">
                        <h2 class="text-lg font-bold text-[#0F172A]">Akses Portal Petugas Samsat</h2>
                        <p class="mt-1.5 text-sm text-[#475569] leading-relaxed">
                            Untuk petugas verifikator. Tinjau antrean kendaraan baru, periksa berkas KTP dan STNK secara aman, koreksi NJKB bila diperlukan, dan pantau status tagihan.
                        </p>
                        <div class="mt-5">
                            <a href="{{ route('login', ['portal' => 'petugas']) }}" class="civic-btn-navy">
                                Masuk Portal Petugas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#F4F6F9]">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">Cakupan Wilayah Provinsi</h2>
                    <p class="mt-1 text-sm text-[#475569]">
                        Kalkulator PKB dan pendaftaran wajib pajak mendukung {{ $provinceList->count() }} provinsi yang terdaftar pada layanan referensi wilayah.
                    </p>
                </div>
                <a href="{{ route('kalkulator.index') }}" class="civic-btn-secondary shrink-0">
                    Buka Kalkulator Pajak
                </a>
            </div>

            @if ($provinceList->isEmpty())
                <div class="mt-6">
                    <x-empty-state
                        title="Daftar provinsi belum dimuat"
                        description="Layanan referensi wilayah sedang tidak mengembalikan daftar provinsi. Anda tetap dapat membuka halaman kalkulator pajak untuk memeriksa ketersediaan tarif lokal."
                        :action-href="route('kalkulator.index')"
                        action-label="Buka Kalkulator PKB"
                    />
                </div>
            @else
                <div class="mt-6 civic-card p-4 sm:p-5">
                    <ul class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-[#0F172A] sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        @foreach ($provinceList->take(18) as $prov)
                            @php
                                $provName = is_array($prov) ? ($prov['name'] ?? '') : (string) $prov;
                            @endphp
                            <li class="truncate py-1 font-medium text-[#475569]">{{ $provName }}</li>
                        @endforeach
                    </ul>
                    @if ($provinceList->count() > 18)
                        <p class="mt-3 border-t border-[#E2E8F0] pt-3 text-xs text-[#475569]">
                            Menampilkan 18 dari {{ $provinceList->count() }} provinsi tersedia. Seluruh provinsi dapat dipilih pada formulir Registrasi dan Kalkulator PKB.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
