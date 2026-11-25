<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tagihan_id',
    'kode_transaksi',
    'no_etbpkp',
    'metode_pembayaran',
    'nominal_dibayar',
    'tanggal_bayar',
])]
class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_dibayar' => 'float',
            'tanggal_bayar' => 'datetime',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanPajak::class, 'tagihan_id');
    }
}
