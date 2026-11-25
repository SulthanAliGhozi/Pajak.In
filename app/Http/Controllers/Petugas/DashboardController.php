<?php

declare(strict_types=1);

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\TagihanPajak;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingList = Kendaraan::query()
            ->where('status_verifikasi', 'pending')
            ->with(['user.profile', 'berkas'])
            ->oldest()
            ->limit(10)
            ->get();

        $recentVerified = Kendaraan::query()
            ->whereIn('status_verifikasi', ['approved', 'rejected'])
            ->with(['user.profile', 'verifier'])
            ->latest('verified_at')
            ->limit(10)
            ->get();

        $counts = [
            'pending' => Kendaraan::query()->where('status_verifikasi', 'pending')->count(),
            'approved' => Kendaraan::query()->where('status_verifikasi', 'approved')->count(),
            'rejected' => Kendaraan::query()->where('status_verifikasi', 'rejected')->count(),
            'lunas' => TagihanPajak::query()->where('status', 'lunas')->count(),
        ];

        return view('petugas.dashboard', compact('pendingList', 'recentVerified', 'counts'));
    }
}
