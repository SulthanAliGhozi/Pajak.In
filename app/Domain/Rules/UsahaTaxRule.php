<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Kendaraan\AbstractKendaraan;
use DateTimeInterface;

/**
 * Corporate taxpayer PKB strategy applying a flat enterprise rate without progressive surcharge.
 */
final class UsahaTaxRule extends AbstractTaxRule
{
    private const float DEFAULT_ENTERPRISE_RATE = 0.0112;

    public function getRuleName(): string
    {
        return 'usaha';
    }

    public function hitungPajak(
        AbstractKendaraan $kendaraan,
        array $tarifReferensi,
        ?DateTimeInterface $tanggalHitung = null,
        array $hariLiburNasional = [],
    ): array {
        $enterpriseDefault = (float) (
            $tarifReferensi['enterprise_flat_rate']
            ?? $tarifReferensi['pkb_base_rate']
            ?? self::DEFAULT_ENTERPRISE_RATE
        );
        $tarifDasar = ($tarifReferensi['pkb_over_200cc_rate'] ?? null) !== null && $kendaraan->getKapasitasCc() > 200
            ? (float) $tarifReferensi['pkb_over_200cc_rate']
            : $enterpriseDefault;

        $pkbPokok = $kendaraan->hitungPajakPokok($tarifDasar);
        $pajakProgresif = 0.0;
        $opsenRate = $this->resolveOpsenRate($tarifReferensi);
        $opsenPkb = $this->hitungOpsenPkb($pkbPokok, $opsenRate);
        $swdkllj = $kendaraan->hitungSwdkllj($tarifReferensi);

        $effectiveDueDate = $this->resolveEffectiveDueDate($kendaraan->getJatuhTempoStnk(), $hariLiburNasional);
        $bulanTerlambat = $this->hitungBulanTerlambat($effectiveDueDate, $tanggalHitung, $tarifReferensi);
        $denda = $this->hitungDenda($pkbPokok, $bulanTerlambat, $tarifReferensi);
        $total = round($pkbPokok + $opsenPkb + $swdkllj + $denda, 2);

        return [
            'rule_name' => $this->getRuleName(),
            'tarif_dasar' => $tarifDasar,
            'tarif_progresif' => 0.0,
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
