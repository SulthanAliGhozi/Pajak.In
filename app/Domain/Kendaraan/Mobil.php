<?php

declare(strict_types=1);

namespace App\Domain\Kendaraan;

/**
 * Four-wheeled passenger/commercial vehicle domain entity with car SWDKLLJ tariff rules.
 */
final class Mobil extends AbstractKendaraan
{
    public const float DEFAULT_SWDKLLJ = 143000.0;

    public function getJenisKendaraan(): string
    {
        return 'mobil';
    }

    public function hitungPajakPokok(float $tarifDasar): float
    {
        return round($this->njkb * $tarifDasar, 2);
    }

    public function hitungSwdkllj(array $tarifReferensi = []): float
    {
        return (float) ($tarifReferensi['swdkllj_mobil'] ?? self::DEFAULT_SWDKLLJ);
    }
}
