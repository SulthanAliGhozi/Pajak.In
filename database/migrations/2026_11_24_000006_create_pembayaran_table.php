<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tagihan_id')->unique()->constrained('tagihan_pajak')->cascadeOnDelete();
            $table->string('kode_transaksi', 64)->unique();
            $table->string('no_etbpkp', 64)->unique();
            $table->string('metode_pembayaran', 64);
            $table->decimal('nominal_dibayar', 15, 2);
            $table->dateTime('tanggal_bayar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
