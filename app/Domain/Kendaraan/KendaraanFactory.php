<?php

declare(strict_types=1);

namespace App\Domain\Kendaraan;

use App\Models\Kendaraan;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Factory creating polymorphic Motor or Mobil domain entities from Eloquent models or arrays.
 */
final class KendaraanFactory
{
    public static function fromModel(Kendaraan $model): AbstractKendaraan
    {
        return self::fromArray([
            'id' => $model->id,
            'jenis_kendaraan' => $model->jenis_kendaraan,
            'plat_nomor' => $model->plat_nomor,
            'merek' => $model->merek,
            'model_kendaraan' => $model->model_kendaraan,
            'tahun_pembuatan' => $model->tahun_pembuatan,
            'kapasitas_cc' => $model->kapasitas_cc,
            'njkb' => $model->njkb,
            'provinsi' => $model->provinsi,
            'jatuh_tempo_stnk' => $model->jatuh_tempo_stnk,
            'urutan_kepemilikan' => $model->urutan_kepemilikan,
            'status_verifikasi' => $model->status_verifikasi,
            'catatan_petugas' => $model->catatan_petugas,
            'verified_by' => $model->verified_by,
            'verified_at' => $model->verified_at,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): AbstractKendaraan
    {
        $jenis = strtolower(trim((string) ($data['jenis_kendaraan'] ?? '')));
        $jatuhTempo = self::parseDate($data['jatuh_tempo_stnk'] ?? null, true);
        $verifiedAt = self::parseDate($data['verified_at'] ?? null, false);

        $args = [
            'id' => isset($data['id']) ? (int) $data['id'] : null,
            'platNomor' => (string) ($data['plat_nomor'] ?? ''),
            'merek' => (string) ($data['merek'] ?? ''),
            'modelKendaraan' => (string) ($data['model_kendaraan'] ?? ''),
            'tahunPembuatan' => (int) ($data['tahun_pembuatan'] ?? date('Y')),
            'kapasitasCc' => (int) ($data['kapasitas_cc'] ?? 150),
            'njkb' => (float) ($data['njkb'] ?? 0),
            'provinsi' => strtoupper(trim((string) ($data['provinsi'] ?? 'JAWA BARAT'))),
            'jatuhTempoStnk' => $jatuhTempo,
            'urutanKepemilikan' => max(1, (int) ($data['urutan_kepemilikan'] ?? 1)),
            'statusVerifikasi' => (string) ($data['status_verifikasi'] ?? 'pending'),
            'catatanPetugas' => isset($data['catatan_petugas']) ? (string) $data['catatan_petugas'] : null,
            'verifiedBy' => isset($data['verified_by']) ? (int) $data['verified_by'] : null,
            'verifiedAt' => $verifiedAt,
        ];

        return match ($jenis) {
            'motor' => new Motor(...$args),
            'mobil' => new Mobil(...$args),
            default => throw new InvalidArgumentException("Jenis kendaraan tidak dikenali: {$jenis}"),
        };
    }

    private static function parseDate(mixed $value, bool $required): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            if ($required) {
                return new DateTimeImmutable('today');
            }

            return null;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return new DateTimeImmutable((string) $value);
    }
}
