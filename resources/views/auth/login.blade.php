@extends('layouts.public')

@section('title', 'Masuk Portal Pajak.In')

@section('content')
    @php
        $initialPortal = old('portal', request('portal', 'wajib_pajak'));
        if (!in_array($initialPortal, ['wajib_pajak', 'petugas'], true)) {
            $initialPortal = 'wajib_pajak';
        }
    @endphp

    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6 lg:py-14">
        <x-alert-banner />

        <div class="civic-card overflow-hidden">
            <div class="border-b border-[#E2E8F0] bg-white px-6 py-5">
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Masuk ke Portal Pajak.In</h1>
                <p class="mt-1 text-sm text-[#475569]">Pilih jenis portal akun Anda sebelum memasukkan email dan kata sandi.</p>
            </div>

            <div class="p-6">
                <div role="tablist" aria-label="Pilih portal masuk" data-portal-tablist class="grid grid-cols-2 rounded-md border border-[#64748B] overflow-hidden">
                    <button
                        type="button"
                        role="tab"
                        id="tab-wajib-pajak"
                        data-portal-tab="wajib_pajak"
                        aria-selected="{{ $initialPortal === 'wajib_pajak' ? 'true' : 'false' }}"
                        class="min-h-[44px] px-4 py-2.5 text-sm font-semibold transition-colors border-r border-[#64748B]"
                    >
                        Wajib Pajak
                    </button>
                    <button
                        type="button"
                        role="tab"
                        id="tab-petugas"
                        data-portal-tab="petugas"
                        aria-selected="{{ $initialPortal === 'petugas' ? 'true' : 'false' }}"
                        class="min-h-[44px] px-4 py-2.5 text-sm font-semibold transition-colors"
                    >
                        Petugas
                    </button>
                </div>

                <p id="portal-helper-text" class="mt-3 text-xs leading-relaxed text-[#475569]">
                    Masuk sebagai Wajib Pajak Pribadi atau Badan Usaha untuk mengelola kendaraan, unggah berkas, dan bayar tagihan PKB.
                </p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-5 space-y-4" data-loading-submit>
                    @csrf
                    <input type="hidden" name="portal" id="portal-input" value="{{ $initialPortal }}">

                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#0F172A]">Alamat Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            value="{{ old('email') }}"
                            placeholder="nama@pajakin.local"
                            class="civic-input mt-1.5"
                        >
                        @error('email')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#0F172A]">Kata Sandi</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="Masukkan kata sandi"
                            class="civic-input mt-1.5"
                        >
                        @error('password')
                            <p class="mt-1 text-xs font-medium text-[#991B1B]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label for="remember" class="inline-flex min-h-[44px] items-center gap-2.5 text-sm text-[#0F172A]">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                @checked(old('remember'))
                                class="h-4 w-4 rounded border-[#64748B] text-[#047857] focus:ring-[#047857]"
                            >
                            <span>Ingat sesi masuk saya</span>
                        </label>
                    </div>

                    <button type="submit" data-loading-text="Memverifikasi akun..." class="civic-btn-primary w-full">
                        Masuk ke Portal
                    </button>
                </form>

                <div class="mt-6 rounded-md border border-[#E2E8F0] bg-[#F4F6F9] p-4">
                    <p class="text-xs font-semibold text-[#0F2942]">Akun Uji Coba Terdaftar (Database Seeder)</p>
                    <p class="mt-1 text-xs text-[#475569]">
                        Gunakan tombol di bawah untuk mengisi email dan kata sandi bawaan (<code class="font-mono text-[#0F172A]">password123</code>):
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            data-demo-fill
                            data-demo-portal="wajib_pajak"
                            data-demo-email="budi@pajakin.local"
                            data-demo-password="password123"
                            class="inline-flex min-h-[44px] items-center rounded border border-[#64748B] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F2942] hover:bg-[#E2E8F0]"
                        >
                            Isi WP Pribadi (budi@pajakin.local)
                        </button>
                        <button
                            type="button"
                            data-demo-fill
                            data-demo-portal="wajib_pajak"
                            data-demo-email="ptmaju@pajakin.local"
                            data-demo-password="password123"
                            class="inline-flex min-h-[44px] items-center rounded border border-[#64748B] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F2942] hover:bg-[#E2E8F0]"
                        >
                            Isi WP Usaha (ptmaju@pajakin.local)
                        </button>
                        <button
                            type="button"
                            data-demo-fill
                            data-demo-portal="petugas"
                            data-demo-email="petugas@pajakin.local"
                            data-demo-password="password123"
                            class="inline-flex min-h-[44px] items-center rounded border border-[#64748B] bg-white px-3 py-1.5 text-xs font-semibold text-[#0F2942] hover:bg-[#E2E8F0]"
                        >
                            Isi Petugas (petugas@pajakin.local)
                        </button>
                    </div>
                </div>

                <div class="mt-6 border-t border-[#E2E8F0] pt-4 text-center text-sm text-[#475569]">
                    Belum memiliki akun wajib pajak?
                    <a href="{{ route('register') }}" class="font-semibold text-[#047857] underline underline-offset-4 hover:text-[#065F46]">
                        Daftar di sini
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
