<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'nik_npwp',
    'jenis_entitas',
    'provinsi',
    'kota',
    'alamat',
    'no_telepon',
])]
class WajibPajakProfile extends Model
{
    protected $table = 'wajib_pajak_profiles';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
