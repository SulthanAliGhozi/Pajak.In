<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wajib_pajak_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nik_npwp', 20)->unique();
            $table->enum('jenis_entitas', ['pribadi', 'usaha'])->default('pribadi');
            $table->string('provinsi', 100);
            $table->string('kota', 100);
            $table->text('alamat');
            $table->string('no_telepon', 25);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wajib_pajak_profiles');
    }
};
