<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_kendaraan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kendaraan_id')->constrained('kendaraan')->cascadeOnDelete();
            $table->enum('jenis_berkas', ['ktp', 'stnk']);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index(['kendaraan_id', 'jenis_berkas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_kendaraan');
    }
};
