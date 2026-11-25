@extends('layouts.app')

@section('title', 'Detail Kendaraan ' . $kendaraan->plat_nomor)

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">{{ $kendaraan->plat_nomor }}</h1>
                <x-status-badge :status="$kendaraan->status_verifikasi" />
            </div>
            <p class="mt-1 text-sm text-[#475569]">
                {{ $kendaraan->merek }} {{ $kendaraan->model_kendaraan }} ({{ $kendaraan->tahun_pembuatan }})
            </p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('wp.kendaraan.index') }}" class="civic-btn-secondary">
                Kembali ke Daftar
            </a>
            @if (in_array($kendaraan->status_verifikasi, ['pending', 'rejected'], true))
                <a href="{{ route('wp.kendaraan.edit', $kendaraan) }}" class="civic-btn-primary">
                    Ubah Data & Berkas
                </a>
            @endif
        </div>
    </div>

    @if ($kendaraan->status_verifikasi === 'rejected')
        <div role="alert" class="mb-6 rounded-md border border-[#EF4444]/50 bg-[#FEE2E2] p-4 text-sm text-[#991B1B]">
            <p class="font-bold">Pengajuan Verifikasi Ditolak</p>
            <p class="mt-1">
                Alasan petugas: {{ $kendaraan->catatan_petugas ?: 'Periksa kembali kesesuaian data kendaraan serta kejelasan berkas KTP dan STNK.' }}
            </p>
            <div class="mt-3">
                <a href="{{ route('wp.kendaraan.edit', $kendaraan) }}" class="inline-flex min-h-[44px] items-center rounded bg-[#991B1B] px-4 py-2 text-xs font-semibold text-white hover:bg-[#7F1D1D]">
                    Perbaiki Data & Ajukan Ulang
                </a>
            </div>
        </div>
    @elseif ($kendaraan->status_verifikasi === 'approved' && $kendaraan->catatan_petugas)
        <div role="status" class="mb-6 rounded-md border border-[#10B981]/50 bg-[#D1FAE5] p-4 text-sm text-[#065F46]">
            <p class="font-bold">Catatan Verifikasi Petugas</p>
            <p class="mt-0.5">{{ $kendaraan->catatan_petugas }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
        <div class="space-y-6 lg:col-span-7">
            <section aria-labelledby="spec-heading" class="civic-card p-5 sm:p-6">
                <h2 id="spec-heading" class="text-base font-bold text-[#0F2942]">Spesifikasi Kendaraan</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Jenis Kendaraan</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">{{ $kendaraan->jenis_kendaraan === 'mobil' ? 'Mobil' : 'Sepeda Motor' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Kapasitas Mesin</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A] tabular-nums">{{ $kendaraan->kapasitas_cc }} cc</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">NJKB</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format((float) $kendaraan->njkb, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Urutan Kepemilikan</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">Kendaraan ke-{{ $kendaraan->urutan_kepemilikan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Provinsi Terdaftar</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">{{ $kendaraan->provinsi }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-[#475569]">Jatuh Tempo STNK</dt>
                        <dd class="mt-0.5 font-semibold text-[#0F172A]">{{ $kendaraan->jatuh_tempo_stnk?->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </section>

            <section aria-labelledby="berkas-heading" class="civic-card p-5 sm:p-6">
                <h2 id="berkas-heading" class="text-base font-bold text-[#0F2942]">Berkas Terunggah (Penyimpanan Privat)</h2>
                @if ($kendaraan->berkas->isEmpty())
                    <p class="mt-3 text-sm text-[#475569]">Belum ada berkas KTP atau STNK yang terunggah untuk kendaraan ini.</p>
                @else
                    <ul class="mt-4 divide-y divide-[#E2E8F0] border-t border-b border-[#E2E8F0]">
                        @foreach ($kendaraan->berkas as $b)
                            <li class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-[#0F172A]">
                                        {{ strtoupper($b->jenis_berkas) }}: {{ $b->original_name }}
                                    </p>
                                    <p class="text-xs text-[#475569]">
                                        Ukuran {{ number_format($b->size_bytes / 1024, 1) }} KB | Diunggah {{ $b->uploaded_at?->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <a href="{{ route('berkas.show', $b) }}" target="_blank" rel="noopener" class="civic-btn-secondary shrink-0 text-xs">
                                    Lihat Berkas
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section aria-labelledby="riwayat-tagihan-heading" class="civic-card p-5 sm:p-6">
                <h2 id="riwayat-tagihan-heading" class="text-base font-bold text-[#0F2942]">Riwayat Tagihan Pajak</h2>
                @if ($kendaraan->tagihan->isEmpty())
                    <p class="mt-3 text-sm text-[#475569]">
                        Tagihan pajak belum diterbitkan. Tagihan dibuat otomatis setelah pengajuan kendaraan disetujui petugas.
                    </p>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($kendaraan->tagihan as $t)
                            <div class="flex flex-col gap-3 rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-[#0F2942]">Tahun Pajak {{ $t->tahun_pajak }}</p>
                                        <x-status-badge :status="$t->status" />
                                    </div>
                                    <p class="mt-1 text-xs text-[#475569]">
                                        Jatuh tempo: {{ $t->jatuh_tempo?->format('d/m/Y') }} | Total: <strong class="text-[#0F172A] tabular-nums">Rp {{ number_format((float) $t->total_tagihan, 0, ',', '.') }}</strong>
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('wp.tagihan.show', $t) }}" class="civic-btn-navy text-xs">
                                        {{ $t->isLunas() ? 'Detail Pembayaran' : 'Bayar Sekarang' }}
                                    </a>
                                    @if ($t->isLunas() && $t->pembayaran)
                                        <a href="{{ route('wp.pembayaran.etbpkp', $t->pembayaran) }}" class="civic-btn-primary text-xs">
                                            Unduh e-TBPKP
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside aria-labelledby="simulasi-heading" class="lg:col-span-5 space-y-5">
            @if (isset($simulasi) && $simulasi !== null)
                <div class="civic-card overflow-hidden">
                    <div class="border-b border-[#E2E8F0] bg-[#0F2942] px-5 py-4 text-white">
                        <h2 id="simulasi-heading" class="text-base font-bold">Simulasi Kewajiban PKB Saat Ini</h2>
                        <p class="mt-0.5 text-xs text-[#CBD5E1]">{{ $simulasi->ruleName }}</p>
                    </div>
                    <div class="p-5">
                        <dl class="divide-y divide-[#E2E8F0] text-sm">
                            <div class="flex justify-between py-2.5">
                                <dt class="text-[#475569]">PKB Pokok</dt>
                                <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format($simulasi->pkbPokok, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <dt class="text-[#475569]">Pajak Progresif</dt>
                                <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format($simulasi->pajakProgresif, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <dt class="text-[#475569]">Opsen PKB ({{ number_format($simulasi->opsenRate * 100, 0, ',', '.') }}%)</dt>
                                <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format($simulasi->opsenPkb, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <dt class="text-[#475569]">SWDKLLJ</dt>
                                <dd class="font-semibold text-[#0F172A] tabular-nums">Rp {{ number_format($simulasi->swdkllj, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between py-2.5">
                                <dt class="text-[#475569]">Denda ({{ $simulasi->bulanTerlambat }} bulan)</dt>
                                <dd class="font-semibold {{ $simulasi->dendaKeterlambatan > 0 ? 'text-[#991B1B]' : 'text-[#0F172A]' }} tabular-nums">
                                    Rp {{ number_format($simulasi->dendaKeterlambatan, 0, ',', '.') }}
                                </dd>
                            </div>
                            <div class="flex justify-between py-3 font-bold text-[#0F2942]">
                                <dt>Estimasi Total</dt>
                                <dd class="text-[#047857] tabular-nums">Rp {{ number_format($simulasi->totalTagihan, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @else
                <div class="civic-card overflow-hidden">
                    <div class="border-b border-[#E2E8F0] bg-[#0F2942] px-5 py-4 text-white">
                        <h2 id="simulasi-heading" class="text-base font-bold">Ringkasan Status Pemeriksaan</h2>
                    </div>
                    <div class="p-5 text-sm space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[#475569]">Status Verifikasi</span>
                            <x-status-badge :status="$kendaraan->status_verifikasi" />
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[#475569]">Diperiksa Oleh</span>
                            <span class="font-semibold text-[#0F172A]">{{ $kendaraan->verifier?->name ?? 'Menunggu petugas' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[#475569]">Tanggal Verifikasi</span>
                            <span class="font-semibold text-[#0F172A]">{{ $kendaraan->verified_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="pt-3 border-t border-[#E2E8F0]">
                            <a href="{{ route('kalkulator.index') }}" class="civic-btn-secondary w-full text-xs">
                                Buka Kalkulator Simulasi PKB
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </aside>
    </div>
@endsection
