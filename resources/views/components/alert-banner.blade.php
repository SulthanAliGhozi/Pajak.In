@if (session('success'))
    <div role="status" class="mb-6 rounded-md border border-[#10B981]/50 bg-[#D1FAE5] px-4 py-3 text-sm text-[#065F46]">
        <p class="font-semibold">Berhasil</p>
        <p class="mt-0.5">{{ session('success') }}</p>
    </div>
@endif

@if (session('status'))
    <div role="status" class="mb-6 rounded-md border border-[#10B981]/50 bg-[#D1FAE5] px-4 py-3 text-sm text-[#065F46]">
        <p class="font-semibold">Informasi</p>
        <p class="mt-0.5">{{ session('status') }}</p>
    </div>
@endif

@if (session('warning'))
    <div role="alert" class="mb-6 rounded-md border border-[#F59E0B]/50 bg-[#FEF3C7] px-4 py-3 text-sm text-[#92400E]">
        <p class="font-semibold">Perhatian</p>
        <p class="mt-0.5">{{ session('warning') }}</p>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-6 rounded-md border border-[#EF4444]/50 bg-[#FEE2E2] px-4 py-3 text-sm text-[#991B1B]">
        <p class="font-semibold">Terjadi Kesalahan</p>
        <p class="mt-0.5">{{ session('error') }}</p>
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-6 rounded-md border border-[#EF4444]/50 bg-[#FEE2E2] px-4 py-3 text-sm text-[#991B1B]">
        <p class="font-semibold">Periksa kembali data yang Anda masukkan:</p>
        <ul class="mt-1.5 list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
