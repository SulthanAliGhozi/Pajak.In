<aside class="space-y-6 lg:col-span-5">
    @if (isset($previewCalculation) && $previewCalculation !== null)
        <section aria-labelledby="pratinjau-tagihan-heading" class="civic-card overflow-hidden">
            <div class="border-b border-[#E2E8F0] bg-[#0F2942] px-5 py-4 text-white">
                <h2 id="pratinjau-tagihan-heading" class="text-base font-bold">Pratinjau Tagihan Jika Disetujui</h2>
                <p class="mt-0.5 text-xs text-[#CBD5E1]">{{ $previewCalculation->ruleName }}</p>
            </div>
            <div class="p-5 text-sm">
                <dl class="divide-y divide-[#E2E8F0]">
                    <div class="flex justify-between py-2">
                        <dt class="text-[#475569]">PKB Pokok + Progresif</dt>
                        <dd class="font-semibold text-[#0F172A] tabular-nums">
                            Rp {{ number_format($previewCalculation->pkbPokok + $previewCalculation->pajakProgresif, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-[#475569]">Opsen PKB ({{ number_format($previewCalculation->opsenRate * 100, 0, ',', '.') }}%)</dt>
                        <dd class="font-semibold text-[#0F172A] tabular-nums">
                            Rp {{ number_format($previewCalculation->opsenPkb, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-[#475569]">SWDKLLJ</dt>
                        <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format($previewCalculation->swdkllj, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-[#475569]">Denda ({{ $previewCalculation->bulanTerlambat }} bln)</dt>
                        <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format($previewCalculation->dendaKeterlambatan, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5 font-bold text-[#0F2942]">
                        <dt>Estimasi Total Tagihan</dt>
                        <dd class="text-[#047857] tabular-nums">Rp {{ number_format($previewCalculation->totalTagihan, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>
        </section>
    @endif

    <section aria-labelledby="keputusan-approve-heading" class="civic-card p-5 sm:p-6">
        <h2 id="keputusan-approve-heading" class="text-base font-bold text-[#065F46]">Setujui & Terbitkan Tagihan</h2>
        <p class="mt-1 text-xs text-[#475569]">
            Anda dapat menyesuaikan nilai NJKB sebelum menerbitkan tagihan resmi untuk kendaraan ini.
        </p>

        <form method="POST" action="{{ route('petugas.verifikasi.approve', $kendaraan) }}" class="mt-4 space-y-4" data-loading-submit>
            @csrf
            <div>
                <div class="flex items-center justify-between gap-2">
                    <label for="njkb-approve-input" class="block text-sm font-semibold text-[#0F172A]">NJKB Final (Rp) *</label>
                    @if ($refNjkbValue > 0)
                        <button type="button" data-apply-njkb="{{ (int) $refNjkbValue }}" class="text-xs font-semibold text-[#047857] underline hover:text-[#065F46]">
                            Pakai Referensi
                        </button>
                    @endif
                </div>
                <input
                    id="njkb-approve-input"
                    name="njkb"
                    type="number"
                    min="100000"
                    step="100000"
                    required
                    value="{{ old('njkb', (int) $kendaraan->njkb) }}"
                    class="civic-input mt-1.5 tabular-nums"
                >
                @error('njkb')
                    <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="catatan_approve" class="block text-sm font-semibold text-[#0F172A]">Catatan Petugas (Opsional)</label>
                <textarea
                    id="catatan_approve"
                    name="catatan_petugas"
                    rows="2"
                    placeholder="Contoh: Berkas KTP dan STNK lengkap dan sesuai."
                    class="civic-input mt-1.5"
                >{{ old('catatan_petugas') }}</textarea>
            </div>

            <button type="submit" data-loading-text="Menyetujui & menerbitkan tagihan..." class="civic-btn-primary w-full">
                Setujui & Terbitkan Tagihan
            </button>
        </form>
    </section>

    <section aria-labelledby="keputusan-reject-heading" class="civic-card p-5 sm:p-6">
        <h2 id="keputusan-reject-heading" class="text-base font-bold text-[#991B1B]">Tolak Pengajuan</h2>
        <p class="mt-1 text-xs text-[#475569]">
            Sertakan alasan penolakan yang jelas agar wajib pajak dapat memperbaiki data atau mengunggah ulang berkas.
        </p>

        <form method="POST" action="{{ route('petugas.verifikasi.reject', $kendaraan) }}" class="mt-4 space-y-4" data-loading-submit>
            @csrf
            <div>
                <label for="catatan_reject" class="block text-sm font-semibold text-[#0F172A]">Alasan Penolakan *</label>
                <textarea
                    id="catatan_reject"
                    name="catatan_petugas"
                    rows="3"
                    required
                    placeholder="Contoh: Foto STNK tidak terbaca atau nomor polisi tidak sesuai."
                    class="civic-input mt-1.5"
                ></textarea>
                @error('catatan_petugas')
                    <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" data-loading-text="Menolak pengajuan..." class="civic-btn-danger w-full">
                Tolak Pengajuan Kendaraan
            </button>
        </form>
    </section>
</aside>
