<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AksesBerkasDitolakException;
use App\Exceptions\PajakException;
use App\Models\BerkasKendaraan;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BerkasController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $storageService,
    ) {
    }

    public function show(Request $request, BerkasKendaraan $berkas): BinaryFileResponse
    {
        try {
            $path = $this->storageService->getAbsolutePath($berkas, $request->user());

            return response()->file($path);
        } catch (AksesBerkasDitolakException $e) {
            abort(403, $e->getMessage());
        } catch (PajakException $e) {
            abort(404, $e->getMessage());
        }
    }
}
