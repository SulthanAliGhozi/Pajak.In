@extends('layouts.app')

@section('title', 'Antrean Verifikasi Petugas')

@section('content')
    @php
        $pendingItems = collect($pendingList ?? []);
        $recentItems = collect($recentVerified ?? []);
        $countData = is_array($counts ?? null) ? $counts : [];

        $pendingCount = $countData['pending'] ?? $pendingItems->count();
        $approvedCount = $countData['approved'] ?? 0;
        $rejectedCount = $countData['rejected'] ?? 0;
        $lunasCount = $countData['lunas'] ?? $countData['tagihan_belum_lunas'] ?? 0;
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Antrean Verifikasi Petugas</h1>
            <p class="mt-1 text-sm text-[#475569]">
                Periksa kelengkapan berkas KTP/STNK dan kesesuaian NJKB pengajuan kendaraan yang menunggu persetujuan.
            </p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('petugas.verifikasi.index', ['status' => 'pending']) }}" class="civic-btn-primary">
                Semua Antrean
            </a>
            <a href="{{ route('petugas.tagihan.index') }}" class="civic-btn-secondary">
                Pantau Tagihan
            </a>
        </div>
    </div>

    <section aria-label="Ringkasan verifikasi" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Menunggu Verifikasi</p>
            <p class="mt-1 text-2xl font-bold text-[#92400E] tabular-nums">{{ $pendingCount }}</p>
        </div>
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Kendaraan Disetujui</p>
            <p class="mt-1 text-2xl font-bold text-[#065F46] tabular-nums">{{ $approvedCount }}</p>
        </div>
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Pengajuan Ditolak</p>
            <p class="mt-1 text-2xl font-bold text-[#991B1B] tabular-nums">{{ $rejectedCount }}</p>
        </div>
        <div class="civic-card p-4">
            <p class="text-xs font-medium text-[#475569]">Tagihan Lunas</p>
            <p class="mt-1 text-2xl font-bold text-[#0F2942] tabular-nums">{{ $lunasCount }}</p>
        </div>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
        <section aria-labelledby="antrean-pending-heading" class="lg:col-span-8">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 id="antrean-pending-heading" class="text-lg font-bold text-[#0F172A]">Pengajuan Menunggu Verifikasi</h2>
                <a href="{{ route('petugas.verifikasi.index', ['status' => 'pending']) }}" class="inline-flex min-h-[44px] items-center text-sm font-semibold text-[#047857] hover:underline">
                    Lihat Lengkap
                </a>
            </div>

            @if ($pendingItems->isEmpty())
                <x-empty-state
                    title="Antrean verifikasi kosong"
                    description="Tidak ada kendaraan dengan status menunggu verifikasi saat ini. Periksa daftar kendaraan terverifikasi atau tagihan aktif."
                    :action-href="route('petugas.verifikasi.index', ['status' => 'all'])"
                    action-label="Lihat Semua Kendaraan"
                />
            @else
                <div class="space-y-3">
                    @foreach ($pendingItems as $k)
                        <article class="civic-card p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-[#0F2942]">{{ $k->plat_nomor }}</h3>
                                        <x-status-badge :status="$k->status_verifikasi" />
                                        @if ($k->user?->profile)
                                            <x-status-badge :status="$k->user->profile->jenis_entitas" />
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm font-medium text-[#0F172A]">
                                        {{ $k->merek }} {{ $k->model_kendaraan }} ({{ $k->tahun_pembuatan }}, {{ $k->kapasitas_cc }} cc)
                                    </p>
                                    <p class="mt-1 text-xs text-[#475569]">
                                        Pemilik: <strong class="text-[#0F172A]">{{ $k->user?->name ?? '-' }}</strong> |
                                        Provinsi: {{ $k->provinsi }} |
                                        NJKB Diajukan: <strong class="text-[#0F172A] tabular-nums">Rp {{ number_format((float) $k->njkb, 0, ',', '.') }}</strong>
                                    </p>
                                </div>
                                <a href="{{ route('petugas.verifikasi.show', $k) }}" class="civic-btn-primary shrink-0 text-sm">
                                    Periksa & Verifikasi
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section aria-labelledby="riwayat-verifikasi-heading" class="lg:col-span-4">
            <h2 id="riwayat-verifikasi-heading" class="mb-3 text-lg font-bold text-[#0F172A]">Verifikasi Terbaru</h2>
            @if ($recentItems->isEmpty())
                <div class="civic-card p-5 text-sm text-[#475569]">
                    Belum ada riwayat verifikasi kendaraan yang tercatat.
                </div>
            @else
                <div class="civic-card divide-y divide-[#E2E8F0]">
                    @foreach ($recentItems as $rv)
                        <div class="flex items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <a href="{{ route('petugas.verifikasi.show', $rv) }}" class="font-bold text-[#0F2942] hover:underline">
                                    {{ $rv->plat_nomor }}
                                </a>
                                <p class="truncate text-xs text-[#475569]">
                                    {{ $rv->user?->name ?? '-' }} | {{ $rv->verified_at?->format('d/m/Y H:i') ?? $rv->updated_at?->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <x-status-badge :status="$rv->status_verifikasi" />
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
