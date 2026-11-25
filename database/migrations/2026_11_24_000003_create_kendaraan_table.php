<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendaraan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('jenis_kendaraan', ['motor', 'mobil']);
            $table->string('plat_nomor', 20)->unique();
            $table->string('merek', 80);
            $table->string('model_kendaraan', 100);
            $table->integer('tahun_pembuatan');
            $table->integer('kapasitas_cc');
            $table->decimal('njkb', 15, 2);
            $table->string('provinsi', 100);
            $table->date('jatuh_tempo_stnk');
            $table->integer('urutan_kepemilikan')->default(1);
            $table->enum('status_verifikasi', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_petugas')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status_verifikasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
