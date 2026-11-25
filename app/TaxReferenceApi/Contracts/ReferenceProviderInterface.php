<?php

declare(strict_types=1);

namespace App\TaxReferenceApi\Contracts;

interface ReferenceProviderInterface
{
    public function getProviderName(): string;

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function lookup(array $criteria = []): array;
}
