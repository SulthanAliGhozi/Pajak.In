<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Domain\Kendaraan\AbstractKendaraan;
use DateTimeInterface;

/**
 * Strategy contract for computing annual vehicle tax components by taxpayer entity type.
 */
interface TaxRuleInterface
{
    public function getRuleName(): string;

    /**
     * Computes PKB base, progressive surcharge, SWDKLLJ, late penalty, and total due.
     *
     * @param array<string, mixed> $tarifReferensi
     * @param list<string|array{tanggal: string}> $hariLiburNasional
     * @return array{
     *     rule_name: string,
     *     tarif_dasar: float,
     *     tarif_progresif: float,
     *     opsen_rate: float,
     *     pkb_pokok: float,
     *     pajak_progresif: float,
     *     opsen_pkb: float,
     *     swdkllj: float,
     *     denda_keterlambatan: float,
     *     total_tagihan: float,
     *     bulan_terlambat: int,
     *     effective_due_date: string,
     *     regulation_note: string
     * }
     */
    public function hitungPajak(
        AbstractKendaraan $kendaraan,
        array $tarifReferensi,
        ?DateTimeInterface $tanggalHitung = null,
        array $hariLiburNasional = [],
    ): array;
}
