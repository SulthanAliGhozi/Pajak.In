<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Provider contract for Indonesian administrative province data.
 */
interface RegionProviderInterface
{
    /**
     * @return list<array{id: string, name: string}>
     */
    public function getProvinces(): array;

    /**
     * Returns the source used on the last lookup ('api' or 'fallback').
     */
    public function lastSource(): string;
}
