<?php

declare(strict_types=1);

namespace App\Domain\Kendaraan;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Abstract base entity encapsulating vehicle tax attributes and verification state transitions.
 */
abstract class AbstractKendaraan
{
    public function __construct(
        protected ?int $id,
        protected string $platNomor,
        protected string $merek,
        protected string $modelKendaraan,
        protected int $tahunPembuatan,
        protected int $kapasitasCc,
        protected float $njkb,
        protected string $provinsi,
        protected DateTimeImmutable $jatuhTempoStnk,
        protected int $urutanKepemilikan = 1,
        protected string $statusVerifikasi = 'pending',
        protected ?string $catatanPetugas = null,
        protected ?int $verifiedBy = null,
        protected ?DateTimeImmutable $verifiedAt = null,
    ) {
        if ($this->njkb < 0) {
            throw new InvalidArgumentException('NJKB tidak boleh bernilai negatif.');
        }
        if ($this->urutanKepemilikan < 1) {
            throw new InvalidArgumentException('Urutan kepemilikan minimal bernilai 1.');
        }
    }

    abstract public function getJenisKendaraan(): string;

    abstract public function hitungPajakPokok(float $tarifDasar): float;

    /**
     * @param array<string, mixed> $tarifReferensi
     */
    abstract public function hitungSwdkllj(array $tarifReferensi = []): float;

    public function approve(
        int $petugasId,
        ?string $catatan = null,
        ?DateTimeImmutable $verifiedAt = null,
    ): void {
        $this->statusVerifikasi = 'approved';
        $this->verifiedBy = $petugasId;
        $this->catatanPetugas = $catatan !== null && trim($catatan) !== '' ? trim($catatan) : null;
        $this->verifiedAt = $verifiedAt ?? new DateTimeImmutable();
    }

    public function reject(
        int $petugasId,
        string $catatan,
        ?DateTimeImmutable $verifiedAt = null,
    ): void {
        $trimmedNote = trim($catatan);
        if ($trimmedNote === '') {
            throw new InvalidArgumentException('Catatan penolakan wajib diisi oleh petugas.');
        }

        $this->statusVerifikasi = 'rejected';
        $this->verifiedBy = $petugasId;
        $this->catatanPetugas = $trimmedNote;
        $this->verifiedAt = $verifiedAt ?? new DateTimeImmutable();
    }

    public function isApproved(): bool
    {
        return $this->statusVerifikasi === 'approved';
    }

    public function isPending(): bool
    {
        return $this->statusVerifikasi === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->statusVerifikasi === 'rejected';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlatNomor(): string
    {
        return $this->platNomor;
    }

    public function getMerek(): string
    {
        return $this->merek;
    }

    public function getModelKendaraan(): string
    {
        return $this->modelKendaraan;
    }

    public function getTahunPembuatan(): int
    {
        return $this->tahunPembuatan;
    }

    public function getKapasitasCc(): int
    {
        return $this->kapasitasCc;
    }

    public function getNjkb(): float
    {
        return $this->njkb;
    }

    public function getProvinsi(): string
    {
        return $this->provinsi;
    }

    public function getJatuhTempoStnk(): DateTimeImmutable
    {
        return $this->jatuhTempoStnk;
    }

    public function getUrutanKepemilikan(): int
    {
        return $this->urutanKepemilikan;
    }

    public function getStatusVerifikasi(): string
    {
        return $this->statusVerifikasi;
    }

    public function getCatatanPetugas(): ?string
    {
        return $this->catatanPetugas;
    }

    public function getVerifiedBy(): ?int
    {
        return $this->verifiedBy;
    }

    public function getVerifiedAt(): ?DateTimeImmutable
    {
        return $this->verifiedAt;
    }
}
