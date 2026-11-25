<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\TagihanPajak;

interface PaymentGatewayInterface
{
    public function isConfigured(): bool;

    /**
     * @return array{snap_token: string, redirect_url: string}
     */
    public function createSnapTransaction(TagihanPajak $tagihan, string $orderId): array;

    /**
     * @return array{
     *     order_id: string,
     *     transaction_status: string,
     *     payment_type: string,
     *     fraud_status: string,
     *     gross_amount: float
     * }
     */
    public function fetchTransactionStatus(string $orderId): array;

    public function verifyWebhookSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signatureKey,
    ): bool;
}
