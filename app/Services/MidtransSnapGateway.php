<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentGatewayException;
use App\Models\TagihanPajak;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class MidtransSnapGateway implements PaymentGatewayInterface
{
    public function isConfigured(): bool
    {
        return trim((string) config('services.midtrans.server_key', '')) !== ''
            && trim((string) config('services.midtrans.client_key', '')) !== '';
    }

    public function createSnapTransaction(TagihanPajak $tagihan, string $orderId): array
    {
        $this->ensureConfigured();
        $tagihan->loadMissing('kendaraan.user.profile');

        $kendaraan = $tagihan->kendaraan;
        $user = $kendaraan->user;
        $grossAmount = (int) round((float) $tagihan->total_tagihan);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [
                [
                    'id' => "PKB-{$tagihan->id}",
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => substr("PKB {$tagihan->tahun_pajak} {$kendaraan->plat_nomor}", 0, 50),
                ],
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => (string) ($user->profile?->no_telepon ?? ''),
            ],
        ];

        $url = $this->snapBaseUrl() . '/snap/v1/transactions';

        try {
            $response = $this->client()->post($url, $payload);
        } catch (Throwable $e) {
            throw new PaymentGatewayException('Gagal menghubungi Midtrans Snap Sandbox.', ['order_id' => $orderId], 503, $e);
        }

        if (!$response->successful()) {
            $errorMsg = (string) ($response->json('error_messages.0') ?? 'Midtrans menolak pembuatan transaksi.');
            throw new PaymentGatewayException($errorMsg, ['status' => $response->status(), 'order_id' => $orderId], 502);
        }

        $token = (string) $response->json('token', '');
        $redirectUrl = (string) $response->json('redirect_url', '');
        if ($token === '') {
            throw new PaymentGatewayException('Snap token tidak ditemukan pada respons Midtrans.', ['order_id' => $orderId], 502);
        }

        return [
            'snap_token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    public function fetchTransactionStatus(string $orderId): array
    {
        $this->ensureConfigured();
        $url = $this->apiBaseUrl() . '/v2/' . urlencode($orderId) . '/status';

        try {
            $response = $this->client()->get($url);
        } catch (Throwable $e) {
            throw new PaymentGatewayException('Gagal mengambil status transaksi dari Midtrans.', ['order_id' => $orderId], 503, $e);
        }

        if (!$response->successful()) {
            throw new PaymentGatewayException(
                'Status transaksi belum tersedia di Midtrans. Selesaikan pembayaran terlebih dahulu.',
                ['status' => $response->status(), 'order_id' => $orderId],
                502,
            );
        }

        $data = $response->json();
        if (!is_array($data) || !isset($data['transaction_status'])) {
            throw new PaymentGatewayException('Respons status transaksi Midtrans tidak valid.', ['order_id' => $orderId], 502);
        }

        return [
            'order_id' => (string) ($data['order_id'] ?? $orderId),
            'transaction_status' => strtolower((string) $data['transaction_status']),
            'payment_type' => (string) ($data['payment_type'] ?? 'midtrans'),
            'fraud_status' => strtolower((string) ($data['fraud_status'] ?? '')),
            'gross_amount' => (float) ($data['gross_amount'] ?? 0),
        ];
    }

    public function verifyWebhookSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signatureKey,
    ): bool {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        if ($serverKey === '' || $signatureKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expected, $signatureKey);
    }

    private function ensureConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new PaymentGatewayException('Konfigurasi Midtrans Server Key / Client Key belum diatur.', [], 503);
        }
    }

    private function client(): PendingRequest
    {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        $timeout = max(3, (int) config('services.midtrans.timeout', 10));

        return Http::acceptJson()
            ->asJson()
            ->withBasicAuth($serverKey, '')
            ->timeout($timeout)
            ->connectTimeout($timeout);
    }

    private function snapBaseUrl(): string
    {
        return (bool) config('services.midtrans.is_production', false)
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    private function apiBaseUrl(): string
    {
        return (bool) config('services.midtrans.is_production', false)
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
