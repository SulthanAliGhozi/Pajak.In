<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Contracts\TaxRuleInterface;
use App\Domain\Kendaraan\AbstractKendaraan;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Shared tax-rule behavior for regional base rates, opsen PKB, due-date grace, and late penalties.
 */
abstract class AbstractTaxRule implements TaxRuleInterface
{
    protected const float DEFAULT_PENALTY_RATE_PER_MONTH = 0.02;
    protected const int DEFAULT_MAX_PENALTY_MONTHS = 24;
    protected const float DEFAULT_OPSEN_RATE = 0.66;

    /**
     * Resolves regional base PKB rate, supporting provinces with >200cc tiers (e.g. Bali).
     *
     * @param array<string, mixed> $tarifReferensi
     */
    protected function resolveRegionalBaseRate(
        AbstractKendaraan $kendaraan,
        array $tarifReferensi,
        float $defaultRate,
    ): float {
        $baseRate = (float) ($tarifReferensi['pkb_base_rate'] ?? $defaultRate);
        $over200CcRate = $tarifReferensi['pkb_over_200cc_rate'] ?? null;

        if ($over200CcRate !== null && $kendaraan->getKapasitasCc() > 200) {
            return (float) $over200CcRate;
        }

        return $baseRate;
    }

    /**
     * @param array<string, mixed> $tarifReferensi
     */
    protected function resolveOpsenRate(array $tarifReferensi): float
    {
        return array_key_exists('opsen_rate', $tarifReferensi)
            ? max(0.0, (float) $tarifReferensi['opsen_rate'])
            : self::DEFAULT_OPSEN_RATE;
    }

    protected function hitungOpsenPkb(float $pkbTerutang, float $opsenRate): float
    {
        return $opsenRate > 0 ? round($pkbTerutang * $opsenRate, 2) : 0.0;
    }

    /**
     * Advances weekend or national-holiday due dates to the next Indonesian business day.
     *
     * @param list<string|array{tanggal: string}> $hariLiburNasional
     */
    protected function resolveEffectiveDueDate(
        DateTimeImmutable $jatuhTempo,
        array $hariLiburNasional = [],
    ): DateTimeImmutable {
        $holidaySet = $this->indexHolidayDates($hariLiburNasional);
        $current = $jatuhTempo->setTime(0, 0);

        while (true) {
            $weekday = (int) $current->format('N');
            $isoDate = $current->format('Y-m-d');
            if ($weekday < 6 && !isset($holidaySet[$isoDate])) {
                return $current;
            }
            $current = $current->modify('+1 day');
        }
    }

    /**
     * Counts overdue months (partial month counts as 1), capped by max_penalty_months.
     *
     * @param array<string, mixed> $tarifReferensi
     */
    protected function hitungBulanTerlambat(
        DateTimeImmutable $effectiveDueDate,
        ?DateTimeInterface $tanggalHitung,
        array $tarifReferensi,
    ): int {
        $calcDate = $tanggalHitung !== null
            ? DateTimeImmutable::createFromInterface($tanggalHitung)->setTime(0, 0)
            : new DateTimeImmutable('today');

        if ($calcDate <= $effectiveDueDate) {
            return 0;
        }

        $diff = $effectiveDueDate->diff($calcDate);
        $months = ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0);
        $maxMonths = (int) ($tarifReferensi['max_penalty_months'] ?? self::DEFAULT_MAX_PENALTY_MONTHS);

        return min(max(1, $maxMonths), max(0, $months));
    }

    /**
     * @param array<string, mixed> $tarifReferensi
     */
    protected function hitungDenda(
        float $dasarDenda,
        int $bulanTerlambat,
        array $tarifReferensi,
    ): float {
        if ($bulanTerlambat <= 0 || $dasarDenda <= 0) {
            return 0.0;
        }

        $ratePerMonth = (float) ($tarifReferensi['late_penalty_rate_per_month'] ?? self::DEFAULT_PENALTY_RATE_PER_MONTH);

        return round($dasarDenda * $ratePerMonth * $bulanTerlambat, 2);
    }

    /**
     * @param list<string|array{tanggal: string}> $hariLiburNasional
     * @return array<string, true>
     */
    private function indexHolidayDates(array $hariLiburNasional): array
    {
        $indexed = [];
        foreach ($hariLiburNasional as $item) {
            $date = is_array($item) ? (string) ($item['tanggal'] ?? '') : (string) $item;
            if ($date !== '') {
                $indexed[$date] = true;
            }
        }

        return $indexed;
    }
}
