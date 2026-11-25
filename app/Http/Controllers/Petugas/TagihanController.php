<?php

declare(strict_types=1);

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\TagihanPajak;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = (string) $request->query('status', 'all');
        if (!in_array($statusFilter, ['belum_lunas', 'lunas', 'all'], true)) {
            $statusFilter = 'all';
        }

        $query = TagihanPajak::query()
            ->with(['kendaraan.user.profile', 'pembayaran'])
            ->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $tagihanList = $query->get();

        return view('petugas.tagihan.index', compact('tagihanList', 'statusFilter'));
    }
}
