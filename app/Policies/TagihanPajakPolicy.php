<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TagihanPajak;
use App\Models\User;

class TagihanPajakPolicy
{
    public function view(User $user, TagihanPajak $tagihan): bool
    {
        $tagihan->loadMissing('kendaraan');

        return $user->isPetugas() || (int) $tagihan->kendaraan?->user_id === (int) $user->id;
    }

    public function pay(User $user, TagihanPajak $tagihan): bool
    {
        $tagihan->loadMissing('kendaraan');

        return $user->isWajibPajak() && (int) $tagihan->kendaraan?->user_id === (int) $user->id;
    }
}
