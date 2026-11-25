<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTaxApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = trim((string) config('services.tax_api.key', ''));
        if ($expectedKey === '') {
            return $next($request);
        }

        $providedKey = (string) $request->header('X-Api-Key', '');
        if ($providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
            return response()->json([
                'error' => 'API key tidak valid atau tidak ditemukan',
            ], 401);
        }

        return $next($request);
    }
}
