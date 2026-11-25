@extends('layouts.app')

@section('title', 'Ringkasan Wajib Pajak')

@section('content')
    @php
        $kendaraanItems = collect($kendaraanList ?? []);
        $tagihanItems = isset($tagihanAktif)
            ? collect($tagihanAktif)
            : $kendaraanItems->pluck('tagihan')->flatten()->where('status', 'belum_lunas');
        $summaryData = is_array($summary ?? null) ? $summary : [];

        $totalKendaraan = $summaryData['total_kendaraan'] ?? $kendaraanItems->count();
        $approvedCount = $summaryData['disetujui'] ?? $summaryData['approved'] ?? $kendaraanItems->where('status_verifikasi', 'approved')->count();
        $pendingCount = $summaryData['pending_verifikasi'] ?? $summaryData['menunggu_verifikasi'] ?? $kendaraanItems->where('status_verifikasi', 'pending')->count();
        $unpaidCount = $summaryData['tagihan_belum_lunas'] ?? $tagihanItems->count();
        $unpaidTotal = $summaryData['total_nominal_belum_lunas'] ?? $summaryData['total_tagihan_aktif'] ?? $tagihanItems->sum('total_tagihan');
        $rejectedVehicles = $kendaraanItems->where('status_verifikasi', 'rejected');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Portal Wajib Pajak</h1>
            <p class="mt-1 text-sm text-[#475569]">
                {{ $user->name }}
                @if ($profile)
                    | {{ $profile->jenis_entitas === 'usaha' ? 'Badan Usaha' : 'Wajib Pajak Pribadi' }} ({{ $profile->provinsi }})
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('wp.kendaraan.create') }}" class="civic-btn-primary">
                Daftarkan Kendaraan
            </a>
            <a href="{{ route('wp.profil.edit') }}" class="civic-btn-secondary">
                Ubah Profil
            </a>
        </div>
    </div>

    @if ($rejectedVehicles->isNotEmpty())
        <div role="alert" class="mt-6 rounded-md border border-[#EF4444]/50 bg-[#FEE2E2] p-4 text-sm text-[#991B1B]">
            <p class="font-bold">Ada {{ $rejectedVehicles->count() }} pengajuan kendaraan yang perlu diperbaiki</p>
            <ul class="mt-2 space-y-1.5">
                @foreach ($rejectedVehicles as $rv)
                    <li class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <span>
                            <strong>{{ $rv->plat_nomor }}</strong> ({{ $rv->merek }} {{ $rv->model_kendaraan }}):
                            {{ $rv->catatan_petugas ?: 'Periksa kembali kelengkapan data dan berkas.' }}
                        </span>
                        <a href="{{ route('wp.kendaraan.edit', $rv) }}" class="font-semibold underline underline-offset-4 hover:text-[#7F1D1D]">
                            Perbaiki Berkas
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <section aria-label="Ringkasan akun" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Total Kendaraan</p>
            <p class="mt-1 text-2xl font-bold text-[#0F2942] tabular-nums">{{ $totalKendaraan }}</p>
        </div>
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Terverifikasi</p>
            <p class="mt-1 text-2xl font-bold text-[#065F46] tabular-nums">{{ $approvedCount }}</p>
        </div>
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Menunggu Verifikasi</p>
            <p class="mt-1 text-2xl font-bold text-[#92400E] tabular-nums">{{ $pendingCount }}</p>
        </div>
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Tagihan Aktif ({{ $unpaidCount }})</p>
            <p class="mt-1 text-lg sm:text-xl font-bold text-[#991B1B] tabular-nums">
                Rp {{ number_format((float) $unpaidTotal, 0, ',', '.') }}
            </p>
        </div>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
        <section aria-labelledby="tagihan-aktif-heading" class="lg:col-span-7">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 id="tagihan-aktif-heading" class="text-lg font-bold text-[#0F172A]">Tagihan Jatuh Tempo & Belum Lunas</h2>
                <a href="{{ route('wp.tagihan.index') }}" class="inline-flex min-h-[44px] items-center text-sm font-semibold text-[#047857] hover:underline">
                    Semua Tagihan
                </a>
            </div>

            @if ($tagihanItems->isEmpty())
                <x-empty-state
                    title="Tidak ada tagihan aktif saat ini"
                    description="Seluruh tagihan kendaraan terverifikasi Anda telah dilunasi, atau kendaraan baru masih menunggu persetujuan petugas."
                    :action-href="route('wp.tagihan.index')"
                    action-label="Lihat Riwayat Tagihan"
                />
            @else
                <div class="space-y-3">
                    @foreach ($tagihanItems as $tagihan)
                        <article class="civic-card p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-[#0F2942]">
                                            {{ $tagihan->kendaraan?->plat_nomor ?? 'Kendaraan' }}
                                        </h3>
                                        <x-status-badge :status="$tagihan->status" />
                                    </div>
                                    <p class="mt-1 text-sm text-[#475569]">
                                        {{ $tagihan->kendaraan?->merek }} {{ $tagihan->kendaraan?->model_kendaraan }} | Tahun Pajak {{ $tagihan->tahun_pajak }}
                                    </p>
                                    <p class="mt-1 text-xs text-[#475569]">
                                        Jatuh tempo STNK: <strong class="text-[#0F172A]">{{ $tagihan->jatuh_tempo?->format('d/m/Y') }}</strong>
                                        @if ((float) $tagihan->denda_keterlambatan > 0)
                                            | Termasuk denda Rp {{ number_format((float) $tagihan->denda_keterlambatan, 0, ',', '.') }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex flex-col items-start gap-2 sm:items-end">
                                    <p class="text-lg font-bold text-[#0F172A] tabular-nums">
                                        Rp {{ number_format((float) $tagihan->total_tagihan, 0, ',', '.') }}
                                    </p>
                                    <a href="{{ route('wp.tagihan.show', $tagihan) }}" class="civic-btn-primary text-sm">
                                        Bayar Tagihan
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section aria-labelledby="aset-status-heading" class="lg:col-span-5">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 id="aset-status-heading" class="text-lg font-bold text-[#0F172A]">Status Aset Kendaraan</h2>
                <a href="{{ route('wp.kendaraan.index') }}" class="inline-flex min-h-[44px] items-center text-sm font-semibold text-[#047857] hover:underline">
                    Kelola Aset
                </a>
            </div>

            @if ($kendaraanItems->isEmpty())
                <x-empty-state
                    title="Belum ada kendaraan terdaftar"
                    description="Daftarkan kendaraan Anda beserta berkas KTP dan STNK agar petugas dapat melakukan verifikasi dan menerbitkan tagihan PKB."
                    :action-href="route('wp.kendaraan.create')"
                    action-label="Daftarkan Kendaraan"
                />
            @else
                <div class="civic-card divide-y divide-[#E2E8F0]">
                    @foreach ($kendaraanItems->take(6) as $k)
                        <div class="flex items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <a href="{{ route('wp.kendaraan.show', $k) }}" class="font-bold text-[#0F2942] hover:underline">
                                    {{ $k->plat_nomor }}
                                </a>
                                <p class="truncate text-xs text-[#475569]">
                                    {{ $k->merek }} {{ $k->model_kendaraan }} ({{ $k->tahun_pembuatan }})
                                </p>
                            </div>
                            <x-status-badge :status="$k->status_verifikasi" />
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
