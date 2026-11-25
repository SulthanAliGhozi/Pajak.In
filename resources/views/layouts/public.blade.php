<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Layanan Pajak Kendaraan Bermotor') | Pajak.In</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#F4F6F9] text-[#0F172A]">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded focus:bg-[#0F2942] focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>

    <header class="surface-navy sticky top-0 z-30 border-b border-[#1E3A5F] bg-[#0F2942] text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center rounded focus-visible:outline-white">
                <x-brand-logo variant="light" :show-subtitle="true" />
            </a>

            <nav aria-label="Navigasi publik" class="hidden md:flex md:items-center md:gap-2">
                <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center rounded px-3.5 py-2 text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'bg-[#163A5C] text-white' : 'text-[#E2E8F0] hover:bg-[#163A5C] hover:text-white' }}">
                    Beranda
                </a>
                <a href="{{ route('kalkulator.index') }}" class="inline-flex min-h-[44px] items-center rounded px-3.5 py-2 text-sm font-medium transition-colors {{ request()->routeIs('kalkulator.*') ? 'bg-[#163A5C] text-white' : 'text-[#E2E8F0] hover:bg-[#163A5C] hover:text-white' }}">
                    Kalkulator PKB
                </a>
                @auth
                    @php
                        $dashboardRoute = auth()->user()->isPetugas() ? route('petugas.dashboard') : route('wp.dashboard');
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="ml-2 inline-flex min-h-[44px] items-center rounded bg-[#047857] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#065F46]">
                        Buka Portal Saya
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center rounded px-3.5 py-2 text-sm font-medium text-[#E2E8F0] transition-colors hover:bg-[#163A5C] hover:text-white">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="ml-1 inline-flex min-h-[44px] items-center rounded bg-[#047857] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#065F46]">
                        Daftar Wajib Pajak
                    </a>
                @endauth
            </nav>

            <button
                type="button"
                data-mobile-menu-toggle
                aria-expanded="false"
                aria-controls="public-mobile-menu"
                class="inline-flex min-h-[44px] min-w-[44px] items-center gap-2 rounded border border-[#335170] bg-[#163A5C] px-3 py-2 text-sm font-semibold text-white md:hidden"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                </svg>
                <span>Menu</span>
            </button>
        </div>

        <div id="public-mobile-menu" data-mobile-menu-panel class="hidden border-t border-[#1E3A5F] bg-[#0F2942] px-4 pb-4 pt-2 md:hidden">
            <nav aria-label="Navigasi seluler publik" class="flex flex-col gap-1.5">
                <a href="{{ route('home') }}" class="flex min-h-[44px] items-center rounded px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'bg-[#163A5C] text-white' : 'text-[#E2E8F0] hover:bg-[#163A5C]' }}">
                    Beranda
                </a>
                <a href="{{ route('kalkulator.index') }}" class="flex min-h-[44px] items-center rounded px-3 py-2 text-sm font-medium {{ request()->routeIs('kalkulator.*') ? 'bg-[#163A5C] text-white' : 'text-[#E2E8F0] hover:bg-[#163A5C]' }}">
                    Kalkulator PKB
                </a>
                @auth
                    @php
                        $dashboardRoute = auth()->user()->isPetugas() ? route('petugas.dashboard') : route('wp.dashboard');
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="mt-1 flex min-h-[44px] items-center justify-center rounded bg-[#047857] px-4 py-2 text-sm font-semibold text-white hover:bg-[#065F46]">
                        Buka Portal Saya
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex min-h-[44px] items-center rounded px-3 py-2 text-sm font-medium text-[#E2E8F0] hover:bg-[#163A5C]">
                        Masuk Portal
                    </a>
                    <a href="{{ route('register') }}" class="mt-1 flex min-h-[44px] items-center justify-center rounded bg-[#047857] px-4 py-2 text-sm font-semibold text-white hover:bg-[#065F46]">
                        Daftar Wajib Pajak
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-[#E2E8F0] bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div>
                <x-brand-logo variant="dark" />
                <p class="mt-1.5 text-sm text-[#475569]">
                    Sistem Informasi Pajak Kendaraan Bermotor untuk pendaftaran aset, verifikasi berkas, dan penerbitan e-TBPKP.
                </p>
            </div>
            <nav aria-label="Tautan kaki" class="flex flex-wrap items-center gap-4 text-sm font-medium text-[#0F2942]">
                <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center hover:underline">Beranda</a>
                <a href="{{ route('kalkulator.index') }}" class="inline-flex min-h-[44px] items-center hover:underline">Kalkulator PKB</a>
                <a href="{{ route('login') }}" class="inline-flex min-h-[44px] items-center hover:underline">Masuk</a>
                <a href="{{ route('register') }}" class="inline-flex min-h-[44px] items-center hover:underline">Registrasi</a>
            </nav>
        </div>
    </footer>
</body>
</html>
