<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Gateway contract for retrieving provincial tax guidelines, NJKB estimates, and national holidays.
 */
interface TaxReferenceGatewayInterface
{
    /**
     * @return array{
     *     province: string,
     *     pkb_base_rate: float,
     *     progressive_increment: float,
     *     enterprise_flat_rate: float,
     *     late_penalty_rate_per_month: float,
     *     max_penalty_months: int,
     *     swdkllj_motor: float|int,
     *     swdkllj_mobil: float|int
     * }
     */
    public function getTarifByProvinsi(string $provinsi): array;

    /**
     * @return array{
     *     jenis: string,
     *     merek: string,
     *     tahun: int,
     *     cc: int,
     *     estimated_njkb: int,
     *     base_price: int,
     *     depreciation_factor: float,
     *     age_years: int,
     *     currency: string
     * }
     */
    public function estimateNjkb(string $jenis, string $merek, int $tahun, int $cc): array;

    /**
     * @return list<array{tanggal: string, nama: string, kategori: string}>
     */
    public function getHariLiburNasional(int $tahun): array;

    /**
     * Returns the data source used by the most recent gateway operation ('api' or 'fallback').
     */
    public function lastSource(): string;
}
