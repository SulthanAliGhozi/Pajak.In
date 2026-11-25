<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TaxReferenceGatewayInterface;
use App\Contracts\TaxRuleInterface;
use App\Domain\Kendaraan\AbstractKendaraan;
use App\Domain\Kendaraan\KendaraanFactory;
use App\Domain\Rules\PribadiTaxRule;
use App\Domain\Rules\UsahaTaxRule;
use App\Domain\Tax\TaxCalculationResult;
use App\Exceptions\DokumenBelumTerverifikasiException;
use App\Exceptions\TagihanSudahLunasException;
use App\Models\Kendaraan;
use App\Models\TagihanPajak;
use DateTimeInterface;

/**
 * Orchestrates PKB tax simulation, bill generation, and overdue penalty recalculation.
 */
class TaxCalculatorService
{
    public function __construct(
        private readonly TaxReferenceGatewayInterface $gateway,
    ) {
    }

    public function resolveRule(string $jenisEntitas): TaxRuleInterface
    {
        return strtolower(trim($jenisEntitas)) === 'usaha'
            ? new UsahaTaxRule()
            : new PribadiTaxRule();
    }

    public function simulate(
        AbstractKendaraan $kendaraan,
        string $jenisEntitas = 'pribadi',
        ?DateTimeInterface $tanggalHitung = null,
    ): TaxCalculationResult {
        $tarif = $this->gateway->getTarifByProvinsi($kendaraan->getProvinsi());
        $tarifSource = $this->gateway->lastSource();

        $dueYear = (int) $kendaraan->getJatuhTempoStnk()->format('Y');
        $holidays = $this->gateway->getHariLiburNasional($dueYear);
        $holidaySource = $this->gateway->lastSource();

        $source = ($tarifSource === 'api' && $holidaySource === 'api') ? 'api' : 'fallback';
        $normalizedEntitas = strtolower(trim($jenisEntitas)) === 'usaha' ? 'usaha' : 'pribadi';
        $rule = $this->resolveRule($normalizedEntitas);
        $breakdown = $rule->hitungPajak($kendaraan, $tarif, $tanggalHitung, $holidays);

        return new TaxCalculationResult(
            jenisKendaraan: $kendaraan->getJenisKendaraan(),
            provinsi: $kendaraan->getProvinsi(),
            jenisEntitas: $normalizedEntitas,
            njkb: $kendaraan->getNjkb(),
            urutanKepemilikan: $kendaraan->getUrutanKepemilikan(),
            tarifDasar: $breakdown['tarif_dasar'],
            tarifProgresif: $breakdown['tarif_progresif'],
            pkbPokok: $breakdown['pkb_pokok'],
            pajakProgresif: $breakdown['pajak_progresif'],
            swdkllj: $breakdown['swdkllj'],
            dendaKeterlambatan: $breakdown['denda_keterlambatan'],
            totalTagihan: $breakdown['total_tagihan'],
            bulanTerlambat: $breakdown['bulan_terlambat'],
            effectiveDueDate: $breakdown['effective_due_date'],
            tarifSumber: $source,
            ruleName: $breakdown['rule_name'],
            opsenRate: $breakdown['opsen_rate'],
            opsenPkb: $breakdown['opsen_pkb'],
            regulationNote: $breakdown['regulation_note'],
        );
    }

    public function generateAndSaveTagihan(
        Kendaraan $kendaraanModel,
        ?int $tahunPajak = null,
        ?DateTimeInterface $tanggalHitung = null,
    ): TagihanPajak {
        if (!$kendaraanModel->isApproved()) {
            throw new DokumenBelumTerverifikasiException(
                'Tagihan pajak hanya dapat diterbitkan untuk kendaraan yang telah disetujui petugas.',
                ['kendaraan_id' => $kendaraanModel->id, 'status' => $kendaraanModel->status_verifikasi],
            );
        }

        $kendaraanModel->loadMissing('user.profile');
        $jenisEntitas = $kendaraanModel->user?->profile?->jenis_entitas ?? 'pribadi';
        $domainEntity = $kendaraanModel->toDomainEntity();
        $result = $this->simulate($domainEntity, $jenisEntitas, $tanggalHitung);
        $targetYear = $tahunPajak ?? (int) $domainEntity->getJatuhTempoStnk()->format('Y');

        $existing = TagihanPajak::query()
            ->where('kendaraan_id', $kendaraanModel->id)
            ->where('tahun_pajak', $targetYear)
            ->first();

        if ($existing !== null && $existing->isLunas()) {
            throw new TagihanSudahLunasException(
                "Tagihan pajak tahun {$targetYear} untuk kendaraan ini sudah lunas.",
                ['tagihan_id' => $existing->id],
            );
        }

        $attributes = [
            'kendaraan_id' => $kendaraanModel->id,
            'tahun_pajak' => $targetYear,
            'jatuh_tempo' => $domainEntity->getJatuhTempoStnk()->format('Y-m-d'),
            'pkb_pokok' => $result->pkbPokok,
            'pajak_progresif' => $result->pajakProgresif,
            'opsen_pkb' => $result->opsenPkb,
            'swdkllj' => $result->swdkllj,
            'denda_keterlambatan' => $result->dendaKeterlambatan,
            'total_tagihan' => $result->totalTagihan,
            'tarif_sumber' => $result->tarifSumber,
            'regulation_note' => $result->regulationNote !== '' ? $result->regulationNote : null,
            'status' => 'belum_lunas',
        ];

        if ($existing !== null) {
            $existing->fill($attributes);
            $existing->save();

            return $existing;
        }

        return TagihanPajak::query()->create($attributes);
    }

    public function refreshDenda(
        TagihanPajak $tagihan,
        ?DateTimeInterface $tanggalHitung = null,
    ): TagihanPajak {
        if ($tagihan->isLunas()) {
            return $tagihan;
        }

        $tagihan->loadMissing('kendaraan.user.profile');
        $kendaraanModel = $tagihan->kendaraan;
        $jenisEntitas = $kendaraanModel->user?->profile?->jenis_entitas ?? 'pribadi';

        $domainEntity = KendaraanFactory::fromArray([
            'id' => $kendaraanModel->id,
            'jenis_kendaraan' => $kendaraanModel->jenis_kendaraan,
            'plat_nomor' => $kendaraanModel->plat_nomor,
            'merek' => $kendaraanModel->merek,
            'model_kendaraan' => $kendaraanModel->model_kendaraan,
            'tahun_pembuatan' => $kendaraanModel->tahun_pembuatan,
            'kapasitas_cc' => $kendaraanModel->kapasitas_cc,
            'njkb' => $kendaraanModel->njkb,
            'provinsi' => $kendaraanModel->provinsi,
            'jatuh_tempo_stnk' => $tagihan->jatuh_tempo,
            'urutan_kepemilikan' => $kendaraanModel->urutan_kepemilikan,
            'status_verifikasi' => $kendaraanModel->status_verifikasi,
        ]);

        $result = $this->simulate($domainEntity, $jenisEntitas, $tanggalHitung);
        $tagihan->fill([
            'pkb_pokok' => $result->pkbPokok,
            'pajak_progresif' => $result->pajakProgresif,
            'opsen_pkb' => $result->opsenPkb,
            'swdkllj' => $result->swdkllj,
            'denda_keterlambatan' => $result->dendaKeterlambatan,
            'total_tagihan' => $result->totalTagihan,
            'tarif_sumber' => $result->tarifSumber,
            'regulation_note' => $result->regulationNote !== '' ? $result->regulationNote : null,
        ]);
        $tagihan->save();

        return $tagihan;
    }
}
