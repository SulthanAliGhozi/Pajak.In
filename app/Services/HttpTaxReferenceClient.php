<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TaxReferenceGatewayInterface;
use App\Exceptions\TaxApiConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * HTTP client consuming the Pajak.In reference API with short timeout and response caching.
 */
class HttpTaxReferenceClient implements TaxReferenceGatewayInterface
{
    private const int TARIF_CACHE_TTL_MINUTES = 60;
    private const int HOLIDAY_CACHE_TTL_HOURS = 24;

    public function getTarifByProvinsi(string $provinsi): array
    {
        $normalized = strtoupper(trim($provinsi));
        $cacheKey = 'pajakin:tax_api:tarif:' . md5($normalized);

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $payload = $this->requestJson('/v1/tarif', ['provinsi' => $normalized]);
        $data = $payload['data'] ?? null;
        if (!is_array($data) || !isset($data['pkb_base_rate'])) {
            throw new TaxApiConnectionException('Respons tarif dari API referensi tidak valid.');
        }

        Cache::put($cacheKey, $data, now()->addMinutes(self::TARIF_CACHE_TTL_MINUTES));

        return $data;
    }

    public function estimateNjkb(string $jenis, string $merek, int $tahun, int $cc): array
    {
        $query = [
            'jenis' => strtolower(trim($jenis)),
            'merek' => trim($merek),
            'tahun' => $tahun,
            'cc' => $cc,
        ];
        $cacheKey = 'pajakin:tax_api:njkb:' . md5(json_encode($query, JSON_THROW_ON_ERROR));

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $payload = $this->requestJson('/v1/njkb', $query);
        $data = $payload['data'] ?? null;
        if (!is_array($data) || !isset($data['estimated_njkb'])) {
            throw new TaxApiConnectionException('Respons estimasi NJKB dari API referensi tidak valid.');
        }

        Cache::put($cacheKey, $data, now()->addMinutes(self::TARIF_CACHE_TTL_MINUTES));

        return $data;
    }

    public function getHariLiburNasional(int $tahun): array
    {
        $cacheKey = "pajakin:tax_api:holidays:{$tahun}";

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $payload = $this->requestJson('/v1/libur-nasional', ['tahun' => $tahun]);
        $holidays = $payload['holidays'] ?? null;
        if (!is_array($holidays)) {
            throw new TaxApiConnectionException('Respons hari libur nasional dari API referensi tidak valid.');
        }

        Cache::put($cacheKey, $holidays, now()->addHours(self::HOLIDAY_CACHE_TTL_HOURS));

        return $holidays;
    }

    public function lastSource(): string
    {
        return 'api';
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function requestJson(string $path, array $query = []): array
    {
        $baseUrl = rtrim((string) config('services.tax_api.url', 'https://pajakin.test/api'), '/');
        $url = $baseUrl . $path;

        try {
            $response = $this->buildRequest()->get($url, $query);
        } catch (Throwable $exception) {
            throw new TaxApiConnectionException(
                "Gagal menghubungi API referensi pajak di {$url}.",
                ['url' => $url, 'error' => $exception->getMessage()],
                503,
                $exception,
            );
        }

        if (!$response->successful()) {
            throw new TaxApiConnectionException(
                "API referensi pajak mengembalikan status HTTP {$response->status()}.",
                ['url' => $url, 'status' => $response->status()],
                503,
            );
        }

        $decoded = $response->json();
        if (!is_array($decoded)) {
            throw new TaxApiConnectionException('Format JSON API referensi pajak tidak valid.');
        }

        return $decoded;
    }

    private function buildRequest(): PendingRequest
    {
        $timeout = max(1, (int) config('services.tax_api.timeout', 3));
        $request = Http::acceptJson()->timeout($timeout)->connectTimeout($timeout);

        $apiKey = trim((string) config('services.tax_api.key', ''));
        if ($apiKey !== '') {
            $request = $request->withHeaders(['X-Api-Key' => $apiKey]);
        }

        return $request;
    }
}
