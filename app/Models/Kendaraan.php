<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Kendaraan\AbstractKendaraan;
use App\Domain\Kendaraan\KendaraanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'verified_by',
    'jenis_kendaraan',
    'plat_nomor',
    'merek',
    'model_kendaraan',
    'tahun_pembuatan',
    'kapasitas_cc',
    'njkb',
    'provinsi',
    'jatuh_tempo_stnk',
    'urutan_kepemilikan',
    'status_verifikasi',
    'catatan_petugas',
    'verified_at',
])]
class Kendaraan extends Model
{
    protected $table = 'kendaraan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_pembuatan' => 'integer',
            'kapasitas_cc' => 'integer',
            'njkb' => 'float',
            'jatuh_tempo_stnk' => 'date',
            'urutan_kepemilikan' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(BerkasKendaraan::class, 'kendaraan_id');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanPajak::class, 'kendaraan_id');
    }

    public function isApproved(): bool
    {
        return $this->status_verifikasi === 'approved';
    }

    public function toDomainEntity(): AbstractKendaraan
    {
        return KendaraanFactory::fromModel($this);
    }
}
