@extends('layouts.app')

@section('title', 'Daftar Tagihan Pajak Petugas')

@section('content')
    @php
        $items = $tagihanList ?? collect();
        $activeFilter = $statusFilter ?? 'all';
        $tabs = [
            'all' => 'Semua Tagihan',
            'belum_lunas' => 'Belum Lunas',
            'lunas' => 'Lunas',
        ];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Daftar Tagihan Pajak Kendaraan</h1>
            <p class="mt-1 text-sm text-[#475569]">
                Pantau status pelunasan tagihan pajak kendaraan dan unduh salinan e-TBPKP untuk tagihan berstatus lunas.
            </p>
        </div>
        <a href="{{ route('petugas.dashboard') }}" class="civic-btn-secondary shrink-0">
            Kembali ke Antrean
        </a>
    </div>

    <nav aria-label="Filter status tagihan" class="mb-5 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a
                href="{{ route('petugas.tagihan.index', ['status' => $key]) }}"
                class="inline-flex min-h-[44px] items-center rounded-md border px-4 py-2 text-sm font-semibold transition-colors {{ $activeFilter === $key ? 'border-[#0F2942] bg-[#0F2942] text-white' : 'border-[#64748B] bg-white text-[#0F2942] hover:bg-[#F4F6F9]' }}"
                @if ($activeFilter === $key) aria-current="page" @endif
            >
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($items->isEmpty())
        <x-empty-state
            title="Tidak ada data tagihan pada filter ini"
            description="Belum ada tagihan pajak dengan status yang dipilih. Coba tampilkan semua status tagihan."
            :action-href="route('petugas.tagihan.index', ['status' => 'all'])"
            action-label="Tampilkan Semua Tagihan"
        />
    @else
        <div class="space-y-3 md:hidden">
            @foreach ($items as $t)
                <article class="civic-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-[#0F2942]">{{ $t->kendaraan?->plat_nomor ?? '-' }}</h2>
                            <p class="text-sm text-[#0F172A]">{{ $t->kendaraan?->user?->name ?? '-' }}</p>
                        </div>
                        <x-status-badge :status="$t->status" />
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 border-t border-[#E2E8F0] pt-3 text-xs">
                        <div>
                            <dt class="text-[#475569]">Tahun / Tempo</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $t->tahun_pajak }} ({{ $t->jatuh_tempo?->format('d/m/Y') }})</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">Total Tagihan</dt>
                            <dd class="font-bold text-[#0F2942] tabular-nums">Rp {{ number_format((float) $t->total_tagihan, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                    @if ($t->isLunas() && $t->pembayaran)
                        <div class="mt-4">
                            <a href="{{ route('petugas.pembayaran.etbpkp', $t->pembayaran) }}" class="civic-btn-primary w-full text-xs">
                                Unduh e-TBPKP (PDF)
                            </a>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="hidden md:block civic-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-[#E2E8F0] bg-[#F4F6F9] text-xs font-semibold uppercase tracking-wide text-[#475569]">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Kendaraan</th>
                            <th scope="col" class="px-5 py-3.5">Wajib Pajak</th>
                            <th scope="col" class="px-5 py-3.5">Tahun / Tempo</th>
                            <th scope="col" class="px-5 py-3.5">Total Tagihan</th>
                            <th scope="col" class="px-5 py-3.5">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Bukti Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach ($items as $t)
                            <tr class="hover:bg-[#F4F6F9]/60">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-[#0F2942]">{{ $t->kendaraan?->plat_nomor ?? '-' }}</p>
                                    <p class="text-xs text-[#475569]">{{ $t->kendaraan?->merek }} {{ $t->kendaraan?->model_kendaraan }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-[#0F172A]">{{ $t->kendaraan?->user?->name ?? '-' }}</p>
                                    <p class="text-xs text-[#475569]">{{ $t->kendaraan?->provinsi }}</p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-semibold text-[#0F172A]">Tahun {{ $t->tahun_pajak }}</p>
                                    <p class="text-xs text-[#475569]">Tempo {{ $t->jatuh_tempo?->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-5 py-4 font-bold text-[#0F172A] tabular-nums whitespace-nowrap">
                                    Rp {{ number_format((float) $t->total_tagihan, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$t->status" />
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    @if ($t->isLunas() && $t->pembayaran)
                                        <a href="{{ route('petugas.pembayaran.etbpkp', $t->pembayaran) }}" class="inline-flex min-h-[44px] items-center rounded bg-[#047857] px-3.5 py-2 text-xs font-semibold text-white hover:bg-[#065F46]">
                                            Unduh e-TBPKP
                                        </a>
                                    @else
                                        <span class="text-xs text-[#475569]">Menunggu pembayaran</span>
                                    @endif
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
