@extends('layouts.app')

@section('title', 'Daftar Verifikasi Kendaraan')

@section('content')
    @php
        $items = $kendaraanList ?? collect();
        $activeFilter = $statusFilter ?? 'pending';
        $tabs = [
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'all' => 'Semua Status',
        ];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Verifikasi Pengajuan Kendaraan</h1>
            <p class="mt-1 text-sm text-[#475569]">
                Saring pengajuan berdasarkan status verifikasi untuk meninjau berkas atau menelusuri riwayat pemeriksaan.
            </p>
        </div>
        <a href="{{ route('petugas.dashboard') }}" class="civic-btn-secondary shrink-0">
            Kembali ke Antrean
        </a>
    </div>

    <nav aria-label="Filter status verifikasi" class="mb-5 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a
                href="{{ route('petugas.verifikasi.index', ['status' => $key]) }}"
                class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm font-semibold transition-colors {{ $activeFilter === $key ? 'border-[#0F2942] bg-[#0F2942] text-white' : 'border-[#64748B] bg-white text-[#0F2942] hover:bg-[#F4F6F9]' }}"
                @if ($activeFilter === $key) aria-current="page" @endif
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($items->isEmpty())
        <x-empty-state
            title="Tidak ada kendaraan pada filter ini"
            description="Belum ada pengajuan kendaraan dengan status yang dipilih. Coba pilih filter status lainnya."
            :action-href="route('petugas.verifikasi.index', ['status' => 'all'])"
            action-label="Tampilkan Semua Status"
        />
    @else
        <div class="space-y-3 md:hidden">
            @foreach ($items as $k)
                <article class="civic-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-[#0F2942]">{{ $k->plat_nomor }}</h2>
                            <p class="text-sm text-[#0F172A]">{{ $k->merek }} {{ $k->model_kendaraan }}</p>
                            <p class="text-xs text-[#475569]">Pemilik: {{ $k->user?->name ?? '-' }}</p>
                        </div>
                        <x-status-badge :status="$k->status_verifikasi" />
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 border-t border-[#E2E8F0] pt-3 text-xs">
                        <div>
                            <dt class="text-[#475569]">Provinsi</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $k->provinsi }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">NJKB Diajukan</dt>
                            <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format((float) $k->njkb, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4">
                        <a href="{{ route('petugas.verifikasi.show', $k) }}" class="civic-btn-primary w-full text-xs">
                            Periksa Detail & Berkas
                        </a>
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
                            <th scope="col" class="px-5 py-3.5">Wajib Pajak</th>
                            <th scope="col" class="px-5 py-3.5">Spesifikasi Kendaraan</th>
                            <th scope="col" class="px-5 py-3.5">NJKB Diajukan</th>
                            <th scope="col" class="px-5 py-3.5">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach ($items as $k)
                            <tr class="hover:bg-[#F4F6F9]/60">
                                <td class="px-5 py-4 font-bold text-[#0F2942] whitespace-nowrap">{{ $k->plat_nomor }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#0F172A]">{{ $k->user?->name ?? '-' }}</p>
                                    <p class="text-xs text-[#475569]">
                                        {{ $k->user?->profile?->jenis_entitas === 'usaha' ? 'Badan Usaha' : 'Pribadi' }} | {{ $k->provinsi }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-[#0F172A]">{{ $k->merek }} {{ $k->model_kendaraan }}</p>
                                    <p class="text-xs text-[#475569]">{{ ucfirst($k->jenis_kendaraan) }} | {{ $k->tahun_pembuatan }} | {{ $k->kapasitas_cc }} cc</p>
                                </td>
                                <td class="px-5 py-4 font-semibold text-[#0F172A] tabular-nums whitespace-nowrap">
                                    Rp {{ number_format((float) $k->njkb, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$k->status_verifikasi" />
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('petugas.verifikasi.show', $k) }}" class="inline-flex min-h-[44px] items-center rounded bg-[#0F2942] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#163A5C]">
                                        Periksa Berkas
                                    </a>
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
