<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RegionProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Fetches Indonesian provinces from the Wilayah API with 24-hour caching and static fallback.
 */
class RegionApiClient implements RegionProviderInterface
{
    private const string CACHE_KEY = 'sip_pkb:regions:provinces';

    /** @var list<array{id: string, name: string}> */
    private const array FALLBACK_PROVINCES = [
        ['id' => '11', 'name' => 'ACEH'],
        ['id' => '12', 'name' => 'SUMATERA UTARA'],
        ['id' => '13', 'name' => 'SUMATERA BARAT'],
        ['id' => '14', 'name' => 'RIAU'],
        ['id' => '15', 'name' => 'JAMBI'],
        ['id' => '16', 'name' => 'SUMATERA SELATAN'],
        ['id' => '17', 'name' => 'BENGKULU'],
        ['id' => '18', 'name' => 'LAMPUNG'],
        ['id' => '19', 'name' => 'KEPULAUAN BANGKA BELITUNG'],
        ['id' => '21', 'name' => 'KEPULAUAN RIAU'],
        ['id' => '31', 'name' => 'DKI JAKARTA'],
        ['id' => '32', 'name' => 'JAWA BARAT'],
        ['id' => '33', 'name' => 'JAWA TENGAH'],
        ['id' => '34', 'name' => 'DI YOGYAKARTA'],
        ['id' => '35', 'name' => 'JAWA TIMUR'],
        ['id' => '36', 'name' => 'BANTEN'],
        ['id' => '51', 'name' => 'BALI'],
        ['id' => '52', 'name' => 'NUSA TENGGARA BARAT'],
        ['id' => '53', 'name' => 'NUSA TENGGARA TIMUR'],
        ['id' => '61', 'name' => 'KALIMANTAN BARAT'],
        ['id' => '62', 'name' => 'KALIMANTAN TENGAH'],
        ['id' => '63', 'name' => 'KALIMANTAN SELATAN'],
        ['id' => '64', 'name' => 'KALIMANTAN TIMUR'],
        ['id' => '65', 'name' => 'KALIMANTAN UTARA'],
        ['id' => '71', 'name' => 'SULAWESI UTARA'],
        ['id' => '72', 'name' => 'SULAWESI TENGAH'],
        ['id' => '73', 'name' => 'SULAWESI SELATAN'],
        ['id' => '74', 'name' => 'SULAWESI TENGGARA'],
        ['id' => '75', 'name' => 'GORONTALO'],
        ['id' => '76', 'name' => 'SULAWESI BARAT'],
        ['id' => '81', 'name' => 'MALUKU'],
        ['id' => '82', 'name' => 'MALUKU UTARA'],
        ['id' => '91', 'name' => 'PAPUA BARAT'],
        ['id' => '92', 'name' => 'PAPUA'],
        ['id' => '93', 'name' => 'PAPUA TENGAH'],
        ['id' => '94', 'name' => 'PAPUA PEGUNUNGAN'],
        ['id' => '95', 'name' => 'PAPUA SELATAN'],
        ['id' => '96', 'name' => 'PAPUA BARAT DAYA'],
    ];

    private string $source = 'api';

    public function getProvinces(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached) && $cached !== []) {
            $this->source = 'api';

            return $cached;
        }

        $baseUrl = rtrim((string) config('services.region_api.url', 'https://emsifa.github.io/api-wilayah-indonesia/api'), '/');
        $timeout = max(1, (int) config('services.region_api.timeout', 3));

        try {
            $response = Http::acceptJson()->timeout($timeout)->connectTimeout($timeout)->get("{$baseUrl}/provinces.json");
            if ($response->successful() && is_array($response->json()) && $response->json() !== []) {
                $provinces = array_values(array_map(
                    static fn (array $row): array => [
                        'id' => (string) ($row['id'] ?? ''),
                        'name' => strtoupper(trim((string) ($row['name'] ?? ''))),
                    ],
                    $response->json(),
                ));

                Cache::put(self::CACHE_KEY, $provinces, now()->addHours(24));
                $this->source = 'api';

                return $provinces;
            }
        } catch (Throwable) {
            // Fall through to deterministic province list when external API is unreachable.
        }

        $this->source = 'fallback';

        return self::FALLBACK_PROVINCES;
    }

    public function lastSource(): string
    {
        return $this->source;
    }
}
