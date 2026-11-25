@extends('layouts.app')

@section('title', 'Profil Wajib Pajak')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Profil Wajib Pajak</h1>
                <p class="mt-1 text-sm text-[#475569]">
                    Perbarui identitas kepemilikan, wilayah provinsi, dan alamat korespondensi Anda.
                </p>
            </div>
            @if (!empty($regionSource))
                <x-status-badge :status="$regionSource" :label="$regionSource === 'api' ? 'Sumber Wilayah: API' : 'Sumber Wilayah: Lokal'" />
            @endif
        </div>

        <div class="civic-card p-5 sm:p-6">
            <form method="POST" action="{{ route('wp.profil.update') }}" class="space-y-5" data-loading-submit>
                @csrf
                @method('PUT')

                <div class="rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4">
                    <p class="text-xs font-semibold text-[#475569]">Email Akun Terdaftar</p>
                    <p class="mt-0.5 text-sm font-semibold text-[#0F172A]">{{ $user->email }}</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#0F172A]">Nama Lengkap / Badan Usaha *</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            required
                            value="{{ old('name', $user->name) }}"
                            class="civic-input mt-1.5"
                        >
                        @error('name')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nik_npwp" class="block text-sm font-semibold text-[#0F172A]">NIK / NPWP *</label>
                        <input
                            id="nik_npwp"
                            name="nik_npwp"
                            type="text"
                            maxlength="20"
                            required
                            value="{{ old('nik_npwp', $profile?->nik_npwp) }}"
                            class="civic-input mt-1.5 tabular-nums"
                        >
                        @error('nik_npwp')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="jenis_entitas" class="block text-sm font-semibold text-[#0F172A]">Jenis Entitas *</label>
                        <select id="jenis_entitas" name="jenis_entitas" required class="civic-input mt-1.5">
                            <option value="pribadi" @selected(old('jenis_entitas', $profile?->jenis_entitas) === 'pribadi')>Pribadi (Tarif Progresif)</option>
                            <option value="usaha" @selected(old('jenis_entitas', $profile?->jenis_entitas) === 'usaha')>Badan Usaha (Tarif Rata)</option>
                        </select>
                        @error('jenis_entitas')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_telepon" class="block text-sm font-semibold text-[#0F172A]">Nomor Telepon *</label>
                        <input
                            id="no_telepon"
                            name="no_telepon"
                            type="tel"
                            maxlength="25"
                            required
                            value="{{ old('no_telepon', $profile?->no_telepon) }}"
                            class="civic-input mt-1.5 tabular-nums"
                        >
                        @error('no_telepon')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="provinsi" class="block text-sm font-semibold text-[#0F172A]">Provinsi *</label>
                        <select id="provinsi" name="provinsi" required class="civic-input mt-1.5">
                            <option value="">Pilih provinsi</option>
                            @foreach ($provinces ?? [] as $prov)
                                @php
                                    $provName = is_array($prov) ? ($prov['name'] ?? '') : (string) $prov;
                                    $selectedProv = old('provinsi', $profile?->provinsi);
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
                        <label for="kota" class="block text-sm font-semibold text-[#0F172A]">Kota / Kabupaten *</label>
                        <input
                            id="kota"
                            name="kota"
                            type="text"
                            required
                            value="{{ old('kota', $profile?->kota) }}"
                            class="civic-input mt-1.5"
                        >
                        @error('kota')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="alamat" class="block text-sm font-semibold text-[#0F172A]">Alamat Lengkap *</label>
                    <textarea id="alamat" name="alamat" rows="3" required class="civic-input mt-1.5">{{ old('alamat', $profile?->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-[#E2E8F0] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('wp.dashboard') }}" class="civic-btn-secondary">
                        Kembali ke Ringkasan
                    </a>
                    <button type="submit" data-loading-text="Menyimpan profil..." class="civic-btn-primary">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
