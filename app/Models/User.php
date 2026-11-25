<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(WajibPajakProfile::class, 'user_id');
    }

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'user_id');
    }

    public function verifiedKendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'verified_by');
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isWajibPajak(): bool
    {
        return $this->role === 'wajib_pajak';
    }
}
