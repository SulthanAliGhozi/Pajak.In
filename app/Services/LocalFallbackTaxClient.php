<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TaxReferenceGatewayInterface;
use DateTimeImmutable;

/**
 * Deterministic offline reference provider used when the external tax API is unavailable.
 */
class LocalFallbackTaxClient implements TaxReferenceGatewayInterface
{
    /** @var array<string, array{float, float, float, float, float|null, string}> */
    private const array PROVINCE_RATES = [
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

    /** @var array<string, array<int, int>> */
    private const array CC_BASE_PRICES = [
        'motor' => [115 => 15500000, 135 => 18500000, 160 => 24500000, 250 => 45000000, 500 => 95000000, 10000 => 190000000],
        'mobil' => [1200 => 145000000, 1500 => 215000000, 2000 => 340000000, 2500 => 480000000, 3500 => 750000000, 10000 => 1250000000],
    ];

    /** @var array<string, array<string, float>> */
    private const array BRAND_MULTIPLIERS = [
        'motor' => ['honda' => 1.05, 'yamaha' => 1.02, 'suzuki' => 0.95, 'kawasaki' => 1.18, 'vespa' => 1.35],
        'mobil' => ['toyota' => 1.08, 'honda' => 1.06, 'mitsubishi' => 1.04, 'suzuki' => 0.96, 'daihatsu' => 0.92, 'hyundai' => 1.03, 'wuling' => 0.88],
    ];

    /** @var array<int, list<array{tanggal: string, nama: string}>> */
    private const array MOVABLE_HOLIDAYS = [
        2025 => [
            ['tanggal' => '2025-01-27', 'nama' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['tanggal' => '2025-01-29', 'nama' => 'Tahun Baru Imlek 2576 Kongzili'],
            ['tanggal' => '2025-03-29', 'nama' => 'Hari Suci Nyepi 1947'],
            ['tanggal' => '2025-03-31', 'nama' => 'Hari Raya Idul Fitri 1446 H'],
            ['tanggal' => '2025-04-01', 'nama' => 'Hari Raya Idul Fitri 1446 H'],
            ['tanggal' => '2025-05-12', 'nama' => 'Hari Raya Waisak 2569 BE'],
            ['tanggal' => '2025-06-06', 'nama' => 'Hari Raya Idul Adha 1446 H'],
            ['tanggal' => '2025-06-27', 'nama' => 'Tahun Baru Islam 1447 H'],
            ['tanggal' => '2025-09-05', 'nama' => 'Maulid Nabi Muhammad SAW'],
        ],
        2026 => [
            ['tanggal' => '2026-01-16', 'nama' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['tanggal' => '2026-02-17', 'nama' => 'Tahun Baru Imlek 2577 Kongzili'],
            ['tanggal' => '2026-03-19', 'nama' => 'Hari Suci Nyepi 1948'],
            ['tanggal' => '2026-03-21', 'nama' => 'Hari Raya Idul Fitri 1447 H'],
            ['tanggal' => '2026-03-22', 'nama' => 'Hari Raya Idul Fitri 1447 H'],
            ['tanggal' => '2026-05-27', 'nama' => 'Hari Raya Idul Adha 1447 H'],
            ['tanggal' => '2026-05-31', 'nama' => 'Hari Raya Waisak 2570 BE'],
            ['tanggal' => '2026-06-16', 'nama' => 'Tahun Baru Islam 1448 H'],
            ['tanggal' => '2026-08-25', 'nama' => 'Maulid Nabi Muhammad SAW'],
        ],
        2027 => [
            ['tanggal' => '2027-01-05', 'nama' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['tanggal' => '2027-02-06', 'nama' => 'Tahun Baru Imlek 2578 Kongzili'],
            ['tanggal' => '2027-03-08', 'nama' => 'Hari Suci Nyepi 1949'],
            ['tanggal' => '2027-03-10', 'nama' => 'Hari Raya Idul Fitri 1448 H'],
            ['tanggal' => '2027-03-11', 'nama' => 'Hari Raya Idul Fitri 1448 H'],
            ['tanggal' => '2027-05-17', 'nama' => 'Hari Raya Idul Adha 1448 H'],
            ['tanggal' => '2027-05-20', 'nama' => 'Hari Raya Waisak 2571 BE'],
            ['tanggal' => '2027-06-06', 'nama' => 'Tahun Baru Islam 1449 H'],
            ['tanggal' => '2027-08-15', 'nama' => 'Maulid Nabi Muhammad SAW'],
        ],
    ];

    public function getTarifByProvinsi(string $provinsi): array
    {
        $normalized = strtoupper((string) preg_replace('/\s+/', ' ', trim($provinsi)));
        $matched = isset(self::PROVINCE_RATES[$normalized]) ? $normalized : 'DEFAULT';
        [$baseRate, $progressiveIncrement, $enterpriseRate, $opsenRate, $over200CcRate, $note] = self::PROVINCE_RATES[$matched];

        return [
            'province' => $matched,
            'pkb_base_rate' => $baseRate,
            'progressive_increment' => $progressiveIncrement,
            'enterprise_flat_rate' => $enterpriseRate,
            'opsen_rate' => $opsenRate,
            'pkb_over_200cc_rate' => $over200CcRate,
            'regulation_note' => $note,
            'late_penalty_rate_per_month' => 0.02,
            'max_penalty_months' => 24,
            'swdkllj_motor' => 35000,
            'swdkllj_mobil' => 143000,
        ];
    }

    public function estimateNjkb(string $jenis, string $merek, int $tahun, int $cc): array
    {
        $normalizedJenis = strtolower(trim($jenis)) === 'mobil' ? 'mobil' : 'motor';
        $normalizedMerek = strtolower(trim($merek));
        $base = self::CC_BASE_PRICES[$normalizedJenis][10000];

        foreach (self::CC_BASE_PRICES[$normalizedJenis] as $maxCc => $price) {
            if ($cc <= $maxCc) {
                $base = $price;
                break;
            }
        }

        $multiplier = self::BRAND_MULTIPLIERS[$normalizedJenis][$normalizedMerek] ?? 1.0;
        $basePrice = (int) round($base * $multiplier);
        $ageYears = max(0, (int) date('Y') - $tahun);
        $factor = max(0.22, round(0.90 * (0.92 ** $ageYears), 4));
        $estimatedNjkb = (int) (round(($basePrice * $factor) / 100000) * 100000);

        return [
            'jenis' => $normalizedJenis,
            'merek' => $normalizedMerek,
            'tahun' => $tahun,
            'cc' => $cc,
            'estimated_njkb' => $estimatedNjkb,
            'base_price' => $basePrice,
            'depreciation_factor' => $factor,
            'age_years' => $ageYears,
            'currency' => 'IDR',
        ];
    }

    public function getHariLiburNasional(int $tahun): array
    {
        $fixed = [
            ['tanggal' => "{$tahun}-01-01", 'nama' => 'Tahun Baru Masehi'],
            ['tanggal' => "{$tahun}-05-01", 'nama' => 'Hari Buruh Internasional'],
            ['tanggal' => "{$tahun}-06-01", 'nama' => 'Hari Lahir Pancasila'],
            ['tanggal' => "{$tahun}-08-17", 'nama' => 'Hari Proklamasi Kemerdekaan RI'],
            ['tanggal' => "{$tahun}-12-25", 'nama' => 'Hari Raya Natal'],
        ];

        $holidays = array_merge($fixed, $this->computeEasterHolidays($tahun), self::MOVABLE_HOLIDAYS[$tahun] ?? []);
        usort($holidays, static fn (array $a, array $b): int => strcmp($a['tanggal'], $b['tanggal']));

        return array_map(
            static fn (array $item): array => [
                'tanggal' => $item['tanggal'],
                'nama' => $item['nama'],
                'kategori' => 'nasional',
            ],
            $holidays,
        );
    }

    public function lastSource(): string
    {
        return 'fallback';
    }

    /**
     * @return list<array{tanggal: string, nama: string}>
     */
    private function computeEasterHolidays(int $year): array
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
