@extends('layouts.app')

@section('title', 'Kendaraan Saya')

@section('content')
    @php
        $items = $kendaraanList ?? collect();
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Daftar Kendaraan Saya</h1>
            <p class="mt-1 text-sm text-[#475569]">
                Pantau status verifikasi dokumen dan rincian aset kendaraan bermotor yang terdaftar atas nama Anda.
            </p>
        </div>
        <a href="{{ route('wp.kendaraan.create') }}" class="civic-btn-primary shrink-0">
            Daftarkan Kendaraan
        </a>
    </div>

    @if ($items->isEmpty())
        <x-empty-state
            title="Belum ada kendaraan terdaftar"
            description="Tambahkan kendaraan bermotor beserta dokumen KTP dan STNK untuk memulai proses verifikasi pajak kendaraan."
            :action-href="route('wp.kendaraan.create')"
            action-label="Daftarkan Kendaraan Pertama"
        />
    @else
        <div class="space-y-3 md:hidden">
            @foreach ($items as $k)
                <article class="civic-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-[#0F2942]">{{ $k->plat_nomor }}</h2>
                            <p class="text-sm text-[#0F172A]">{{ $k->merek }} {{ $k->model_kendaraan }}</p>
                        </div>
                        <x-status-badge :status="$k->status_verifikasi" />
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 border-t border-[#E2E8F0] pt-3 text-xs">
                        <div>
                            <dt class="text-[#475569]">Jenis / Tahun</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ ucfirst($k->jenis_kendaraan) }} ({{ $k->tahun_pembuatan }})</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">NJKB</dt>
                            <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format((float) $k->njkb, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">Provinsi</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $k->provinsi }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">Jatuh Tempo STNK</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $k->jatuh_tempo_stnk?->format('d/m/Y') }}</dd>
                        </div>
                    </dl>
                    @if ($k->status_verifikasi === 'rejected' && $k->catatan_petugas)
                        <p class="mt-3 rounded bg-[#FEE2E2] px-3 py-2 text-xs text-[#991B1B]">
                            Catatan petugas: {{ $k->catatan_petugas }}
                        </p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('wp.kendaraan.show', $k) }}" class="civic-btn-navy flex-1 text-xs">
                            Lihat Detail
                        </a>
                        @if (in_array($k->status_verifikasi, ['pending', 'rejected'], true))
                            <a href="{{ route('wp.kendaraan.edit', $k) }}" class="civic-btn-secondary flex-1 text-xs">
                                Ubah Data
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="hidden md:block civic-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-[#E2E8F0] bg-[#F4F6F9] text-xs font-semibold uppercase tracking-wide text-[#475569]">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Plat Nomor</th>
                            <th scope="col" class="px-5 py-3.5">Kendaraan</th>
                            <th scope="col" class="px-5 py-3.5">NJKB</th>
                            <th scope="col" class="px-5 py-3.5">Provinsi & Tempo</th>
                            <th scope="col" class="px-5 py-3.5">Status Verifikasi</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach ($items as $k)
                            <tr class="hover:bg-[#F4F6F9]/60">
                                <td class="px-5 py-4 font-bold text-[#0F2942] whitespace-nowrap">{{ $k->plat_nomor }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#0F172A]">{{ $k->merek }} {{ $k->model_kendaraan }}</p>
                                    <p class="text-xs text-[#475569]">
                                        {{ ucfirst($k->jenis_kendaraan) }} | {{ $k->tahun_pembuatan }} | {{ $k->kapasitas_cc }} cc | Urutan ke-{{ $k->urutan_kepemilikan }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-[#0F172A] tabular-nums whitespace-nowrap">
                                    Rp {{ number_format((float) $k->njkb, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-[#0F172A]">{{ $k->provinsi }}</p>
                                    <p class="text-xs text-[#475569]">Tempo: {{ $k->jatuh_tempo_stnk?->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <x-status-badge :status="$k->status_verifikasi" />
                                    @if ($k->status_verifikasi === 'rejected' && $k->catatan_petugas)
                                        <p class="mt-1 max-w-xs text-xs text-[#991B1B]">{{ $k->catatan_petugas }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('wp.kendaraan.show', $k) }}" class="inline-flex min-h-[44px] items-center rounded border border-[#64748B] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F2942] hover:bg-[#F4F6F9]">
                                            Detail
                                        </a>
                                        @if (in_array($k->status_verifikasi, ['pending', 'rejected'], true))
                                            <a href="{{ route('wp.kendaraan.edit', $k) }}" class="inline-flex min-h-[44px] items-center rounded bg-[#0F2942] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#163A5C]">
                                                Ubah
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if (method_exists($items, 'links') && $items->hasPages())
            <div class="mt-5">{{ $items->links() }}</div>
        @endif
    @endif
@endsection
