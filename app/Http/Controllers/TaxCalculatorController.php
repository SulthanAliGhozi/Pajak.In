<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\RegionProviderInterface;
use App\Contracts\TaxReferenceGatewayInterface;
use App\Domain\Kendaraan\KendaraanFactory;
use App\Http\Requests\CalculateTaxRequest;
use App\Services\TaxCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class TaxCalculatorController extends Controller
{
    public function __construct(
        private readonly RegionProviderInterface $regions,
        private readonly TaxReferenceGatewayInterface $gateway,
        private readonly TaxCalculatorService $calculator,
    ) {
    }

    public function index(): View
    {
        try {
            $provinces = $this->regions->getProvinces();
        } catch (Throwable) {
            $provinces = [];
        }

        return view('kalkulator.index', [
            'provinces' => $provinces,
            'result' => null,
            'apiSource' => 'api',
        ]);
    }

    public function calculate(CalculateTaxRequest $request): View|JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        try {
            $njkbSource = 'api';
            if (empty($validated['njkb'])) {
                $estimate = $this->gateway->estimateNjkb(
                    $validated['jenis_kendaraan'],
                    $validated['merek'],
                    (int) $validated['tahun_pembuatan'],
                    (int) $validated['kapasitas_cc'],
                );
                $njkb = (float) $estimate['estimated_njkb'];
                $njkbSource = $this->gateway->lastSource();
            } else {
                $njkb = (float) $validated['njkb'];
            }

            $kendaraan = KendaraanFactory::fromArray([
                ...$validated,
                'njkb' => $njkb,
                'plat_nomor' => 'SIMULASI',
                'model_kendaraan' => $validated['merek'],
            ]);

            $result = $this->calculator->simulate($kendaraan, $validated['jenis_entitas']);
            $apiSource = ($result->tarifSumber === 'api' && $njkbSource === 'api') ? 'api' : 'fallback';

            if ($request->expectsJson()) {
                return response()->json([
                    'data' => $result->toArray(),
                    'api_source' => $apiSource,
                ]);
            }

            return view('kalkulator.index', [
                'provinces' => $this->regions->getProvinces(),
                'result' => $result,
                'input' => $validated,
                'apiSource' => $apiSource,
            ]);
        } catch (Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menghitung simulasi pajak: '.$e->getMessage()]);
        }
    }
}
