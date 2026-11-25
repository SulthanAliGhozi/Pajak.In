<?php

declare(strict_types=1);

namespace App\Domain\Tax;

/**
 * Immutable DTO representing a complete PKB tax calculation breakdown.
 */
final readonly class TaxCalculationResult
{
    public function __construct(
        public string $jenisKendaraan,
        public string $provinsi,
        public string $jenisEntitas,
        public float $njkb,
        public int $urutanKepemilikan,
        public float $tarifDasar,
        public float $tarifProgresif,
        public float $pkbPokok,
        public float $pajakProgresif,
        public float $swdkllj,
        public float $dendaKeterlambatan,
        public float $totalTagihan,
        public int $bulanTerlambat,
        public string $effectiveDueDate,
        public string $tarifSumber,
        public string $ruleName,
        public float $opsenRate = 0.0,
        public float $opsenPkb = 0.0,
        public string $regulationNote = '',
    ) {
    }

    /**
     * @return array<string, int|float|string>
     */
    public function toArray(): array
    {
        return [
            'jenis_kendaraan' => $this->jenisKendaraan,
            'provinsi' => $this->provinsi,
            'jenis_entitas' => $this->jenisEntitas,
            'njkb' => $this->njkb,
            'urutan_kepemilikan' => $this->urutanKepemilikan,
            'tarif_dasar' => $this->tarifDasar,
            'tarif_progresif' => $this->tarifProgresif,
            'opsen_rate' => $this->opsenRate,
            'pkb_pokok' => $this->pkbPokok,
            'pajak_progresif' => $this->pajakProgresif,
            'opsen_pkb' => $this->opsenPkb,
            'swdkllj' => $this->swdkllj,
            'denda_keterlambatan' => $this->dendaKeterlambatan,
            'total_tagihan' => $this->totalTagihan,
            'bulan_terlambat' => $this->bulanTerlambat,
            'effective_due_date' => $this->effectiveDueDate,
            'tarif_sumber' => $this->tarifSumber,
            'rule_name' => $this->ruleName,
            'regulation_note' => $this->regulationNote,
        ];
    }
}
