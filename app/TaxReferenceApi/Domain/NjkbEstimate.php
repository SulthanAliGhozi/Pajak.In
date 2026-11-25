<?php

declare(strict_types=1);

namespace App\TaxReferenceApi\Domain;

use JsonSerializable;

final readonly class NjkbEstimate implements JsonSerializable
{
    public function __construct(
        public string $jenis,
        public string $merek,
        public int $tahun,
        public int $cc,
        public int $estimatedNjkb,
        public int $basePrice,
        public float $depreciationFactor,
        public int $ageYears,
        public string $currency = 'IDR',
    ) {
    }

    /**
     * @return array<string, int|float|string>
     */
    public function toArray(): array
    {
        return [
            'jenis' => $this->jenis,
            'merek' => $this->merek,
            'tahun' => $this->tahun,
            'cc' => $this->cc,
            'estimated_njkb' => $this->estimatedNjkb,
            'base_price' => $this->basePrice,
            'depreciation_factor' => $this->depreciationFactor,
            'age_years' => $this->ageYears,
            'currency' => $this->currency,
        ];
    }

    /**
     * @return array<string, int|float|string>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
