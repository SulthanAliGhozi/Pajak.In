@extends('layouts.app')

@section('title', 'Daftarkan Kendaraan Baru')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">Pendaftaran Kendaraan Bermotor</h1>
            <p class="mt-1 text-sm text-[#475569]">
                Isi spesifikasi kendaraan dan unggah berkas KTP serta STNK untuk diverifikasi oleh petugas pajak.
            </p>
        </div>

        <div class="civic-card p-5 sm:p-6">
            <form method="POST" action="{{ route('wp.kendaraan.store') }}" enctype="multipart/form-data" class="space-y-6" data-loading-submit>
                @csrf

                @include('wajib-pajak.kendaraan._form', ['kendaraan' => null])

                <div class="flex flex-col-reverse gap-3 border-t border-[#E2E8F0] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('wp.kendaraan.index') }}" class="civic-btn-secondary">
                        Batal
                    </a>
                    <button type="submit" data-loading-text="Mengirim pengajuan..." class="civic-btn-primary">
                        Ajukan Verifikasi Kendaraan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
