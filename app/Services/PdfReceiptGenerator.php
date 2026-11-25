<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates official digital tax payment receipts (e-TBPKP) as PDF documents.
 */
class PdfReceiptGenerator
{
    public function renderEtbpkp(Pembayaran $pembayaran): DomPdfWrapper
    {
        $pembayaran->loadMissing([
            'tagihan.kendaraan.user.profile',
            'tagihan.kendaraan.verifier',
        ]);

        return Pdf::loadView('pdfs.etbpkp', [
            'pembayaran' => $pembayaran,
            'tagihan' => $pembayaran->tagihan,
            'kendaraan' => $pembayaran->tagihan->kendaraan,
            'wajibPajak' => $pembayaran->tagihan->kendaraan->user,
            'profile' => $pembayaran->tagihan->kendaraan->user->profile,
        ])->setPaper('a4', 'portrait');
    }

    public function download(Pembayaran $pembayaran): Response
    {
        $filename = sprintf('e-TBPKP-%s.pdf', $pembayaran->no_etbpkp);

        return $this->renderEtbpkp($pembayaran)->download($filename);
    }

    public function stream(Pembayaran $pembayaran): Response
    {
        $filename = sprintf('e-TBPKP-%s.pdf', $pembayaran->no_etbpkp);

        return $this->renderEtbpkp($pembayaran)->stream($filename);
    }
}
