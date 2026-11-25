<?php

declare(strict_types=1);

namespace App\Domain\Kendaraan;

/**
 * Two-wheeled motor vehicle domain entity with motorcycle SWDKLLJ tariff rules.
 */
final class Motor extends AbstractKendaraan
{
    public const float DEFAULT_SWDKLLJ = 35000.0;

    public function getJenisKendaraan(): string
    {
        return 'motor';
    }

    public function hitungPajakPokok(float $tarifDasar): float
    {
        return round($this->njkb * $tarifDasar, 2);
    }

    public function hitungSwdkllj(array $tarifReferensi = []): float
    {
        return (float) ($tarifReferensi['swdkllj_motor'] ?? self::DEFAULT_SWDKLLJ);
    }
}
