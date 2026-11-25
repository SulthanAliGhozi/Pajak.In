<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kendaraan;
use App\Models\User;

class KendaraanPolicy
{
    public function view(User $user, Kendaraan $kendaraan): bool
    {
        return $user->isPetugas() || (int) $kendaraan->user_id === (int) $user->id;
    }

    public function update(User $user, Kendaraan $kendaraan): bool
    {
        return (int) $kendaraan->user_id === (int) $user->id
            && in_array($kendaraan->status_verifikasi, ['pending', 'rejected'], true);
    }

    public function delete(User $user, Kendaraan $kendaraan): bool
    {
        if ((int) $kendaraan->user_id !== (int) $user->id) {
            return false;
        }

        return !$kendaraan->tagihan()->where('status', 'lunas')->exists();
    }
}
