<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Kendaraan\AbstractKendaraan;
use DateTimeInterface;

/**
 * Individual taxpayer PKB strategy with progressive ownership surcharge and late penalties.
 */
final class PribadiTaxRule extends AbstractTaxRule
{
    private const float DEFAULT_BASE_RATE = 0.0112;
    private const float DEFAULT_PROGRESSIVE_INCREMENT = 0.005;

    public function getRuleName(): string
    {
        return 'pribadi';
    }

    public function hitungPajak(
        AbstractKendaraan $kendaraan,
        array $tarifReferensi,
        ?DateTimeInterface $tanggalHitung = null,
        array $hariLiburNasional = [],
    ): array {
        $tarifDasar = $this->resolveRegionalBaseRate($kendaraan, $tarifReferensi, self::DEFAULT_BASE_RATE);
        $increment = (float) ($tarifReferensi['progressive_increment'] ?? self::DEFAULT_PROGRESSIVE_INCREMENT);
        $tarifProgresif = max(0, $kendaraan->getUrutanKepemilikan() - 1) * $increment;

        $pkbPokok = $kendaraan->hitungPajakPokok($tarifDasar);
        $pajakProgresif = round($kendaraan->getNjkb() * $tarifProgresif, 2);
        $opsenRate = $this->resolveOpsenRate($tarifReferensi);
        $opsenPkb = $this->hitungOpsenPkb($pkbPokok + $pajakProgresif, $opsenRate);
        $swdkllj = $kendaraan->hitungSwdkllj($tarifReferensi);

        $effectiveDueDate = $this->resolveEffectiveDueDate($kendaraan->getJatuhTempoStnk(), $hariLiburNasional);
        $bulanTerlambat = $this->hitungBulanTerlambat($effectiveDueDate, $tanggalHitung, $tarifReferensi);
        $denda = $this->hitungDenda($pkbPokok + $pajakProgresif, $bulanTerlambat, $tarifReferensi);
        $total = round($pkbPokok + $pajakProgresif + $opsenPkb + $swdkllj + $denda, 2);

        return [
            'rule_name' => $this->getRuleName(),
            'tarif_dasar' => $tarifDasar,
            'tarif_progresif' => $tarifProgresif,
            'opsen_rate' => $opsenRate,
            'pkb_pokok' => $pkbPokok,
            'pajak_progresif' => $pajakProgresif,
            'opsen_pkb' => $opsenPkb,
            'swdkllj' => $swdkllj,
            'denda_keterlambatan' => $denda,
            'total_tagihan' => $total,
            'bulan_terlambat' => $bulanTerlambat,
            'effective_due_date' => $effectiveDueDate->format('Y-m-d'),
            'regulation_note' => (string) ($tarifReferensi['regulation_note'] ?? ''),
        ];
    }
}
