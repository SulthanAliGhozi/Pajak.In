@props([
    'title',
    'description',
    'actionHref' => null,
    'actionLabel' => null,
])

<div class="civic-card p-6 sm:p-8 text-center">
    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-md bg-[#F4F6F9] border border-[#E2E8F0] text-[#0F2942]">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M8 9h8M8 13h5" stroke-linecap="round" />
        </svg>
    </div>
    <h3 class="text-base font-semibold text-[#0F172A]">{{ $title }}</h3>
    <p class="mx-auto mt-1.5 max-w-md text-sm text-[#475569] leading-relaxed">{{ $description }}</p>
    @if ($actionHref && $actionLabel)
        <div class="mt-5">
            <a href="{{ $actionHref }}" class="civic-btn-primary">
                {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
