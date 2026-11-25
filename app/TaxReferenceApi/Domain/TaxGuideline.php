<?php

declare(strict_types=1);

namespace App\TaxReferenceApi\Domain;

use JsonSerializable;

final readonly class TaxGuideline implements JsonSerializable
{
    public function __construct(
        public string $province,
        public float $pkbBaseRate,
        public float $progressiveIncrement,
        public float $enterpriseFlatRate,
        public float $latePenaltyRatePerMonth,
        public int $maxPenaltyMonths,
        public int $swdklljMotor,
        public int $swdklljMobil,
        public float $opsenRate = 0.66,
        public ?float $pkbOver200CcRate = null,
        public string $regulationNote = '',
    ) {
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function toArray(): array
    {
        return [
            'province' => $this->province,
            'pkb_base_rate' => $this->pkbBaseRate,
            'progressive_increment' => $this->progressiveIncrement,
            'enterprise_flat_rate' => $this->enterpriseFlatRate,
            'opsen_rate' => $this->opsenRate,
            'pkb_over_200cc_rate' => $this->pkbOver200CcRate,
            'regulation_note' => $this->regulationNote,
            'late_penalty_rate_per_month' => $this->latePenaltyRatePerMonth,
            'max_penalty_months' => $this->maxPenaltyMonths,
            'swdkllj_motor' => $this->swdklljMotor,
            'swdkllj_mobil' => $this->swdklljMobil,
        ];
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
