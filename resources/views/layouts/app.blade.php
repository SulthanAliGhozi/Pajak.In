<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Pajak.In') | Pajak.In</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $currentUser = auth()->user();
    $isPetugas = $currentUser && $currentUser->isPetugas();

    $navItems = $isPetugas
        ? [
            ['label' => 'Antrean Verifikasi', 'route' => 'petugas.dashboard', 'active' => 'petugas.dashboard'],
            ['label' => 'Verifikasi Kendaraan', 'route' => 'petugas.verifikasi.index', 'active' => 'petugas.verifikasi.*'],
            ['label' => 'Daftar Tagihan', 'route' => 'petugas.tagihan.index', 'active' => 'petugas.tagihan.*'],
            ['label' => 'Kalkulator PKB', 'route' => 'kalkulator.index', 'active' => 'kalkulator.*'],
        ]
        : [
            ['label' => 'Ringkasan', 'route' => 'wp.dashboard', 'active' => 'wp.dashboard'],
            ['label' => 'Kendaraan Saya', 'route' => 'wp.kendaraan.index', 'active' => 'wp.kendaraan.*'],
            ['label' => 'Tagihan Pajak', 'route' => 'wp.tagihan.index', 'active' => 'wp.tagihan.*'],
            ['label' => 'Profil Wajib Pajak', 'route' => 'wp.profil.edit', 'active' => 'wp.profil.*'],
            ['label' => 'Kalkulator PKB', 'route' => 'kalkulator.index', 'active' => 'kalkulator.*'],
        ];

    $homeDashboard = $isPetugas ? route('petugas.dashboard') : route('wp.dashboard');
    $roleLabel = $isPetugas ? 'Petugas Pajak' : 'Wajib Pajak';
@endphp
<body class="flex min-h-screen flex-col bg-[#F4F6F9] text-[#0F172A]">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded focus:bg-[#0F2942] focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>

    <header class="surface-navy sticky top-0 z-30 border-b border-[#1E3A5F] bg-[#0F2942] text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5 sm:px-6 lg:px-8">
            <div class="flex items-center gap-6">
                <a href="{{ $homeDashboard }}" class="inline-flex min-h-[44px] items-center rounded">
                    <x-brand-logo variant="light" />
                </a>

                <nav aria-label="Navigasi portal" class="hidden lg:flex lg:items-center lg:gap-1">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="inline-flex min-h-[44px] items-center rounded px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs($item['active']) ? 'bg-[#047857] text-white' : 'text-[#E2E8F0] hover:bg-[#163A5C] hover:text-white' }}"
                           @if (request()->routeIs($item['active'])) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="hidden lg:flex lg:items-center lg:gap-3">
                <div class="text-right leading-tight">
                    <p class="text-sm font-semibold text-white">{{ $currentUser?->name }}</p>
                    <p class="text-xs text-[#CBD5E1]">{{ $roleLabel }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" data-loading-submit>
                    @csrf
                    <button type="submit" data-loading-text="Keluar..." class="inline-flex min-h-[44px] items-center rounded border border-[#335170] bg-[#163A5C] px-3.5 py-2 text-sm font-medium text-white transition-colors hover:bg-[#1E4A73]">
                        Keluar
                    </button>
                </form>
            </div>

            <button
                type="button"
                data-mobile-menu-toggle
                aria-expanded="false"
                aria-controls="portal-mobile-menu"
                class="inline-flex min-h-[44px] min-w-[44px] items-center gap-2 rounded border border-[#335170] bg-[#163A5C] px-3 py-2 text-sm font-semibold text-white lg:hidden"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                </svg>
                <span>Menu</span>
            </button>
        </div>

        <div id="portal-mobile-menu" data-mobile-menu-panel class="hidden border-t border-[#1E3A5F] bg-[#0F2942] px-4 pb-4 pt-3 lg:hidden">
            <div class="mb-3 rounded border border-[#1E3A5F] bg-[#163A5C]/60 px-3 py-2.5">
                <p class="text-sm font-semibold text-white">{{ $currentUser?->name }}</p>
                <p class="text-xs text-[#CBD5E1]">{{ $roleLabel }} ({{ $currentUser?->email }})</p>
            </div>
            <nav aria-label="Navigasi seluler portal" class="flex flex-col gap-1.5">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex min-h-[44px] items-center rounded px-3 py-2 text-sm font-medium {{ request()->routeIs($item['active']) ? 'bg-[#047857] text-white' : 'text-[#E2E8F0] hover:bg-[#163A5C]' }}"
                       @if (request()->routeIs($item['active'])) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}" class="mt-2 pt-2 border-t border-[#1E3A5F]" data-loading-submit>
                    @csrf
                    <button type="submit" data-loading-text="Keluar..." class="flex min-h-[44px] w-full items-center justify-center rounded border border-[#335170] bg-[#163A5C] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1E4A73]">
                        Keluar dari Portal
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main id="main-content" class="flex-1">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <x-alert-banner />
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-[#E2E8F0] bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-[#475569] sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>Pajak.In (Portal Pajak Kendaraan Bermotor)</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="inline-flex min-h-[44px] items-center font-medium text-[#0F2942] hover:underline">Beranda Publik</a>
                <a href="{{ route('kalkulator.index') }}" class="inline-flex min-h-[44px] items-center font-medium text-[#0F2942] hover:underline">Kalkulator Pajak</a>
            </div>
        </div>
    </footer>
</body>
</html>
