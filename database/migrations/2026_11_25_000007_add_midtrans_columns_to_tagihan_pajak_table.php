<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan_pajak', function (Blueprint $table): void {
            $table->string('midtrans_order_id', 64)->nullable()->unique()->after('status');
            $table->string('midtrans_snap_token', 128)->nullable()->after('midtrans_order_id');
            $table->string('midtrans_redirect_url', 255)->nullable()->after('midtrans_snap_token');
            $table->string('midtrans_status', 32)->nullable()->after('midtrans_redirect_url');
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_pajak', function (Blueprint $table): void {
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_snap_token',
                'midtrans_redirect_url',
                'midtrans_status',
            ]);
        });
    }
};
