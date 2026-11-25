@if (isset($result) && $result !== null)
    @php
        $sourceBadge = $apiSource ?? $result->tarifSumber ?? 'fallback';
    @endphp
    <div class="civic-card overflow-hidden">
        <div class="border-b border-[#E2E8F0] bg-[#0F2942] px-5 py-4 text-white sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="kalkulator-result-heading" class="text-base font-bold text-white">Hasil Rincian Simulasi PKB</h2>
                <x-status-badge :status="$sourceBadge" />
            </div>
            <p class="mt-1 text-xs text-[#CBD5E1]">
                Aturan: {{ $result->ruleName }} | Provinsi: {{ $result->provinsi }}
            </p>
            @if (!empty($result->regulationNote))
                <p class="mt-1 text-xs text-[#E2E8F0]">{{ $result->regulationNote }}</p>
            @endif
        </div>

        <div class="p-5 sm:p-6">
            <div class="rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#475569]">Estimasi Total Tagihan</p>
                <p class="mt-1 text-2xl font-bold text-[#0F2942] sm:text-3xl tabular-nums">
                    Rp {{ number_format($result->totalTagihan, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-[#475569]">
                    Jatuh tempo efektif (penyesuaian hari libur): <span class="font-semibold text-[#0F172A]">{{ $result->effectiveDueDate }}</span>
                </p>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-sm">
                    <caption class="sr-only">Rincian komponen pajak kendaraan bermotor</caption>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-[#475569]">NJKB Dasar</th>
                            <td class="py-2.5 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format($result->njkb, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-[#475569]">
                                PKB Pokok ({{ number_format($result->tarifDasar * 100, 2, ',', '.') }}%)
                            </th>
                            <td class="py-2.5 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format($result->pkbPokok, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-[#475569]">
                                Pajak Progresif (Urutan ke-{{ $result->urutanKepemilikan }}, tarif tambahan {{ number_format($result->tarifProgresif * 100, 2, ',', '.') }}%)
                            </th>
                            <td class="py-2.5 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format($result->pajakProgresif, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-[#475569]">
                                Opsen PKB Kab/Kota ({{ number_format($result->opsenRate * 100, 0, ',', '.') }}%)
                            </th>
                            <td class="py-2.5 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format($result->opsenPkb, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-[#475569]">SWDKLLJ</th>
                            <td class="py-2.5 text-right font-semibold text-[#0F172A] tabular-nums">
                                Rp {{ number_format($result->swdkllj, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2.5 pr-4 text-left font-medium text-[#475569]">
                                Denda Keterlambatan ({{ $result->bulanTerlambat }} bulan)
                            </th>
                            <td class="py-2.5 text-right font-semibold {{ $result->dendaKeterlambatan > 0 ? 'text-[#991B1B]' : 'text-[#0F172A]' }} tabular-nums">
                                Rp {{ number_format($result->dendaKeterlambatan, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="bg-[#F4F6F9]">
                            <th scope="row" class="py-3 pl-2 pr-4 text-left font-bold text-[#0F2942]">Total Tagihan</th>
                            <td class="py-3 pr-2 text-right font-bold text-[#047857] tabular-nums">
                                Rp {{ number_format($result->totalTagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @guest
                <div class="mt-6 flex flex-col gap-3 rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-[#475569]">
                        Ingin mendaftarkan kendaraan ini agar tagihan resmi dan e-TBPKP dapat diterbitkan?
                    </p>
                    <a href="{{ route('register') }}" class="civic-btn-navy shrink-0 text-xs">
                        Daftar Wajib Pajak
                    </a>
                </div>
            @endguest
        </div>
    </div>
@else
    <div class="civic-card p-6 sm:p-8">
        <h2 id="kalkulator-result-heading" class="text-lg font-bold text-[#0F172A]">Hasil Simulasi Belum Tersedia</h2>
        <p class="mt-2 text-sm text-[#475569] leading-relaxed">
            Isi parameter kendaraan di formulir sebelah kiri lalu tekan tombol <strong class="font-semibold text-[#0F172A]">Hitung Simulasi Pajak</strong>. Rincian PKB pokok, tarif progresif, SWDKLLJ, penyesuaian jatuh tempo hari libur, serta denda keterlambatan akan ditampilkan di panel ini.
        </p>
        <div class="mt-5 rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4 text-xs text-[#475569] space-y-1.5">
            <p class="font-semibold text-[#0F172A]">Catatan Perhitungan:</p>
            <p>1. Wajib pajak pribadi dikenakan tarif progresif mulai kepemilikan ke-2.</p>
            <p>2. Wajib pajak badan usaha menggunakan tarif rata sesuai provinsi tanpa tarif progresif.</p>
            <p>3. Jika tanggal jatuh tempo STNK jatuh pada hari libur nasional atau akhir pekan, jatuh tempo efektif digeser ke hari kerja berikutnya.</p>
        </div>
    </div>
@endif
