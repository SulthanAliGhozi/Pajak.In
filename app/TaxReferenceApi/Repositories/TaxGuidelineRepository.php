<?php

declare(strict_types=1);

namespace App\TaxReferenceApi\Repositories;

use App\TaxReferenceApi\Contracts\ReferenceProviderInterface;
use App\TaxReferenceApi\Domain\TaxGuideline;
use App\TaxReferenceApi\Exceptions\ReferenceValidationException;

final class TaxGuidelineRepository implements ReferenceProviderInterface
{
    private const int SWDKLLJ_MOTOR = 35000;
    private const int SWDKLLJ_MOBIL = 143000;
    private const float DEFAULT_LATE_PENALTY_RATE = 0.02;
    private const int DEFAULT_MAX_PENALTY_MONTHS = 24;

    /** @var array<string, TaxGuideline> */
    private array $guidelines;

    public function __construct()
    {
        $this->guidelines = $this->seedGuidelines();
    }

    public function getProviderName(): string
    {
        return 'tax_guideline_repository';
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function lookup(array $criteria = []): array
    {
        if (array_key_exists('provinsi', $criteria)) {
            $provinsi = (string) $criteria['provinsi'];
            $result = $this->findByProvince($provinsi);

            return [
                'data' => $result['guideline']->toArray(),
                'matched_province' => $result['matched_province'],
                'is_default_fallback' => $result['is_default_fallback'],
            ];
        }

        $items = array_map(
            static fn (TaxGuideline $guideline): array => $guideline->toArray(),
            $this->findAll()
        );

        return [
            'data' => $items,
            'count' => count($items),
        ];
    }

    /**
     * @return list<TaxGuideline>
     */
    public function findAll(): array
    {
        $result = [];
        foreach ($this->guidelines as $key => $guideline) {
            if ($key !== 'DEFAULT') {
                $result[] = $guideline;
            }
        }

        return $result;
    }

    /**
     * @return array{guideline: TaxGuideline, matched_province: string, is_default_fallback: bool}
     */
    public function findByProvince(string $province): array
    {
        $normalized = $this->normalizeProvince($province);
        if ($normalized === '') {
            throw new ReferenceValidationException('Parameter provinsi tidak boleh kosong.', [
                'provinsi' => 'Parameter provinsi wajib diisi jika disertakan.',
            ]);
        }

        if (isset($this->guidelines[$normalized]) && $normalized !== 'DEFAULT') {
            return [
                'guideline' => $this->guidelines[$normalized],
                'matched_province' => $normalized,
                'is_default_fallback' => false,
            ];
        }

        return [
            'guideline' => $this->guidelines['DEFAULT'],
            'matched_province' => 'DEFAULT',
            'is_default_fallback' => true,
        ];
    }

    public function normalizeProvince(string $province): string
    {
        $collapsed = preg_replace('/\s+/', ' ', trim($province));

        return strtoupper($collapsed ?? '');
    }

    /**
     * @return array<string, TaxGuideline>
     */
    private function seedGuidelines(): array
    {
        $definitions = [
            'DKI JAKARTA' => [0.02, 0.01, 0.02, 0.0, null, 'Perda DKI Jakarta No. 1 Tahun 2024 (Non-Opsen)'],
            'JAWA BARAT' => [0.0112, 0.005, 0.0112, 0.66, null, 'Perda Jabar No. 9 Tahun 2023 + Opsen PKB 66%'],
            'JAWA TENGAH' => [0.0105, 0.005, 0.0105, 0.66, null, 'Perda Jateng No. 12 Tahun 2023 + Opsen PKB 66%'],
            'JAWA TIMUR' => [0.012, 0.005, 0.012, 0.66, null, 'Perda Jatim No. 8 Tahun 2023 + Opsen PKB 66%'],
            'BANTEN' => [0.012, 0.005, 0.012, 0.66, null, 'Perda Banten No. 1 Tahun 2024 + Opsen PKB 66%'],
            'DI YOGYAKARTA' => [0.009, 0.005, 0.009, 0.66, null, 'Perda DIY No. 11 Tahun 2023 + Opsen PKB 66%'],
            'BALI' => [0.0105, 0.0, 0.0105, 0.66, 0.012, 'Perda Bali No. 1 Tahun 2024 (Non-Progresif) + Opsen PKB 66%'],
            'SUMATERA UTARA' => [0.01, 0.005, 0.01, 0.66, null, 'Perda Sumut No. 1 Tahun 2024 + Opsen PKB 66%'],
            'SULAWESI SELATAN' => [0.01, 0.005, 0.01, 0.66, null, 'Perda Sulsel No. 1 Tahun 2024 + Opsen PKB 66%'],
            'DEFAULT' => [0.012, 0.005, 0.012, 0.66, null, 'Standar UU HKPD No. 1/2022 + Opsen PKB 66%'],
        ];

        $seeded = [];
        foreach ($definitions as $province => [$base, $progressive, $enterprise, $opsen, $over200Cc, $note]) {
            $seeded[$province] = new TaxGuideline(
                province: $province,
                pkbBaseRate: $base,
                progressiveIncrement: $progressive,
                enterpriseFlatRate: $enterprise,
                latePenaltyRatePerMonth: self::DEFAULT_LATE_PENALTY_RATE,
                maxPenaltyMonths: self::DEFAULT_MAX_PENALTY_MONTHS,
                swdklljMotor: self::SWDKLLJ_MOTOR,
                swdklljMobil: self::SWDKLLJ_MOBIL,
                opsenRate: $opsen,
                pkbOver200CcRate: $over200Cc,
                regulationNote: $note,
            );
        }

        return $seeded;
    }
}
