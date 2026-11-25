<?php

declare(strict_types=1);

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\PdfReceiptGenerator;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PembayaranController extends Controller
{
    public function __construct(
        private readonly PdfReceiptGenerator $pdfGenerator,
    ) {
    }

    public function downloadEtbpkp(Pembayaran $pembayaran): Response
    {
        Gate::authorize('view', $pembayaran);

        return $this->pdfGenerator->download($pembayaran);
    }
}
