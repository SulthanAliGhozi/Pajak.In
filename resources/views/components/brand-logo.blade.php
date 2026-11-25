@props([
    'variant' => 'dark',
    'showSubtitle' => false,
])

@php
    $textColor = $variant === 'light' ? 'text-white' : 'text-[#0F2942]';
    $subColor = $variant === 'light' ? 'text-[#CBD5E1]' : 'text-[#475569]';
    $shieldFill = $variant === 'light' ? '#FFFFFF' : '#0F2942';
    $accentFill = '#047857';
@endphp

<span class="inline-flex items-center gap-3">
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="shrink-0">
        <rect x="2" y="2" width="28" height="28" rx="6" fill="{{ $shieldFill }}" fill-opacity="0.12" stroke="{{ $shieldFill }}" stroke-width="2" />
        <path d="M9 11.5H23M9 16H17M9 20.5H15" stroke="{{ $shieldFill }}" stroke-width="2" stroke-linecap="round" />
        <rect x="18.5" y="17.5" width="7" height="7" rx="1.5" fill="{{ $accentFill }}" />
        <path d="M20.3 21L21.5 22.2L23.8 19.8" stroke="#FFFFFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span class="flex flex-col leading-tight">
        <span class="text-base font-bold tracking-tight {{ $textColor }}">Pajak.In</span>
        @if ($showSubtitle)
            <span class="text-xs font-medium {{ $subColor }}">Portal Pajak Kendaraan Bermotor</span>
        @endif
    </span>
</span>
