@extends('layouts.app')

@section('title', 'Daftar Tagihan Pajak')

@section('content')
    @php
        $items = $tagihanList ?? collect();
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Tagihan Pajak Kendaraan</h1>
            <p class="mt-1 text-sm text-[#475569]">
                Periksa kewajiban PKB tahunan, lakukan pembayaran simulasi, dan unduh e-TBPKP untuk tagihan yang telah lunas.
            </p>
        </div>
        <a href="{{ route('wp.kendaraan.index') }}" class="civic-btn-secondary shrink-0">
            Lihat Kendaraan Saya
        </a>
    </div>

    @if ($items->isEmpty())
        <x-empty-state
            title="Belum ada tagihan pajak"
            description="Tagihan pajak akan diterbitkan setelah kendaraan Anda disetujui oleh petugas verifikator."
            :action-href="route('wp.kendaraan.index')"
            action-label="Cek Status Kendaraan"
        />
    @else
        <div class="space-y-3 md:hidden">
            @foreach ($items as $t)
                <article class="civic-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-[#0F2942]">{{ $t->kendaraan?->plat_nomor ?? '-' }}</h2>
                            <p class="text-sm text-[#0F172A]">{{ $t->kendaraan?->merek }} {{ $t->kendaraan?->model_kendaraan }}</p>
                        </div>
                        <x-status-badge :status="$t->status" />
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 border-t border-[#E2E8F0] pt-3 text-xs">
                        <div>
                            <dt class="text-[#475569]">Tahun Pajak</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $t->tahun_pajak }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">Jatuh Tempo</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $t->jatuh_tempo?->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">Denda</dt>
                            <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format((float) $t->denda_keterlambatan, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[#475569]">Total Tagihan</dt>
                            <dd class="font-bold text-[#0F2942] tabular-nums">Rp {{ number_format((float) $t->total_tagihan, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('wp.tagihan.show', $t) }}" class="civic-btn-navy flex-1 text-xs">
                            {{ $t->isLunas() ? 'Lihat Rincian' : 'Bayar Tagihan' }}
                        </a>
                        @if ($t->isLunas() && $t->pembayaran)
                            <a href="{{ route('wp.pembayaran.etbpkp', $t->pembayaran) }}" class="civic-btn-primary flex-1 text-xs">
                                Unduh e-TBPKP
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
                            <th scope="col" class="px-5 py-3.5">Kendaraan</th>
                            <th scope="col" class="px-5 py-3.5">Tahun / Tempo</th>
                            <th scope="col" class="px-5 py-3.5">PKB & SWDKLLJ</th>
                            <th scope="col" class="px-5 py-3.5">Denda</th>
                            <th scope="col" class="px-5 py-3.5">Total Tagihan</th>
                            <th scope="col" class="px-5 py-3.5">Status</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach ($items as $t)
                            <tr class="hover:bg-[#F4F6F9]/60">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-[#0F2942]">{{ $t->kendaraan?->plat_nomor ?? '-' }}</p>
                                    <p class="text-xs text-[#475569]">{{ $t->kendaraan?->merek }} {{ $t->kendaraan?->model_kendaraan }}</p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-semibold text-[#0F172A]">Tahun {{ $t->tahun_pajak }}</p>
                                    <p class="text-xs text-[#475569]">Tempo {{ $t->jatuh_tempo?->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-5 py-4 text-xs text-[#475569] tabular-nums whitespace-nowrap">
                                    PKB+Opsen Rp {{ number_format((float) $t->pkb_pokok + (float) $t->pajak_progresif + (float) $t->opsen_pkb, 0, ',', '.') }}<br>
                                    SWDKLLJ Rp {{ number_format((float) $t->swdkllj, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 tabular-nums whitespace-nowrap {{ (float) $t->denda_keterlambatan > 0 ? 'font-semibold text-[#991B1B]' : 'text-[#475569]' }}">
                                    Rp {{ number_format((float) $t->denda_keterlambatan, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 font-bold text-[#0F172A] tabular-nums whitespace-nowrap">
                                    Rp {{ number_format((float) $t->total_tagihan, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <x-status-badge :status="$t->status" />
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('wp.tagihan.show', $t) }}" class="inline-flex min-h-[44px] items-center rounded bg-[#0F2942] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#163A5C]">
                                            {{ $t->isLunas() ? 'Detail' : 'Bayar' }}
                                        </a>
                                        @if ($t->isLunas() && $t->pembayaran)
                                            <a href="{{ route('wp.pembayaran.etbpkp', $t->pembayaran) }}" class="inline-flex min-h-[44px] items-center rounded bg-[#047857] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#065F46]">
                                                e-TBPKP PDF
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
