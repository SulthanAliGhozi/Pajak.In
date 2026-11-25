<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Thrown when the external tax reference API is unreachable or returns an invalid response.
 */
final class TaxApiConnectionException extends PajakException
{
    public function __construct(
        string $message = 'Koneksi ke layanan referensi tarif pajak gagal.',
        array $context = [],
        int $code = 503,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $code, $previous);
    }
}
