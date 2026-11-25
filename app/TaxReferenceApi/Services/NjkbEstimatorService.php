<?php

declare(strict_types=1);

namespace App\TaxReferenceApi\Services;

use App\TaxReferenceApi\Contracts\ReferenceProviderInterface;
use App\TaxReferenceApi\Domain\NjkbEstimate;
use App\TaxReferenceApi\Exceptions\ReferenceValidationException;

final class NjkbEstimatorService implements ReferenceProviderInterface
{
    private const int MIN_YEAR = 1970;
    private const int MIN_CC = 50;
    private const int MAX_CC = 10000;
    private const float INITIAL_NJKB_RATIO = 0.90;
    private const float ANNUAL_RETENTION_RATE = 0.92;
    private const float MIN_DEPRECIATION_FLOOR = 0.22;

    /** @var array<string, array<int, int>> */
    private const array CC_BASE_PRICES = [
        'motor' => [
            115 => 15500000,
            135 => 18500000,
            160 => 24500000,
            250 => 45000000,
            500 => 95000000,
            10000 => 190000000,
        ],
        'mobil' => [
            1200 => 145000000,
            1500 => 215000000,
            2000 => 340000000,
            2500 => 480000000,
            3500 => 750000000,
            10000 => 1250000000,
        ],
    ];

    /** @var array<string, array<string, float>> */
    private const array BRAND_MULTIPLIERS = [
        'motor' => [
            'honda' => 1.05,
            'yamaha' => 1.02,
            'suzuki' => 0.95,
            'kawasaki' => 1.18,
            'vespa' => 1.35,
            'ducati' => 1.80,
            'bmw' => 1.75,
        ],
        'mobil' => [
            'toyota' => 1.08,
            'honda' => 1.06,
            'mitsubishi' => 1.04,
            'suzuki' => 0.96,
            'daihatsu' => 0.92,
            'hyundai' => 1.03,
            'wuling' => 0.88,
            'mazda' => 1.15,
            'bmw' => 1.80,
            'mercedes-benz' => 1.85,
            'mercedes' => 1.85,
            'lexus' => 1.90,
        ],
    ];

    public function getProviderName(): string
    {
        return 'njkb_estimator_service';
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function lookup(array $criteria = []): array
    {
        $estimate = $this->estimate(
            (string) ($criteria['jenis'] ?? ''),
            (string) ($criteria['merek'] ?? ''),
            $criteria['tahun'] ?? '',
            $criteria['cc'] ?? '',
        );

        return ['data' => $estimate->toArray()];
    }

    public function estimate(
        string $jenis,
        string $merek,
        int|string $tahun,
        int|string $cc,
        ?int $referenceYear = null,
    ): NjkbEstimate {
        $currentYear = $referenceYear ?? (int) date('Y');
        $validated = $this->validateInputs($jenis, $merek, $tahun, $cc, $currentYear);

        $basePrice = (int) round(
            $this->resolveBasePrice($validated['jenis'], $validated['cc'])
            * $this->resolveBrandMultiplier($validated['jenis'], $validated['merek'])
        );

        $ageYears = max(0, $currentYear - $validated['tahun']);
        $depreciationFactor = $this->calculateDepreciationFactor($ageYears);
        $rawEstimate = $basePrice * $depreciationFactor;
        $estimatedNjkb = (int) (round($rawEstimate / 100000) * 100000);

        return new NjkbEstimate(
            jenis: $validated['jenis'],
            merek: $validated['merek'],
            tahun: $validated['tahun'],
            cc: $validated['cc'],
            estimatedNjkb: $estimatedNjkb,
            basePrice: $basePrice,
            depreciationFactor: $depreciationFactor,
            ageYears: $ageYears,
        );
    }

    /**
     * @return array{jenis: string, merek: string, tahun: int, cc: int}
     */
    private function validateInputs(
        string $jenis,
        string $merek,
        int|string $tahun,
        int|string $cc,
        int $currentYear,
    ): array {
        $errors = [];
        $normalizedJenis = strtolower(trim($jenis));
        if (!in_array($normalizedJenis, ['motor', 'mobil'], true)) {
            $errors['jenis'] = 'Jenis kendaraan harus motor atau mobil.';
        }

        $normalizedMerek = trim($merek);
        if ($normalizedMerek === '' || strlen($normalizedMerek) > 60) {
            $errors['merek'] = 'Merek kendaraan wajib diisi (maksimal 60 karakter).';
        }

        $parsedYear = filter_var($tahun, FILTER_VALIDATE_INT);
        $maxYear = $currentYear + 1;
        if ($parsedYear === false || $parsedYear < self::MIN_YEAR || $parsedYear > $maxYear) {
            $errors['tahun'] = sprintf('Tahun harus antara %d dan %d.', self::MIN_YEAR, $maxYear);
        }

        $parsedCc = filter_var($cc, FILTER_VALIDATE_INT);
        if ($parsedCc === false || $parsedCc < self::MIN_CC || $parsedCc > self::MAX_CC) {
            $errors['cc'] = sprintf('Kapasitas mesin (cc) harus antara %d dan %d.', self::MIN_CC, self::MAX_CC);
        }

        if ($errors !== []) {
            throw new ReferenceValidationException('Parameter estimasi NJKB tidak valid.', $errors);
        }

        return [
            'jenis' => $normalizedJenis,
            'merek' => strtolower($normalizedMerek),
            'tahun' => (int) $parsedYear,
            'cc' => (int) $parsedCc,
        ];
    }

    private function resolveBasePrice(string $jenis, int $cc): int
    {
        foreach (self::CC_BASE_PRICES[$jenis] as $maxCc => $price) {
            if ($cc <= $maxCc) {
                return $price;
            }
        }

        return self::CC_BASE_PRICES[$jenis][10000];
    }

    private function resolveBrandMultiplier(string $jenis, string $merek): float
    {
        return self::BRAND_MULTIPLIERS[$jenis][$merek] ?? 1.0;
    }

    private function calculateDepreciationFactor(int $ageYears): float
    {
        $factor = self::INITIAL_NJKB_RATIO * (self::ANNUAL_RETENTION_RATE ** $ageYears);

        return max(self::MIN_DEPRECIATION_FLOOR, round($factor, 4));
    }
}
