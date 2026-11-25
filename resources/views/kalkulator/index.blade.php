@extends(auth()->check() ? 'layouts.app' : 'layouts.public')

@section('title', 'Kalkulator Simulasi PKB')

@section('content')
    @php
        $isGuest = !auth()->check();
        $formInput = $input ?? [];
        $currentProv = old('provinsi', $formInput['provinsi'] ?? '');
        $currentJenis = old('jenis_kendaraan', $formInput['jenis_kendaraan'] ?? 'motor');
        $currentEntitas = old('jenis_entitas', $formInput['jenis_entitas'] ?? 'pribadi');
        $currentUrutan = (int) old('urutan_kepemilikan', $formInput['urutan_kepemilikan'] ?? 1);
    @endphp

    <div class="{{ $isGuest ? 'mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8' : '' }}">
        @if ($isGuest)
            <x-alert-banner />
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A] sm:text-3xl">Kalkulator Simulasi Pajak Kendaraan Bermotor</h1>
            <p class="mt-1.5 max-w-3xl text-sm text-[#475569]">
                Hitung estimasi PKB pokok, pajak progresif, SWDKLLJ, dan denda keterlambatan berdasarkan provinsi terdaftar serta kalender hari libur nasional.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            <section aria-labelledby="kalkulator-form-heading" class="civic-card p-5 sm:p-6 lg:col-span-6">
                <h2 id="kalkulator-form-heading" class="text-lg font-bold text-[#0F172A]">Parameter Kendaraan</h2>
                <p class="mt-1 text-xs text-[#475569]">Lengkapi seluruh bidang wajib bertanda bintang (*) untuk menjalankan simulasi.</p>

                <form method="POST" action="{{ route('kalkulator.hitung') }}" class="mt-5 space-y-4" data-loading-submit>
                    @csrf

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jenis_kendaraan" class="block text-sm font-semibold text-[#0F172A]">Jenis Kendaraan *</label>
                            <select id="jenis_kendaraan" name="jenis_kendaraan" required class="civic-input mt-1.5">
                                <option value="motor" @selected($currentJenis === 'motor')>Sepeda Motor</option>
                                <option value="mobil" @selected($currentJenis === 'mobil')>Mobil Penumpang / Barang</option>
                            </select>
                            @error('jenis_kendaraan')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_entitas" class="block text-sm font-semibold text-[#0F172A]">Status Kepemilikan *</label>
                            <select id="jenis_entitas" name="jenis_entitas" required class="civic-input mt-1.5">
                                <option value="pribadi" @selected($currentEntitas === 'pribadi')>Wajib Pajak Pribadi (Progresif)</option>
                                <option value="usaha" @selected($currentEntitas === 'usaha')>Badan Usaha (Tarif Rata)</option>
                            </select>
                            @error('jenis_entitas')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="provinsi" class="block text-sm font-semibold text-[#0F172A]">Provinsi Terdaftar *</label>
                        <select id="provinsi" name="provinsi" required class="civic-input mt-1.5">
                            <option value="">Pilih provinsi</option>
                            @foreach ($provinces ?? [] as $prov)
                                @php
                                    $provName = is_array($prov) ? ($prov['name'] ?? '') : (string) $prov;
                                @endphp
                                <option value="{{ $provName }}" @selected(strtoupper((string) $currentProv) === strtoupper($provName))>
                                    {{ $provName }}
                                </option>
                            @endforeach
                        </select>
                        @error('provinsi')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="merek" class="block text-sm font-semibold text-[#0F172A]">Merek *</label>
                            <input
                                id="merek"
                                name="merek"
                                type="text"
                                required
                                value="{{ old('merek', $formInput['merek'] ?? '') }}"
                                placeholder="Contoh: Honda"
                                class="civic-input mt-1.5"
                            >
                            @error('merek')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tahun_pembuatan" class="block text-sm font-semibold text-[#0F172A]">Tahun *</label>
                            <input
                                id="tahun_pembuatan"
                                name="tahun_pembuatan"
                                type="number"
                                min="1970"
                                max="{{ date('Y') + 1 }}"
                                required
                                value="{{ old('tahun_pembuatan', $formInput['tahun_pembuatan'] ?? date('Y') - 2) }}"
                                class="civic-input mt-1.5 tabular-nums"
                            >
                            @error('tahun_pembuatan')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="kapasitas_cc" class="block text-sm font-semibold text-[#0F172A]">Kapasitas CC *</label>
                            <input
                                id="kapasitas_cc"
                                name="kapasitas_cc"
                                type="number"
                                min="50"
                                max="15000"
                                required
                                value="{{ old('kapasitas_cc', $formInput['kapasitas_cc'] ?? 150) }}"
                                class="civic-input mt-1.5 tabular-nums"
                            >
                            @error('kapasitas_cc')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="njkb" class="block text-sm font-semibold text-[#0F172A]">NJKB dalam Rupiah (Opsional)</label>
                        <input
                            id="njkb"
                            name="njkb"
                            type="number"
                            min="100000"
                            step="100000"
                            value="{{ old('njkb', $formInput['njkb'] ?? '') }}"
                            placeholder="Kosongkan untuk estimasi otomatis dari API referensi"
                            class="civic-input mt-1.5 tabular-nums"
                        >
                        <p class="mt-1 text-xs text-[#475569]">
                            Jika dikosongkan, sistem mengambil estimasi NJKB otomatis dari API referensi pajak berdasarkan jenis, merek, tahun, dan CC.
                        </p>
                        @error('njkb')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="urutan_kepemilikan" class="block text-sm font-semibold text-[#0F172A]">Urutan Kepemilikan *</label>
                            <select id="urutan_kepemilikan" name="urutan_kepemilikan" required class="civic-input mt-1.5">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" @selected($currentUrutan === $i)>Kendaraan ke-{{ $i }}</option>
                                @endfor
                            </select>
                            @error('urutan_kepemilikan')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jatuh_tempo_stnk" class="block text-sm font-semibold text-[#0F172A]">Jatuh Tempo STNK *</label>
                            <input
                                id="jatuh_tempo_stnk"
                                name="jatuh_tempo_stnk"
                                type="date"
                                required
                                value="{{ old('jatuh_tempo_stnk', $formInput['jatuh_tempo_stnk'] ?? date('Y-m-d')) }}"
                                class="civic-input mt-1.5"
                            >
                            @error('jatuh_tempo_stnk')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" data-loading-text="Menghitung simulasi..." class="civic-btn-primary w-full">
                            Hitung Simulasi Pajak
                        </button>
                    </div>
                </form>
            </section>

            <section aria-labelledby="kalkulator-result-heading" class="lg:col-span-6">
                @include('kalkulator._result')
            </section>
        </div>
    </div>
@endsection
