@php
    $vehicle = $kendaraan ?? null;
    $isEdit = $vehicle !== null;
    $activeProfile = $profile ?? auth()->user()?->profile;
    $defaultProv = $isEdit ? $vehicle->provinsi : ($activeProfile?->provinsi ?? '');
    $previewPrefix = $isEdit ? 'edit' : 'create';
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label for="jenis_kendaraan" class="block text-sm font-semibold text-[#0F172A]">Jenis Kendaraan *</label>
        <select id="jenis_kendaraan" name="jenis_kendaraan" required class="civic-input mt-1.5">
            <option value="motor" @selected(old('jenis_kendaraan', $vehicle?->jenis_kendaraan ?? 'motor') === 'motor')>Sepeda Motor</option>
            <option value="mobil" @selected(old('jenis_kendaraan', $vehicle?->jenis_kendaraan) === 'mobil')>Mobil Penumpang / Barang</option>
        </select>
        @error('jenis_kendaraan')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="plat_nomor" class="block text-sm font-semibold text-[#0F172A]">Nomor Polisi (Plat Nomor) *</label>
        <input
            id="plat_nomor"
            name="plat_nomor"
            type="text"
            maxlength="20"
            required
            value="{{ old('plat_nomor', $vehicle?->plat_nomor) }}"
            placeholder="Contoh: D 4512 ABC"
            class="civic-input mt-1.5 uppercase"
        >
        @error('plat_nomor')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label for="merek" class="block text-sm font-semibold text-[#0F172A]">Merek *</label>
        <input
            id="merek"
            name="merek"
            type="text"
            maxlength="80"
            required
            value="{{ old('merek', $vehicle?->merek) }}"
            placeholder="Contoh: Honda, Toyota, Yamaha"
            class="civic-input mt-1.5"
        >
        @error('merek')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="model_kendaraan" class="block text-sm font-semibold text-[#0F172A]">Model / Tipe Kendaraan *</label>
        <input
            id="model_kendaraan"
            name="model_kendaraan"
            type="text"
            maxlength="100"
            required
            value="{{ old('model_kendaraan', $vehicle?->model_kendaraan) }}"
            placeholder="Contoh: Vario 160 ABS"
            class="civic-input mt-1.5"
        >
        @error('model_kendaraan')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label for="tahun_pembuatan" class="block text-sm font-semibold text-[#0F172A]">Tahun Pembuatan *</label>
        <input
            id="tahun_pembuatan"
            name="tahun_pembuatan"
            type="number"
            min="1970"
            max="{{ date('Y') + 1 }}"
            required
            value="{{ old('tahun_pembuatan', $vehicle?->tahun_pembuatan ?? (date('Y') - 1)) }}"
            class="civic-input mt-1.5 tabular-nums"
        >
        @error('tahun_pembuatan')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kapasitas_cc" class="block text-sm font-semibold text-[#0F172A]">Kapasitas Mesin (CC) *</label>
        <input
            id="kapasitas_cc"
            name="kapasitas_cc"
            type="number"
            min="50"
            max="10000"
            required
            value="{{ old('kapasitas_cc', $vehicle?->kapasitas_cc ?? 150) }}"
            class="civic-input mt-1.5 tabular-nums"
        >
        @error('kapasitas_cc')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4 space-y-3">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="njkb" class="block text-sm font-semibold text-[#0F172A]">NJKB dalam Rupiah (Opsional)</label>
            <input
                id="njkb"
                name="njkb"
                type="number"
                min="1000000"
                step="100000"
                value="{{ old('njkb', $vehicle ? (int) $vehicle->njkb : '') }}"
                placeholder="Kosongkan untuk estimasi otomatis atau klik tombol di kanan"
                class="civic-input mt-1.5 tabular-nums"
            >
        </div>
        <button
            type="button"
            data-njkb-lookup
            data-endpoint="{{ route('api.referensi.njkb') }}"
            class="civic-btn-navy shrink-0 text-sm"
        >
            Estimasi NJKB via API
        </button>
    </div>
    <p class="text-xs text-[#475569]">
        Jika dikosongkan, nilai NJKB otomatis diambil dari layanan referensi saat pendaftaran disimpan. Urutan kepemilikan kendaraan dihitung otomatis oleh sistem.
    </p>
    <div id="njkb-lookup-status" role="status" aria-live="polite" class="hidden rounded border px-3 py-2 text-xs"></div>
    @error('njkb')
        <p class="text-xs font-medium text-[#991B1B]">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label for="provinsi" class="block text-sm font-semibold text-[#0F172A]">Provinsi Terdaftar *</label>
        <select id="provinsi" name="provinsi" required class="civic-input mt-1.5">
            <option value="">Pilih provinsi</option>
            @foreach ($provinces ?? [] as $prov)
                @php
                    $provName = is_array($prov) ? ($prov['name'] ?? '') : (string) $prov;
                    $selectedProv = old('provinsi', $defaultProv);
                @endphp
                <option value="{{ $provName }}" @selected(strtoupper((string) $selectedProv) === strtoupper($provName))>
                    {{ $provName }}
                </option>
            @endforeach
        </select>
        @error('provinsi')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="jatuh_tempo_stnk" class="block text-sm font-semibold text-[#0F172A]">Tanggal Jatuh Tempo STNK *</label>
        <input
            id="jatuh_tempo_stnk"
            name="jatuh_tempo_stnk"
            type="date"
            required
            value="{{ old('jatuh_tempo_stnk', $vehicle?->jatuh_tempo_stnk?->format('Y-m-d')) }}"
            class="civic-input mt-1.5"
        >
        @error('jatuh_tempo_stnk')
            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
        @enderror
    </div>
</div>

<fieldset class="space-y-4 border-t border-[#E2E8F0] pt-5">
    <legend class="text-sm font-bold text-[#0F2942]">
        {{ $isEdit ? 'Perbarui Berkas (Opsional jika berkas lama masih sesuai, maks. 4 MB)' : 'Unggah Berkas Verifikasi (JPG, PNG, atau PDF, maks. 4 MB)' }}
    </legend>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4">
            <label for="file_ktp" class="block text-sm font-semibold text-[#0F172A]">
                {{ $isEdit ? 'Unggah Ulang KTP' : 'Foto / Scan KTP Pemilik *' }}
            </label>
            <input
                id="file_ktp"
                name="file_ktp"
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                @if (!$isEdit) required @endif
                data-file-preview="ktp-{{ $previewPrefix }}-preview"
                class="mt-2 block w-full text-xs text-[#0F172A] file:mr-3 file:min-h-[44px] file:rounded file:border file:border-[#64748B] file:bg-white file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#0F2942]"
            >
            <p id="ktp-{{ $previewPrefix }}-preview" class="mt-1.5 text-xs text-[#475569]">
                {{ $isEdit ? 'Kosongkan untuk mempertahankan berkas KTP sebelumnya.' : 'Belum ada berkas dipilih.' }}
            </p>
            @error('file_ktp')
                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4">
            <label for="file_stnk" class="block text-sm font-semibold text-[#0F172A]">
                {{ $isEdit ? 'Unggah Ulang STNK' : 'Foto / Scan STNK Kendaraan *' }}
            </label>
            <input
                id="file_stnk"
                name="file_stnk"
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                @if (!$isEdit) required @endif
                data-file-preview="stnk-{{ $previewPrefix }}-preview"
                class="mt-2 block w-full text-xs text-[#0F172A] file:mr-3 file:min-h-[44px] file:rounded file:border file:border-[#64748B] file:bg-white file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#0F2942]"
            >
            <p id="stnk-{{ $previewPrefix }}-preview" class="mt-1.5 text-xs text-[#475569]">
                {{ $isEdit ? 'Kosongkan untuk mempertahankan berkas STNK sebelumnya.' : 'Belum ada berkas dipilih.' }}
            </p>
            @error('file_stnk')
                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
            @enderror
        </div>
    </div>
</fieldset>
