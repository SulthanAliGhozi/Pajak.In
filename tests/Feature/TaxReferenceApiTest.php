<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\TaxReferenceApi\Services\HolidayReferenceService;
use App\TaxReferenceApi\Services\NjkbEstimatorService;
use Tests\TestCase;

class TaxReferenceApiTest extends TestCase
{
    public function test_health_endpoint_returns_service_metadata(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertExactJson([
                'status' => 'ok',
                'service' => 'pajakin-api',
                'version' => '1.0.0',
            ]);
    }

    public function test_tarif_endpoints_cover_regional_hkpd_opsen_and_fallback(): void
    {
        $this->getJson('/api/v1/tarif')
            ->assertOk()
            ->assertJsonPath('count', 9);

        $this->getJson('/api/v1/tarif?provinsi=jawa%20barat')
            ->assertOk()
            ->assertJsonPath('matched_province', 'JAWA BARAT')
            ->assertJsonPath('is_default_fallback', false)
            ->assertJsonPath('data.pkb_base_rate', 0.0112)
            ->assertJsonPath('data.opsen_rate', 0.66);

        $this->getJson('/api/v1/tarif?provinsi=DKI%20JAKARTA')
            ->assertOk()
            ->assertJsonPath('data.pkb_base_rate', 0.02)
            ->assertJsonPath('data.opsen_rate', 0);

        $this->getJson('/api/v1/tarif?provinsi=BALI')
            ->assertOk()
            ->assertJsonPath('data.progressive_increment', 0)
            ->assertJsonPath('data.pkb_over_200cc_rate', 0.012);

        $this->getJson('/api/v1/tarif?provinsi=Aceh')
            ->assertOk()
            ->assertJsonPath('matched_province', 'DEFAULT')
            ->assertJsonPath('is_default_fallback', true)
            ->assertJsonPath('requested_province', 'ACEH');

        $this->getJson('/api/v1/tarif?provinsi=')
            ->assertStatus(422)
            ->assertJsonStructure(['error', 'errors' => ['provinsi']]);
    }

    public function test_njkb_endpoint_and_validation_rules(): void
    {
        $this->getJson('/api/v1/njkb?jenis=mobil&merek=toyota&tahun=2022&cc=1500')
            ->assertOk()
            ->assertJsonPath('data.jenis', 'mobil')
            ->assertJsonPath('data.currency', 'IDR')
            ->assertJsonStructure(['data' => ['estimated_njkb', 'base_price', 'depreciation_factor', 'age_years']]);

        $this->getJson('/api/v1/njkb?jenis=kapal&merek=toyota&tahun=1960&cc=20')
            ->assertStatus(422)
            ->assertJsonStructure(['error', 'errors' => ['jenis', 'tahun', 'cc']]);

        $estimate = (new NjkbEstimatorService())->estimate('motor', 'honda', 2023, 160, 2026);
        $this->assertGreaterThan(10000000, $estimate->estimatedNjkb);
    }

    public function test_libur_nasional_endpoint_and_business_day_shift(): void
    {
        $this->getJson('/api/v1/libur-nasional?tahun=2026')
            ->assertOk()
            ->assertJsonPath('tahun', 2026)
            ->assertJsonStructure(['tahun', 'count', 'holidays']);

        $this->getJson('/api/v1/libur-nasional?tahun=1950')->assertStatus(422);
        $this->getJson('/api/v1/libur-nasional?tahun[]=2026')->assertStatus(400);

        $holidayService = new HolidayReferenceService();
        $this->assertTrue($holidayService->isNationalHoliday('2026-08-17'));
        $this->assertSame('2026-08-18', $holidayService->resolveBusinessDueDate('2026-08-15'));
    }

    public function test_optional_api_key_protects_versioned_routes_when_configured(): void
    {
        config(['services.tax_api.key' => 'secret-pajakin-key']);

        $this->getJson('/api/health')->assertOk();
        $this->getJson('/api/v1/tarif')->assertStatus(401);
        $this->getJson('/api/v1/tarif', ['X-Api-Key' => 'wrong'])->assertStatus(401);
        $this->getJson('/api/v1/tarif', ['X-Api-Key' => 'secret-pajakin-key'])->assertOk();
    }
}
