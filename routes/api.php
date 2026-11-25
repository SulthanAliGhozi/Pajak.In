<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TaxReferenceApiController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [TaxReferenceApiController::class, 'health'])->name('api.health');

Route::prefix('v1')
    ->middleware('tax_api_key')
    ->group(function (): void {
        Route::get('/tarif', [TaxReferenceApiController::class, 'tarif'])->name('api.v1.tarif');
        Route::get('/njkb', [TaxReferenceApiController::class, 'njkb'])->name('api.v1.njkb');
        Route::get('/libur-nasional', [TaxReferenceApiController::class, 'liburNasional'])->name('api.v1.libur');
    });
