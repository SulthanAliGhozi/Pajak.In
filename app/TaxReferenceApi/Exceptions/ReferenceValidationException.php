<?php

declare(strict_types=1);

namespace App\TaxReferenceApi\Exceptions;

use InvalidArgumentException;

final class ReferenceValidationException extends InvalidArgumentException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        string $message,
        private readonly array $errors = [],
    ) {
        parent::__construct($message, 422);
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
