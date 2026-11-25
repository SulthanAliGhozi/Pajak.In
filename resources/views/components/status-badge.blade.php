@props([
    'status' => 'pending',
    'label' => null,
])

@php
    $normalized = strtolower(trim((string) $status));

    $styles = match ($normalized) {
        'approved' => 'bg-[#D1FAE5] text-[#065F46] border-[#10B981]/50',
        'lunas' => 'bg-[#D1FAE5] text-[#065F46] border-[#10B981]/50',
        'rejected' => 'bg-[#FEE2E2] text-[#991B1B] border-[#EF4444]/50',
        'belum_lunas' => 'bg-[#FEE2E2] text-[#991B1B] border-[#EF4444]/50',
        'pending' => 'bg-[#FEF3C7] text-[#92400E] border-[#F59E0B]/50',
        'api' => 'bg-[#E0F2FE] text-[#0F2942] border-[#0F2942]/25',
        'fallback', 'local' => 'bg-[#FEF3C7] text-[#92400E] border-[#F59E0B]/50',
        'pribadi', 'usaha' => 'bg-[#F4F6F9] text-[#0F2942] border-[#CBD5E1]',
        default => 'bg-[#F4F6F9] text-[#0F172A] border-[#CBD5E1]',
    };

    $defaultLabel = match ($normalized) {
        'approved' => 'Disetujui',
        'lunas' => 'Lunas',
        'rejected' => 'Ditolak',
        'belum_lunas' => 'Belum Lunas',
        'pending' => 'Menunggu Verifikasi',
        'api' => 'API Referensi',
        'fallback', 'local' => 'Tarif Lokal (Fallback)',
        'pribadi' => 'Wajib Pajak Pribadi',
        'usaha' => 'Badan Usaha',
        default => ucfirst(str_replace('_', ' ', $normalized)),
    };

    $text = $label ?? $defaultLabel;
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold border whitespace-nowrap {$styles}"]) }}>
    {{ $text }}
</span>
