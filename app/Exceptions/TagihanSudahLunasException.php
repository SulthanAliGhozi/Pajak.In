<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Thrown when payment is attempted on a tax bill that has already been settled.
 */
final class TagihanSudahLunasException extends PajakException
{
    public function __construct(
        string $message = 'Tagihan pajak ini sudah berstatus lunas.',
        array $context = [],
        int $code = 409,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $code, $previous);
    }
}
