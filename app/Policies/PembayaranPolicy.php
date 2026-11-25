<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;

class PembayaranPolicy
{
    public function view(User $user, Pembayaran $pembayaran): bool
    {
        $pembayaran->loadMissing('tagihan.kendaraan');

        return $user->isPetugas() || (int) $pembayaran->tagihan?->kendaraan?->user_id === (int) $user->id;
    }
}
