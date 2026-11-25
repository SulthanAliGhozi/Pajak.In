<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class PaymentGatewayException extends PajakException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        string $message = 'Gagal berkomunikasi dengan layanan payment gateway.',
        array $context = [],
        int $code = 502,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $code, $previous);
    }
}
