<?php

declare(strict_types=1);

namespace App\Http\Controllers\WajibPajak;

use App\Http\Controllers\Controller;
use App\Models\TagihanPajak;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class TagihanController extends Controller
{
    public function __construct(
        private readonly TaxCalculatorService $calculator,
    ) {
    }

    public function index(Request $request): View
    {
        $tagihanList = TagihanPajak::query()
            ->whereHas('kendaraan', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['kendaraan', 'pembayaran'])
            ->latest('tahun_pajak')
            ->latest()
            ->get();

        foreach ($tagihanList as $tagihan) {
            if (!$tagihan->isLunas()) {
                try {
                    $this->calculator->refreshDenda($tagihan);
                } catch (Throwable) {
                    // Keep persisted values when calculation gateway fails.
                }
            }
        }

        return view('wajib-pajak.tagihan.index', compact('tagihanList'));
    }

    public function show(TagihanPajak $tagihan): View
    {
        Gate::authorize('view', $tagihan);
        $tagihan->load(['kendaraan.user.profile', 'pembayaran']);

        if (!$tagihan->isLunas()) {
            try {
                $this->calculator->refreshDenda($tagihan);
            } catch (Throwable) {
                // Keep persisted values when calculation gateway fails.
            }
        }

        return view('wajib-pajak.tagihan.show', compact('tagihan'));
    }
}
