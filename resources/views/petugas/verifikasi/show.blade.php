@extends('layouts.app')

@section('title', 'Pemeriksaan Kendaraan ' . $kendaraan->plat_nomor)

@section('content')
    @php
        $owner = $kendaraan->user;
        $ownerProfile = $owner?->profile;
        $refNjkbValue = is_array($estimatedNjkb ?? null)
            ? (float) ($estimatedNjkb['estimated_njkb'] ?? 0)
            : (float) ($estimatedNjkb ?? 0);
        $njkbDiff = (float) $kendaraan->njkb - $refNjkbValue;
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Pemeriksaan {{ $kendaraan->plat_nomor }}</h1>
                <x-status-badge :status="$kendaraan->status_verifikasi" />
            </div>
            <p class="mt-1 text-sm text-[#475569]">
                {{ $kendaraan->merek }} {{ $kendaraan->model_kendaraan }} ({{ $kendaraan->tahun_pembuatan }}) | {{ $kendaraan->provinsi }}
            </p>
        </div>
        <a href="{{ route('petugas.verifikasi.index') }}" class="civic-btn-secondary shrink-0">
            Kembali ke Daftar Verifikasi
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
        <div class="space-y-6 lg:col-span-7">
            <section aria-labelledby="identitas-wp-heading" class="civic-card p-5 sm:p-6">
                <h2 id="identitas-wp-heading" class="text-base font-bold text-[#0F2942]">Identitas Wajib Pajak</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Nama Pemilik / Badan Usaha</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">{{ $owner?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Jenis Entitas</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">
                            {{ $ownerProfile?->jenis_entitas === 'usaha' ? 'Badan Usaha' : 'Wajib Pajak Pribadi' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">NIK / NPWP</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A] tabular-nums">{{ $ownerProfile?->nik_npwp ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Kontak</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">{{ $owner?->email }} | {{ $ownerProfile?->no_telepon ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-[#475569]">Alamat Domisili</dt>
                        <dd class="mt-0.5 text-[#0F172A]">
                            {{ $ownerProfile?->alamat ?? '-' }}, {{ $ownerProfile?->kota ?? '' }} ({{ $ownerProfile?->provinsi ?? '' }})
                        </dd>
                    </div>
                </dl>
            </section>

            <section aria-labelledby="njkb-compare-heading" class="civic-card p-5 sm:p-6">
                <h2 id="njkb-compare-heading" class="text-base font-bold text-[#0F2942]">Spesifikasi Kendaraan & Referensi NJKB</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Jenis / CC / Urutan</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">
                            {{ ucfirst($kendaraan->jenis_kendaraan) }}, {{ $kendaraan->kapasitas_cc }} cc, Kepemilikan ke-{{ $kendaraan->urutan_kepemilikan }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Jatuh Tempo STNK</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">{{ $kendaraan->jatuh_tempo_stnk?->format('d/m/Y') }}</dd>
                    </div>
                    <div class="rounded border border-[#E2E8F0] bg-[#F4F6F9] p-3">
                        <dt class="text-xs font-medium text-[#475569]">NJKB Diajukan Wajib Pajak</dt>
                        <dd class="mt-1 text-base font-bold text-[#0F172A] tabular-nums">
                            Rp {{ number_format((float) $kendaraan->njkb, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="rounded border border-[#E2E8F0] bg-[#F4F6F9] p-3">
                        <dt class="text-xs font-medium text-[#475569]">Estimasi NJKB Referensi</dt>
                        <dd class="mt-1 text-base font-bold text-[#0F2942] tabular-nums">
                            @if ($refNjkbValue > 0)
                                Rp {{ number_format($refNjkbValue, 0, ',', '.') }}
                            @else
                                Tidak tersedia
                            @endif
                        </dd>
                        @if ($refNjkbValue > 0)
                            <p class="mt-1 text-xs text-[#475569] tabular-nums">
                                Selisih pengajuan: {{ $njkbDiff >= 0 ? '+' : '' }}Rp {{ number_format($njkbDiff, 0, ',', '.') }}
                            </p>
                        @endif
                    </div>
                </dl>
            </section>

            <section aria-labelledby="berkas-privat-heading" class="civic-card p-5 sm:p-6">
                <h2 id="berkas-privat-heading" class="text-base font-bold text-[#0F2942]">Pemeriksaan Berkas KTP & STNK</h2>
                @if ($kendaraan->berkas->isEmpty())
                    <p class="mt-3 text-sm text-[#991B1B]">Tidak ditemukan berkas terunggah untuk pengajuan kendaraan ini.</p>
                @else
                    <div class="mt-4 space-y-5">
                        @foreach ($kendaraan->berkas as $b)
                            <div class="rounded-md border border-[#E2E8F0] p-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-bold text-[#0F172A]">{{ strtoupper($b->jenis_berkas) }}: {{ $b->original_name }}</p>
                                        <p class="text-xs text-[#475569]">{{ $b->mime_type }} | {{ number_format($b->size_bytes / 1024, 1) }} KB</p>
                                    </div>
                                    <a href="{{ route('berkas.show', $b) }}" target="_blank" rel="noopener" class="civic-btn-secondary shrink-0 text-xs">
                                        Buka Berkas
                                    </a>
                                </div>
                                <div class="mt-3">
                                    @if (str_starts_with((string) $b->mime_type, 'image/'))
                                        <img
                                            src="{{ route('berkas.show', $b) }}"
                                            alt="Pratinjau dokumen {{ strtoupper($b->jenis_berkas) }} {{ $kendaraan->plat_nomor }}"
                                            width="640"
                                            height="360"
                                            class="max-h-64 w-full rounded border border-[#E2E8F0] bg-[#F4F6F9] object-contain"
                                        >
                                    @else
                                        <iframe
                                            src="{{ route('berkas.show', $b) }}"
                                            title="Pratinjau PDF {{ strtoupper($b->jenis_berkas) }} {{ $kendaraan->plat_nomor }}"
                                            width="640"
                                            height="280"
                                            class="h-64 w-full rounded border border-[#E2E8F0] bg-[#F4F6F9]"
                                        ></iframe>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        @include('petugas.verifikasi._decision-panel')
    </div>
@endsection
