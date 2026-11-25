<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * Thrown when tax billing or payment is attempted before vehicle verification is approved.
 */
final class DokumenBelumTerverifikasiException extends PajakException
{
    public function __construct(
        string $message = 'Dokumen kendaraan belum terverifikasi oleh petugas.',
        array $context = [],
        int $code = 422,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $code, $previous);
    }
}
