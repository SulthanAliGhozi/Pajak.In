<?php

declare(strict_types=1);

namespace App\Http\Controllers\WajibPajak;

use App\Http\Controllers\Controller;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TaxCalculatorService $calculator,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->profile;

        $kendaraanList = $user->kendaraan()
            ->with([
                'berkas',
                'tagihan' => fn ($query) => $query->latest('tahun_pajak')->with('pembayaran'),
            ])
            ->latest()
            ->get();

        foreach ($kendaraanList as $kendaraan) {
            foreach ($kendaraan->tagihan as $tagihan) {
                if (!$tagihan->isLunas()) {
                    try {
                        $this->calculator->refreshDenda($tagihan);
                    } catch (Throwable) {
                        // Preserve last persisted penalty when reference gateway fails.
                    }
                }
            }
        }

        $unpaidTagihan = $kendaraanList->pluck('tagihan')->flatten()->where('status', 'belum_lunas');

        $summary = [
            'total_kendaraan' => $kendaraanList->count(),
            'pending_verifikasi' => $kendaraanList->where('status_verifikasi', 'pending')->count(),
            'tagihan_belum_lunas' => $unpaidTagihan->count(),
            'total_nominal_belum_lunas' => (float) $unpaidTagihan->sum('total_tagihan'),
        ];

        return view('wajib-pajak.dashboard', compact('user', 'profile', 'kendaraanList', 'summary'));
    }
}
