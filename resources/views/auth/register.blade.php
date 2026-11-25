@extends('layouts.public')

@section('title', 'Registrasi Wajib Pajak')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:py-12">
        <x-alert-banner />

        <div class="civic-card overflow-hidden">
            <div class="border-b border-[#E2E8F0] bg-white px-6 py-5">
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Registrasi Akun Wajib Pajak</h1>
                <p class="mt-1 text-sm text-[#475569]">
                    Lengkapi data akun dan identitas NIK atau NPWP untuk mulai mendaftarkan kendaraan bermotor di Pajak.In.
                </p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="p-6 space-y-6" data-loading-submit>
                @csrf

                <fieldset class="space-y-4">
                    <legend class="text-sm font-bold text-[#0F2942] border-b border-[#E2E8F0] pb-2 w-full">
                        1. Kredensial Masuk
                    </legend>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-[#0F172A]">Nama Lengkap / Badan Usaha *</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                required
                                value="{{ old('name') }}"
                                placeholder="Nama sesuai KTP atau Akta Usaha"
                                class="civic-input mt-1.5"
                            >
                            @error('name')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-[#0F172A]">Alamat Email *</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                value="{{ old('email') }}"
                                placeholder="email@contoh.id"
                                class="civic-input mt-1.5"
                            >
                            @error('email')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-[#0F172A]">Kata Sandi *</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                required
                                placeholder="Minimal 8 karakter"
                                class="civic-input mt-1.5"
                            >
                            @error('password')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-[#0F172A]">Konfirmasi Kata Sandi *</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                                placeholder="Ulangi kata sandi"
                                class="civic-input mt-1.5"
                            >
                        </div>
                    </div>
                </fieldset>

                <fieldset class="space-y-4">
                    <legend class="text-sm font-bold text-[#0F2942] border-b border-[#E2E8F0] pb-2 w-full">
                        2. Profil Identitas Wajib Pajak
                    </legend>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="jenis_entitas" class="block text-sm font-semibold text-[#0F172A]">Jenis Entitas *</label>
                            <select id="jenis_entitas" name="jenis_entitas" required class="civic-input mt-1.5">
                                <option value="pribadi" @selected(old('jenis_entitas', 'pribadi') === 'pribadi')>Pribadi (Perorangan)</option>
                                <option value="usaha" @selected(old('jenis_entitas') === 'usaha')>Badan Usaha / Perusahaan</option>
                            </select>
                            @error('jenis_entitas')
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
                                value="{{ old('nik_npwp') }}"
                                placeholder="16 digit NIK atau 15 digit NPWP"
                                class="civic-input mt-1.5 tabular-nums"
                            >
                            @error('nik_npwp')
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
                                    @endphp
                                    <option value="{{ $provName }}" @selected(strtoupper((string) old('provinsi')) === strtoupper($provName))>
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
                                value="{{ old('kota') }}"
                                placeholder="Contoh: KOTA BANDUNG"
                                class="civic-input mt-1.5"
                            >
                            @error('kota')
                                <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="no_telepon" class="block text-sm font-semibold text-[#0F172A]">Nomor Telepon Aktif *</label>
                        <input
                            id="no_telepon"
                            name="no_telepon"
                            type="tel"
                            maxlength="25"
                            required
                            value="{{ old('no_telepon') }}"
                            placeholder="Contoh: 081234567890"
                            class="civic-input mt-1.5 tabular-nums"
                        >
                        @error('no_telepon')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-semibold text-[#0F172A]">Alamat Domisili / Kantor *</label>
                        <textarea
                            id="alamat"
                            name="alamat"
                            rows="3"
                            required
                            placeholder="Jalan, nomor, kecamatan, dan kode pos"
                            class="civic-input mt-1.5"
                        >{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>
                </fieldset>

                <div class="flex flex-col-reverse gap-3 border-t border-[#E2E8F0] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('login') }}" class="text-center text-sm font-semibold text-[#0F2942] underline underline-offset-4 hover:text-[#047857]">
                        Sudah punya akun? Masuk
                    </a>
                    <button type="submit" data-loading-text="Mendaftarkan akun..." class="civic-btn-primary">
                        Daftar Akun Wajib Pajak
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
