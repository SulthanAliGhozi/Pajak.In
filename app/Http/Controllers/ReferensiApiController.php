<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\TaxReferenceGatewayInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ReferensiApiController extends Controller
{
    public function __construct(
        private readonly TaxReferenceGatewayInterface $gateway,
    ) {
    }

    public function njkb(Request $request): JsonResponse
    {
        $maxYear = (int) date('Y') + 1;
        $validated = $request->validate([
            'jenis' => ['required', 'string', Rule::in(['motor', 'mobil'])],
            'merek' => ['required', 'string', 'max:80'],
            'tahun' => ['required', 'integer', 'min:1970', "max:{$maxYear}"],
            'cc' => ['required', 'integer', 'min:50', 'max:10000'],
        ]);

        try {
            $data = $this->gateway->estimateNjkb(
                $validated['jenis'],
                $validated['merek'],
                (int) $validated['tahun'],
                (int) $validated['cc'],
            );

            return response()->json([
                'data' => $data,
                'source' => $this->gateway->lastSource(),
            ]);
        } catch (Throwable) {
            return response()->json(['message' => 'Gagal mengambil estimasi NJKB.'], 500);
        }
    }
}
