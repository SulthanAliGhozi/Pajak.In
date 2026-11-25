@extends('layouts.app')

@section('title', 'Detail Tagihan PKB ' . ($tagihan->kendaraan?->plat_nomor ?? ''))

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">
                    Tagihan PKB {{ $tagihan->kendaraan?->plat_nomor }}
                </h1>
                <x-status-badge :status="$tagihan->status" />
            </div>
            <p class="mt-1 text-sm text-[#475569]">
                Tahun Pajak {{ $tagihan->tahun_pajak }} | Jatuh Tempo STNK {{ $tagihan->jatuh_tempo?->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('wp.tagihan.index') }}" class="civic-btn-secondary shrink-0">
            Kembali ke Daftar Tagihan
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
        <section aria-labelledby="rincian-tagihan-heading" class="civic-card overflow-hidden lg:col-span-7">
            <div class="border-b border-[#E2E8F0] bg-[#0F2942] px-5 py-4 text-white sm:px-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="rincian-tagihan-heading" class="text-base font-bold">Rincian Komponen Tagihan</h2>
                    @if (!empty($tagihan->tarif_sumber))
                        <x-status-badge :status="$tagihan->tarif_sumber" />
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-[#CBD5E1]">
                    {{ $tagihan->kendaraan?->merek }} {{ $tagihan->kendaraan?->model_kendaraan }} ({{ $tagihan->kendaraan?->tahun_pembuatan }}) | {{ $tagihan->kendaraan?->provinsi }}
                </p>
                @if (!empty($tagihan->regulation_note))
                    <p class="mt-1 text-xs text-[#E2E8F0]">{{ $tagihan->regulation_note }}</p>
                @endif
            </div>

            <div class="p-5 sm:p-6">
                <table class="w-full text-sm">
                    <caption class="sr-only">Rincian tagihan pajak kendaraan bermotor</caption>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        <tr>
                            <th scope="row" class="py-3 pr-4 text-left font-medium text-[#475569]">PKB Pokok</th>
                            <td class="py-3 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format((float) $tagihan->pkb_pokok, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-3 pr-4 text-left font-medium text-[#475569]">Pajak Progresif (Kepemilikan ke-{{ $tagihan->kendaraan?->urutan_kepemilikan ?? 1 }})</th>
                            <td class="py-3 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format((float) $tagihan->pajak_progresif, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-3 pr-4 text-left font-medium text-[#475569]">Opsen PKB Kab/Kota</th>
                            <td class="py-3 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format((float) $tagihan->opsen_pkb, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-3 pr-4 text-left font-medium text-[#475569]">SWDKLLJ</th>
                            <td class="py-3 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format((float) $tagihan->swdkllj, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-3 pr-4 text-left font-medium text-[#475569]">Denda Keterlambatan</th>
                            <td class="py-3 text-right font-semibold {{ (float) $tagihan->denda_keterlambatan > 0 ? 'text-[#991B1B]' : 'text-[#0F172A]' }} tabular-nums">
                                Rp {{ number_format((float) $tagihan->denda_keterlambatan, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="bg-[#F4F6F9]">
                            <th scope="row" class="py-3.5 pl-2 pr-4 text-left text-base font-bold text-[#0F2942]">Total Tagihan</th>
                            <td class="py-3.5 pr-2 text-right text-lg font-bold text-[#047857] tabular-nums">
                                Rp {{ number_format((float) $tagihan->total_tagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section aria-labelledby="aksi-pembayaran-heading" class="lg:col-span-5">
            @if ($tagihan->isLunas() && $tagihan->pembayaran)
                <div class="civic-card p-5 sm:p-6">
                    <h2 id="aksi-pembayaran-heading" class="text-base font-bold text-[#065F46]">Bukti Pelunasan Resmi (e-TBPKP)</h2>
                    <p class="mt-1 text-sm text-[#475569]">
                        Tagihan ini telah dilunasi. Anda dapat mengunduh dokumen e-TBPKP resmi dalam format PDF.
                    </p>

                    <dl class="mt-4 divide-y divide-[#E2E8F0] border-t border-b border-[#E2E8F0] text-sm">
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-[#475569]">Nomor e-TBPKP</dt>
                            <dd class="font-bold text-[#0F2942] tabular-nums">{{ $tagihan->pembayaran->no_etbpkp }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-[#475569]">Kode Transaksi</dt>
                            <dd class="font-semibold text-[#0F172A] tabular-nums">{{ $tagihan->pembayaran->kode_transaksi }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-[#475569]">Metode Bayar</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $tagihan->pembayaran->metode_pembayaran }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-[#475569]">Tanggal Bayar</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $tagihan->pembayaran->tanggal_bayar?->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2.5">
                            <dt class="text-[#475569]">Nominal Dibayar</dt>
                            <dd class="font-bold text-[#065F46] tabular-nums">
                                Rp {{ number_format((float) $tagihan->pembayaran->nominal_dibayar, 0, ',', '.') }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-5">
                        <a href="{{ route('wp.pembayaran.etbpkp', $tagihan->pembayaran) }}" class="civic-btn-primary w-full">
                            Unduh e-TBPKP (PDF)
                        </a>
                    </div>
                </div>
            @else
                <div class="civic-card p-5 sm:p-6">
                    <h2 id="aksi-pembayaran-heading" class="text-base font-bold text-[#0F2942]">Pelunasan Tagihan Pajak</h2>
                    <p class="mt-1 text-sm text-[#475569]">
                        Bayar tagihan menggunakan Midtrans Snap (Sandbox) untuk simulasi Virtual Account, QRIS, atau E-Wallet. Denda terbaru dihitung ulang otomatis saat transaksi dibuat.
                    </p>

                    <div class="mt-4 rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-3.5 text-xs text-[#475569]">
                        Total yang harus dibayar:
                        <strong class="block mt-1 text-base font-bold text-[#0F2942] tabular-nums">
                            Rp {{ number_format((float) $tagihan->total_tagihan, 0, ',', '.') }}
                        </strong>
                    </div>

                    <div id="midtrans-status-box" role="status" class="hidden mt-4 rounded-md border px-3.5 py-2.5 text-xs font-medium"></div>

                    <div class="mt-4 space-y-2.5">
                        <button
                            type="button"
                            data-midtrans-pay
                            data-token-url="{{ route('wp.tagihan.midtrans.token', $tagihan) }}"
                            data-sync-url="{{ route('wp.tagihan.midtrans.sync', $tagihan) }}"
                            class="civic-btn-primary w-full"
                        >
                            Bayar via Midtrans Snap (Sandbox)
                        </button>

                        @if (!empty($tagihan->midtrans_order_id))
                            <div class="rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-3 text-xs text-[#475569] space-y-2">
                                <p>
                                    Order Midtrans terakhir: <strong class="text-[#0F172A] tabular-nums">{{ $tagihan->midtrans_order_id }}</strong>
                                    ({{ strtoupper((string) ($tagihan->midtrans_status ?? 'pending')) }})
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('wp.tagihan.midtrans.sync', $tagihan) }}" data-loading-submit>
                                        @csrf
                                        <button type="submit" data-loading-text="Memeriksa status..." class="civic-btn-navy text-xs">
                                            Periksa Status Midtrans
                                        </button>
                                    </form>
                                    @if (!empty($tagihan->midtrans_redirect_url))
                                        <a href="{{ $tagihan->midtrans_redirect_url }}" target="_blank" rel="noopener" class="civic-btn-secondary text-xs">
                                            Buka Halaman Midtrans
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <details class="mt-5 border-t border-[#E2E8F0] pt-4">
                        <summary class="text-xs font-semibold text-[#0F2942] hover:underline">
                            Opsi Cadangan: Simulasi Pelunasan Instan (Offline)
                        </summary>
                        <form method="POST" action="{{ route('wp.tagihan.bayar', $tagihan) }}" class="mt-3 space-y-3" data-loading-submit>
                            @csrf
                            <div>
                                <label for="metode_pembayaran" class="block text-xs font-semibold text-[#0F172A]">Metode Pembayaran *</label>
                                <select id="metode_pembayaran" name="metode_pembayaran" required class="civic-input mt-1">
                                    <option value="Virtual Account Bank" @selected(old('metode_pembayaran') === 'Virtual Account Bank')>Virtual Account Bank</option>
                                    <option value="QRIS Simulasi" @selected(old('metode_pembayaran') === 'QRIS Simulasi')>QRIS Simulasi</option>
                                    <option value="Loket Samsat" @selected(old('metode_pembayaran') === 'Loket Samsat')>Loket Samsat</option>
                                </select>
                                @error('metode_pembayaran')
                                    <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" data-loading-text="Memproses pembayaran..." class="civic-btn-secondary w-full text-xs">
                                Konfirmasi Pelunasan Offline
                            </button>
                        </form>
                    </details>

                    @if (!empty(config('services.midtrans.client_key')))
                        @php
                            $snapScriptUrl = config('services.midtrans.is_production')
                                ? 'https://app.midtrans.com/snap/snap.js'
                                : 'https://app.sandbox.midtrans.com/snap/snap.js';
                        @endphp
                        <script src="{{ $snapScriptUrl }}" data-client-key="{{ config('services.midtrans.client_key') }}" defer></script>
                    @endif
                </div>
            @endif
        </section>
    </div>
@endsection
