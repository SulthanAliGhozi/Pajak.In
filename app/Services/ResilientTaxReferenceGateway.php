<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TaxReferenceGatewayInterface;
use App\Exceptions\TaxApiConnectionException;

/**
 * Resilient gateway querying the HTTP reference API first and falling back locally on failure.
 */
class ResilientTaxReferenceGateway implements TaxReferenceGatewayInterface
{
    private string $source = 'api';

    public function __construct(
        private readonly HttpTaxReferenceClient $httpClient,
        private readonly LocalFallbackTaxClient $fallbackClient,
    ) {
    }

    public function getTarifByProvinsi(string $provinsi): array
    {
        return $this->executeWithFallback(
            fn (): array => $this->httpClient->getTarifByProvinsi($provinsi),
            fn (): array => $this->fallbackClient->getTarifByProvinsi($provinsi),
        );
    }

    public function estimateNjkb(string $jenis, string $merek, int $tahun, int $cc): array
    {
        return $this->executeWithFallback(
            fn (): array => $this->httpClient->estimateNjkb($jenis, $merek, $tahun, $cc),
            fn (): array => $this->fallbackClient->estimateNjkb($jenis, $merek, $tahun, $cc),
        );
    }

    public function getHariLiburNasional(int $tahun): array
    {
        return $this->executeWithFallback(
            fn (): array => $this->httpClient->getHariLiburNasional($tahun),
            fn (): array => $this->fallbackClient->getHariLiburNasional($tahun),
        );
    }

    public function lastSource(): string
    {
        return $this->source;
    }

    /**
     * @template T
     * @param callable(): T $primary
     * @param callable(): T $fallback
     * @return T
     */
    private function executeWithFallback(callable $primary, callable $fallback): mixed
    {
        try {
            $result = $primary();
            $this->source = 'api';

            return $result;
        } catch (TaxApiConnectionException) {
            $this->source = 'fallback';

            return $fallback();
        }
    }
}
