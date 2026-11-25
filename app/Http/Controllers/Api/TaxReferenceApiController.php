<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\TaxReferenceApi\Exceptions\ReferenceValidationException;
use App\TaxReferenceApi\Repositories\TaxGuidelineRepository;
use App\TaxReferenceApi\Services\HolidayReferenceService;
use App\TaxReferenceApi\Services\NjkbEstimatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxReferenceApiController extends Controller
{
    private const string SERVICE_NAME = 'pajakin-api';
    private const string SERVICE_VERSION = '1.0.0';

    public function __construct(
        private readonly TaxGuidelineRepository $taxGuidelines,
        private readonly NjkbEstimatorService $njkbEstimator,
        private readonly HolidayReferenceService $holidayService,
    ) {
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => self::SERVICE_NAME,
            'version' => self::SERVICE_VERSION,
        ]);
    }

    public function tarif(Request $request): JsonResponse
    {
        try {
            if ($request->query->has('provinsi')) {
                $provinsi = $this->readScalarQuery($request, 'provinsi');
                $result = $this->taxGuidelines->lookup(['provinsi' => $provinsi]);
                if ($result['is_default_fallback'] === true) {
                    $result['requested_province'] = $this->taxGuidelines->normalizeProvince($provinsi);
                }

                return response()->json($result);
            }

            return response()->json($this->taxGuidelines->lookup());
        } catch (ReferenceValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->getErrors()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function njkb(Request $request): JsonResponse
    {
        try {
            $estimate = $this->njkbEstimator->estimate(
                jenis: $this->readScalarQuery($request, 'jenis'),
                merek: $this->readScalarQuery($request, 'merek'),
                tahun: $this->readScalarQuery($request, 'tahun'),
                cc: $this->readScalarQuery($request, 'cc'),
            );

            return response()->json(['data' => $estimate->toArray()]);
        } catch (ReferenceValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->getErrors()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function liburNasional(Request $request): JsonResponse
    {
        try {
            $criteria = [];
            if ($request->query->has('tahun')) {
                $criteria['tahun'] = $this->readScalarQuery($request, 'tahun');
            }

            return response()->json($this->holidayService->lookup($criteria));
        } catch (ReferenceValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->getErrors()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function readScalarQuery(Request $request, string $key): string
    {
        $raw = $request->query->all()[$key] ?? '';
        if (!is_scalar($raw)) {
            throw new \InvalidArgumentException("Parameter query \"{$key}\" tidak valid.");
        }

        return (string) $raw;
    }
}
