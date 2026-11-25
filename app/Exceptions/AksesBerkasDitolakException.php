<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Thrown when an authenticated user attempts to access a private vehicle document without permission.
 */
final class AksesBerkasDitolakException extends PajakException
{
    public function __construct(
        string $message = 'Anda tidak memiliki izin untuk mengakses berkas kendaraan ini.',
        array $context = [],
        int $code = 403,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $code, $previous);
    }
}
