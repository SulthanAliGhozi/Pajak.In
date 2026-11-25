<?php

declare(strict_types=1);

namespace App\Http\Controllers\WajibPajak;

use App\Exceptions\DokumenBelumTerverifikasiException;
use App\Exceptions\PajakException;
use App\Exceptions\TagihanSudahLunasException;
use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\TagihanPajak;
use App\Services\PaymentService;
use App\Services\PdfReceiptGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PembayaranController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly PdfReceiptGenerator $pdfGenerator,
    ) {
    }

    public function store(Request $request, TagihanPajak $tagihan): RedirectResponse
    {
        Gate::authorize('pay', $tagihan);

        $validated = $request->validate([
            'metode_pembayaran' => ['required', 'string', 'max:64'],
        ]);

        try {
            $pembayaran = $this->paymentService->bayarTagihan(
                $tagihan,
                $validated['metode_pembayaran'],
            );

            return redirect()
                ->route('wp.tagihan.show', $tagihan)
                ->with('success', "Pembayaran berhasil diproses. No. e-TBPKP: {$pembayaran->no_etbpkp}");
        } catch (DokumenBelumTerverifikasiException|TagihanSudahLunasException|PajakException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        } catch (Throwable) {
            return back()->withErrors(['payment' => 'Terjadi kesalahan saat memproses pembayaran.']);
        }
    }

    public function downloadEtbpkp(Pembayaran $pembayaran): Response
    {
        Gate::authorize('view', $pembayaran);

        return $this->pdfGenerator->download($pembayaran);
    }
}
