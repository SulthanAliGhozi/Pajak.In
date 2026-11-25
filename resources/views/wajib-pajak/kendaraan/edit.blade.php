@extends('layouts.app')

@section('title', 'Ubah Data Kendaraan ' . $kendaraan->plat_nomor)

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Perbarui Data Kendaraan {{ $kendaraan->plat_nomor }}</h1>
            <p class="mt-1 text-sm text-[#475569]">
                Perbaikan data akan mengembalikan status verifikasi menjadi menunggu pemeriksaan ulang oleh petugas.
            </p>
        </div>

        @if ($kendaraan->status_verifikasi === 'rejected' && $kendaraan->catatan_petugas)
            <div role="alert" class="mb-6 rounded-md border border-[#EF4444]/50 bg-[#FEE2E2] p-4 text-sm text-[#991B1B]">
                <p class="font-bold">Catatan Penolakan Petugas</p>
                <p class="mt-1">{{ $kendaraan->catatan_petugas }}</p>
            </div>
        @endif

        <div class="civic-card p-5 sm:p-6">
            <form method="POST" action="{{ route('wp.kendaraan.update', $kendaraan) }}" enctype="multipart/form-data" class="space-y-6" data-loading-submit>
                @csrf
                @method('PUT')

                @include('wajib-pajak.kendaraan._form', ['kendaraan' => $kendaraan])

                <div class="flex flex-col-reverse gap-3 border-t border-[#E2E8F0] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('wp.kendaraan.show', $kendaraan) }}" class="civic-btn-secondary">
                        Batal
                    </a>
                    <button type="submit" data-loading-text="Menyimpan pembaruan..." class="civic-btn-primary">
                        Simpan Pembaruan Kendaraan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
