<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'kendaraan_id',
    'tahun_pajak',
    'jatuh_tempo',
    'pkb_pokok',
    'pajak_progresif',
    'opsen_pkb',
    'swdkllj',
    'denda_keterlambatan',
    'total_tagihan',
    'tarif_sumber',
    'regulation_note',
    'status',
    'midtrans_order_id',
    'midtrans_snap_token',
    'midtrans_redirect_url',
    'midtrans_status',
])]
class TagihanPajak extends Model
{
    protected $table = 'tagihan_pajak';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_pajak' => 'integer',
            'jatuh_tempo' => 'date',
            'pkb_pokok' => 'float',
            'pajak_progresif' => 'float',
            'opsen_pkb' => 'float',
            'swdkllj' => 'float',
            'denda_keterlambatan' => 'float',
            'total_tagihan' => 'float',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }

    public function isLunas(): bool
    {
        return $this->status === 'lunas';
    }

    public function tandaiLunas(): self
    {
        $this->status = 'lunas';
        $this->save();

        return $this;
    }
}
