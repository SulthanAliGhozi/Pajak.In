<?php

declare(strict_types=1);

namespace App\TaxReferenceApi\Services;

use App\TaxReferenceApi\Contracts\ReferenceProviderInterface;
use App\TaxReferenceApi\Data\IndonesianHolidayCalendar;
use App\TaxReferenceApi\Exceptions\ReferenceValidationException;
use DateTimeImmutable;

final class HolidayReferenceService implements ReferenceProviderInterface
{
    private const int MIN_YEAR = 1970;
    private const int MAX_YEAR = 2100;

    public function getProviderName(): string
    {
        return 'holiday_reference_service';
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function lookup(array $criteria = []): array
    {
        $rawYear = $criteria['tahun'] ?? (int) date('Y');
        $year = $this->parseYear($rawYear);
        $holidays = $this->getHolidaysForYear($year);

        return [
            'tahun' => $year,
            'count' => count($holidays),
            'holidays' => $holidays,
        ];
    }

    /**
     * @return list<array{tanggal: string, nama: string, kategori: string}>
     */
    public function getHolidaysForYear(int|string $rawYear): array
    {
        $year = $this->parseYear($rawYear);
        $holidays = array_merge(
            $this->buildFixedHolidays($year),
            $this->buildEasterHolidays($year),
            IndonesianHolidayCalendar::MOVABLE_HOLIDAYS[$year] ?? []
        );

        usort($holidays, static fn (array $a, array $b): int => strcmp($a['tanggal'], $b['tanggal']));

        return array_map(
            static fn (array $item): array => [
                'tanggal' => $item['tanggal'],
                'nama' => $item['nama'],
                'kategori' => 'nasional',
            ],
            $holidays
        );
    }

    public function isNationalHoliday(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            return false;
        }

        $year = (int) $parsed->format('Y');
        foreach ($this->getHolidaysForYear($year) as $holiday) {
            if ($holiday['tanggal'] === $date) {
                return true;
            }
        }

        return false;
    }

    /**
     * Shifts weekend or national holiday due dates to the next Indonesian business day.
     */
    public function resolveBusinessDueDate(string $dueDate): string
    {
        $current = DateTimeImmutable::createFromFormat('!Y-m-d', $dueDate);
        if ($current === false || $current->format('Y-m-d') !== $dueDate) {
            throw new ReferenceValidationException('Format tanggal tidak valid (gunakan YYYY-MM-DD).', [
                'tanggal' => 'Format tanggal harus YYYY-MM-DD.',
            ]);
        }

        while (true) {
            $weekday = (int) $current->format('N');
            $formatted = $current->format('Y-m-d');
            if ($weekday < 6 && !$this->isNationalHoliday($formatted)) {
                return $formatted;
            }
            $current = $current->modify('+1 day');
        }
    }

    private function parseYear(int|string $rawYear): int
    {
        $year = filter_var($rawYear, FILTER_VALIDATE_INT);
        if ($year === false || $year < self::MIN_YEAR || $year > self::MAX_YEAR) {
            throw new ReferenceValidationException(
                sprintf('Parameter tahun harus antara %d dan %d.', self::MIN_YEAR, self::MAX_YEAR),
                ['tahun' => sprintf('Tahun harus antara %d dan %d.', self::MIN_YEAR, self::MAX_YEAR)]
            );
        }

        return (int) $year;
    }

    /**
     * @return list<array{tanggal: string, nama: string}>
     */
    private function buildFixedHolidays(int $year): array
    {
        return [
            ['tanggal' => sprintf('%d-01-01', $year), 'nama' => 'Tahun Baru Masehi'],
            ['tanggal' => sprintf('%d-05-01', $year), 'nama' => 'Hari Buruh Internasional'],
            ['tanggal' => sprintf('%d-06-01', $year), 'nama' => 'Hari Lahir Pancasila'],
            ['tanggal' => sprintf('%d-08-17', $year), 'nama' => 'Hari Proklamasi Kemerdekaan RI'],
            ['tanggal' => sprintf('%d-12-25', $year), 'nama' => 'Hari Raya Natal'],
        ];
    }

    /**
     * Computes Good Friday, Easter Sunday, and Ascension Day without ext-calendar.
     *
     * @return list<array{tanggal: string, nama: string}>
     */
    private function buildEasterHolidays(int $year): array
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        $easter = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));

        return [
            ['tanggal' => $easter->modify('-2 days')->format('Y-m-d'), 'nama' => 'Wafat Yesus Kristus'],
            ['tanggal' => $easter->format('Y-m-d'), 'nama' => 'Kebangkitan Yesus Kristus (Paskah)'],
            ['tanggal' => $easter->modify('+39 days')->format('Y-m-d'), 'nama' => 'Kenaikan Yesus Kristus'],
        ];
    }
}
