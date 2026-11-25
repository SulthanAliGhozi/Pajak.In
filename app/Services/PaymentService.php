<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\DokumenBelumTerverifikasiException;
use App\Exceptions\PajakException;
use App\Exceptions\PaymentGatewayException;
use App\Exceptions\TagihanSudahLunasException;
use App\Models\Pembayaran;
use App\Models\TagihanPajak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Executes tax bill settlements via Midtrans Snap Sandbox or local simulation.
 */
class PaymentService
{
    private readonly PaymentGatewayInterface $gateway;

    public function __construct(
        private readonly TaxCalculatorService $calculator,
        ?PaymentGatewayInterface $gateway = null,
    ) {
        $this->gateway = $gateway ?? new MidtransSnapGateway();
    }

    public function bayarTagihan(
        TagihanPajak $tagihan,
        string $metodePembayaran,
        ?float $nominalDibayar = null,
        ?string $kodeTransaksi = null,
    ): Pembayaran {
        return DB::transaction(function () use ($tagihan, $metodePembayaran, $nominalDibayar, $kodeTransaksi): Pembayaran {
            $lockedTagihan = TagihanPajak::query()
                ->with('kendaraan')
                ->lockForUpdate()
                ->findOrFail($tagihan->id);

            $this->ensurePayable($lockedTagihan);
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
                'kode_transaksi' => $kodeTransaksi ?: $this->generateKodeTransaksi($now->format('Ymd')),
                'no_etbpkp' => $this->generateNoEtbpkp($now->format('Y')),
                'metode_pembayaran' => trim($metodePembayaran),
                'nominal_dibayar' => $lockedTagihan->total_tagihan,
                'tanggal_bayar' => $now,
            ]);

            $lockedTagihan->tandaiLunas();

            return $pembayaran;
        });
    }

    /**
     * @return array{order_id: string, snap_token: string, redirect_url: string, gross_amount: int}
     */
    public function createMidtransCheckout(TagihanPajak $tagihan): array
    {
        $tagihan->loadMissing('kendaraan.user.profile');
        $this->ensurePayable($tagihan);
        $this->calculator->refreshDenda($tagihan);

        $orderId = sprintf('PAJAKIN-%d-%s', $tagihan->id, strtoupper(Str::random(6)));
        $snap = $this->gateway->createSnapTransaction($tagihan, $orderId);

        $tagihan->fill([
            'midtrans_order_id' => $orderId,
            'midtrans_snap_token' => $snap['snap_token'],
            'midtrans_redirect_url' => $snap['redirect_url'],
            'midtrans_status' => 'pending',
        ]);
        $tagihan->save();

        return [
            'order_id' => $orderId,
            'snap_token' => $snap['snap_token'],
            'redirect_url' => $snap['redirect_url'],
            'gross_amount' => (int) round((float) $tagihan->total_tagihan),
        ];
    }

    /**
     * @return array{status: string, message: string, pembayaran: ?Pembayaran}
     */
    public function syncMidtransStatus(TagihanPajak $tagihan): array
    {
        $tagihan->loadMissing('pembayaran', 'kendaraan');

        if ($tagihan->isLunas()) {
            return [
                'status' => 'lunas',
                'message' => 'Tagihan ini sudah berstatus lunas.',
                'pembayaran' => $tagihan->pembayaran,
            ];
        }

        if (empty($tagihan->midtrans_order_id)) {
            throw new PaymentGatewayException('Belum ada transaksi Midtrans untuk tagihan ini.', [], 422);
        }

        $remote = $this->gateway->fetchTransactionStatus((string) $tagihan->midtrans_order_id);

        return $this->applyMidtransStatus(
            $tagihan,
            $remote['transaction_status'],
            $remote['payment_type'],
            $remote['fraud_status'],
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{status: string, message: string, pembayaran: ?Pembayaran}
     */
    public function handleMidtransWebhook(array $payload): array
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if (!$this->gateway->verifyWebhookSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            throw new PaymentGatewayException('Signature callback Midtrans tidak valid.', ['order_id' => $orderId], 403);
        }

        $tagihan = TagihanPajak::query()
            ->with('pembayaran', 'kendaraan')
            ->where('midtrans_order_id', $orderId)
            ->first();

        if ($tagihan === null) {
            throw new PajakException('Tagihan untuk order Midtrans tersebut tidak ditemukan.', ['order_id' => $orderId], 404);
        }

        return $this->applyMidtransStatus(
            $tagihan,
            strtolower((string) ($payload['transaction_status'] ?? 'pending')),
            (string) ($payload['payment_type'] ?? 'midtrans'),
            strtolower((string) ($payload['fraud_status'] ?? '')),
        );
    }

    /**
     * @return array{status: string, message: string, pembayaran: ?Pembayaran}
     */
    private function applyMidtransStatus(
        TagihanPajak $tagihan,
        string $txStatus,
        string $paymentType,
        string $fraudStatus,
    ): array {
        if ($tagihan->isLunas()) {
            return [
                'status' => 'lunas',
                'message' => 'Tagihan sudah berstatus lunas.',
                'pembayaran' => $tagihan->pembayaran,
            ];
        }

        $isSettled = $txStatus === 'settlement'
            || ($txStatus === 'capture' && in_array($fraudStatus, ['', 'accept'], true));

        if ($isSettled) {
            $methodLabel = $this->formatPaymentMethod($paymentType);
            $pembayaran = $this->bayarTagihan(
                $tagihan,
                $methodLabel,
                null,
                (string) $tagihan->midtrans_order_id,
            );

            $tagihan->forceFill(['midtrans_status' => $txStatus])->save();

            return [
                'status' => 'lunas',
                'message' => "Pembayaran Midtrans berhasil. No. e-TBPKP: {$pembayaran->no_etbpkp}",
                'pembayaran' => $pembayaran,
            ];
        }

        if (in_array($txStatus, ['cancel', 'deny', 'expire'], true)) {
            $tagihan->forceFill([
                'midtrans_snap_token' => null,
                'midtrans_redirect_url' => null,
                'midtrans_status' => $txStatus,
            ])->save();

            return [
                'status' => $txStatus,
                'message' => "Transaksi Midtrans berstatus {$txStatus}. Silakan buat transaksi pembayaran baru.",
                'pembayaran' => null,
            ];
        }

        $tagihan->forceFill(['midtrans_status' => $txStatus])->save();

        return [
            'status' => $txStatus,
            'message' => 'Pembayaran Midtrans masih menunggu penyelesaian (pending). Selesaikan pembayaran di simulator Midtrans lalu periksa status kembali.',
            'pembayaran' => null,
        ];
    }

    private function ensurePayable(TagihanPajak $tagihan): void
    {
        if (!$tagihan->kendaraan->isApproved()) {
            throw new DokumenBelumTerverifikasiException(
                'Pembayaran ditolak karena kendaraan belum diverifikasi oleh petugas.',
                ['kendaraan_id' => $tagihan->kendaraan_id],
            );
        }

        if ($tagihan->isLunas()) {
            throw new TagihanSudahLunasException(
                'Tagihan pajak ini sudah dibayar sebelumnya.',
                ['tagihan_id' => $tagihan->id],
            );
        }
    }

    private function formatPaymentMethod(string $paymentType): string
    {
        $normalized = strtoupper(str_replace('_', ' ', trim($paymentType)));

        return $normalized !== '' ? "Midtrans ({$normalized})" : 'Midtrans Sandbox';
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
