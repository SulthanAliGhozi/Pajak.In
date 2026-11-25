<?php

declare(strict_types=1);

namespace App\Http\Controllers\Petugas;

use App\Contracts\TaxReferenceGatewayInterface;
use App\Exceptions\PajakException;
use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Services\TaxCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class VerifikasiController extends Controller
{
    public function __construct(
        private readonly TaxReferenceGatewayInterface $gateway,
        private readonly TaxCalculatorService $calculator,
    ) {
    }

    public function index(Request $request): View
    {
        $statusFilter = (string) $request->query('status', 'pending');
        if (!in_array($statusFilter, ['pending', 'approved', 'rejected', 'all'], true)) {
            $statusFilter = 'pending';
        }

        $query = Kendaraan::query()
            ->with(['user.profile', 'berkas', 'verifier'])
            ->latest();

        if ($statusFilter !== 'all') {
            $query->where('status_verifikasi', $statusFilter);
        }

        $kendaraanList = $query->get();

        return view('petugas.verifikasi.index', compact('kendaraanList', 'statusFilter'));
    }

    public function show(Kendaraan $kendaraan): View
    {
        Gate::authorize('view', $kendaraan);
        $kendaraan->load(['user.profile', 'berkas', 'tagihan.pembayaran', 'verifier']);

        try {
            $estimatedNjkb = $this->gateway->estimateNjkb(
                $kendaraan->jenis_kendaraan,
                $kendaraan->merek,
                (int) $kendaraan->tahun_pembuatan,
                (int) $kendaraan->kapasitas_cc,
            );
        } catch (Throwable) {
            $estimatedNjkb = null;
        }

        $jenisEntitas = $kendaraan->user?->profile?->jenis_entitas ?? 'pribadi';

        try {
            $previewCalculation = $this->calculator->simulate($kendaraan->toDomainEntity(), $jenisEntitas);
        } catch (Throwable) {
            $previewCalculation = null;
        }

        return view('petugas.verifikasi.show', compact('kendaraan', 'estimatedNjkb', 'previewCalculation'));
    }

    public function approve(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        Gate::authorize('view', $kendaraan);

        $validated = $request->validate([
            'njkb' => ['nullable', 'numeric', 'min:1000000'],
            'catatan_petugas' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $kendaraan, $validated): void {
                $updates = [
                    'status_verifikasi' => 'approved',
                    'catatan_petugas' => $validated['catatan_petugas'] ?? $kendaraan->catatan_petugas,
                    'verified_by' => $request->user()->id,
                    'verified_at' => now(),
                ];

                if (!empty($validated['njkb'])) {
                    $updates['njkb'] = (float) $validated['njkb'];
                }

                $kendaraan->update($updates);
                $this->calculator->generateAndSaveTagihan($kendaraan->fresh());
            });

            return redirect()
                ->route('petugas.verifikasi.show', $kendaraan)
                ->with('success', 'Kendaraan berhasil disetujui dan tagihan pajak telah diterbitkan.');
        } catch (PajakException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Gagal menyetujui kendaraan: '.$e->getMessage()]);
        }
    }

    public function reject(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        Gate::authorize('view', $kendaraan);

        $validated = $request->validate([
            'catatan_petugas' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        try {
            $kendaraan->update([
                'status_verifikasi' => 'rejected',
                'catatan_petugas' => $validated['catatan_petugas'],
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            return redirect()
                ->route('petugas.verifikasi.show', $kendaraan)
                ->with('success', 'Pengajuan kendaraan telah ditolak dengan catatan perbaikan.');
        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Gagal menolak pengajuan: '.$e->getMessage()]);
        }
    }
}
