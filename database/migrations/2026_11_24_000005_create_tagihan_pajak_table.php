<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_pajak', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kendaraan_id')->constrained('kendaraan')->cascadeOnDelete();
            $table->integer('tahun_pajak');
            $table->date('jatuh_tempo');
            $table->decimal('pkb_pokok', 15, 2);
            $table->decimal('pajak_progresif', 15, 2)->default(0);
            $table->decimal('opsen_pkb', 15, 2)->default(0);
            $table->decimal('swdkllj', 15, 2);
            $table->decimal('denda_keterlambatan', 15, 2)->default(0);
            $table->decimal('total_tagihan', 15, 2);
            $table->string('tarif_sumber', 32)->default('local');
            $table->string('regulation_note', 160)->nullable();
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas');
            $table->timestamps();

            $table->index(['kendaraan_id', 'tahun_pajak']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_pajak');
    }
};
