<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DokumenBelumTerverifikasiException;
use App\Exceptions\PajakException;
use App\Exceptions\TagihanSudahLunasException;
use App\Models\Pembayaran;
use App\Models\TagihanPajak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Executes atomic tax bill settlements and generates unique transaction and e-TBPKP numbers.
 */
class PaymentService
{
    public function __construct(
        private readonly TaxCalculatorService $calculator,
    ) {
    }

    public function bayarTagihan(
        TagihanPajak $tagihan,
        string $metodePembayaran,
        ?float $nominalDibayar = null,
    ): Pembayaran {
        return DB::transaction(function () use ($tagihan, $metodePembayaran, $nominalDibayar): Pembayaran {
            $lockedTagihan = TagihanPajak::query()
                ->with('kendaraan')
                ->lockForUpdate()
                ->findOrFail($tagihan->id);

            if (!$lockedTagihan->kendaraan->isApproved()) {
                throw new DokumenBelumTerverifikasiException(
                    'Pembayaran ditolak karena kendaraan belum diverifikasi oleh petugas.',
                    ['kendaraan_id' => $lockedTagihan->kendaraan_id],
                );
            }

            if ($lockedTagihan->isLunas()) {
                throw new TagihanSudahLunasException(
                    'Tagihan pajak ini sudah dibayar sebelumnya.',
                    ['tagihan_id' => $lockedTagihan->id],
                );
            }

            $this->calculator->refreshDenda($lockedTagihan);

            $amountToPay = $nominalDibayar ?? $lockedTagihan->total_tagihan;
            if ($amountToPay + 0.01 < $lockedTagihan->total_tagihan) {
                throw new PajakException(
                    'Nominal pembayaran kurang dari total tagihan yang harus dilunasi.',
                    ['expected' => $lockedTagihan->total_tagihan, 'provided' => $amountToPay],
                    422,
                );
            }

            $now = now();
            $pembayaran = Pembayaran::query()->create([
                'tagihan_id' => $lockedTagihan->id,
                'kode_transaksi' => $this->generateKodeTransaksi($now->format('Ymd')),
                'no_etbpkp' => $this->generateNoEtbpkp($now->format('Y')),
                'metode_pembayaran' => trim($metodePembayaran),
                'nominal_dibayar' => $lockedTagihan->total_tagihan,
                'tanggal_bayar' => $now,
            ]);

            $lockedTagihan->tandaiLunas();

            return $pembayaran;
        });
    }

    private function generateKodeTransaksi(string $dateStamp): string
    {
        return sprintf('TRX-%s-%s', $dateStamp, strtoupper(Str::random(6)));
    }

    private function generateNoEtbpkp(string $year): string
    {
        return sprintf('ETBPKP-%s-%s', $year, strtoupper(Str::random(8)));
    }
}
