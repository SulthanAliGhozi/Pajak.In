<?php

declare(strict_types=1);

namespace App\Http\Controllers\WajibPajak;

use App\Contracts\RegionProviderInterface;
use App\Contracts\TaxReferenceGatewayInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKendaraanRequest;
use App\Http\Requests\UpdateKendaraanRequest;
use App\Models\Kendaraan;
use App\Services\DocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class KendaraanController extends Controller
{
    public function __construct(
        private readonly RegionProviderInterface $regions,
        private readonly TaxReferenceGatewayInterface $gateway,
        private readonly DocumentStorageService $storageService,
    ) {
    }

    public function index(Request $request): View
    {
        $kendaraanList = $request->user()
            ->kendaraan()
            ->with(['berkas', 'tagihan' => fn ($query) => $query->latest('tahun_pajak')])
            ->latest()
            ->get();

        return view('wajib-pajak.kendaraan.index', compact('kendaraanList'));
    }

    public function create(): View
    {
        return view('wajib-pajak.kendaraan.create', [
            'provinces' => $this->loadProvinces(),
        ]);
    }

    public function store(StoreKendaraanRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            $urutan = Kendaraan::query()
                ->where('user_id', $user->id)
                ->where('jenis_kendaraan', $validated['jenis_kendaraan'])
                ->count() + 1;

            $njkb = $this->resolveNjkb($validated);

            $kendaraan = DB::transaction(function () use ($request, $user, $validated, $urutan, $njkb): Kendaraan {
                $kendaraan = Kendaraan::query()->create([
                    'user_id' => $user->id,
                    'jenis_kendaraan' => $validated['jenis_kendaraan'],
                    'plat_nomor' => $validated['plat_nomor'],
                    'merek' => $validated['merek'],
                    'model_kendaraan' => $validated['model_kendaraan'],
                    'tahun_pembuatan' => (int) $validated['tahun_pembuatan'],
                    'kapasitas_cc' => (int) $validated['kapasitas_cc'],
                    'njkb' => $njkb,
                    'provinsi' => strtoupper(trim($validated['provinsi'])),
                    'jatuh_tempo_stnk' => $validated['jatuh_tempo_stnk'],
                    'urutan_kepemilikan' => $urutan,
                    'status_verifikasi' => 'pending',
                ]);

                $this->storageService->store($kendaraan, $request->file('file_ktp'), 'ktp');
                $this->storageService->store($kendaraan, $request->file('file_stnk'), 'stnk');

                return $kendaraan;
            });

            return redirect()
                ->route('wp.kendaraan.show', $kendaraan)
                ->with('success', 'Kendaraan dan berkas berhasil didaftarkan. Menunggu verifikasi petugas.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal mendaftarkan kendaraan: '.$e->getMessage()]);
        }
    }

    public function show(Kendaraan $kendaraan): View
    {
        Gate::authorize('view', $kendaraan);
        $kendaraan->load(['berkas', 'tagihan.pembayaran', 'verifier']);

        return view('wajib-pajak.kendaraan.show', compact('kendaraan'));
    }

    public function edit(Kendaraan $kendaraan): View
    {
        Gate::authorize('update', $kendaraan);
        $kendaraan->load('berkas');

        return view('wajib-pajak.kendaraan.edit', [
            'kendaraan' => $kendaraan,
            'provinces' => $this->loadProvinces(),
        ]);
    }

    public function update(UpdateKendaraanRequest $request, Kendaraan $kendaraan): RedirectResponse
    {
        Gate::authorize('update', $kendaraan);
        $validated = $request->validated();

        try {
            $njkb = $this->resolveNjkb($validated);

            DB::transaction(function () use ($request, $kendaraan, $validated, $njkb): void {
                $kendaraan->update([
                    'jenis_kendaraan' => $validated['jenis_kendaraan'],
                    'plat_nomor' => $validated['plat_nomor'],
                    'merek' => $validated['merek'],
                    'model_kendaraan' => $validated['model_kendaraan'],
                    'tahun_pembuatan' => (int) $validated['tahun_pembuatan'],
                    'kapasitas_cc' => (int) $validated['kapasitas_cc'],
                    'njkb' => $njkb,
                    'provinsi' => strtoupper(trim($validated['provinsi'])),
                    'jatuh_tempo_stnk' => $validated['jatuh_tempo_stnk'],
                    'status_verifikasi' => 'pending',
                    'catatan_petugas' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                ]);

                if ($request->hasFile('file_ktp')) {
                    $this->replaceBerkas($kendaraan, $request->file('file_ktp'), 'ktp');
                }
                if ($request->hasFile('file_stnk')) {
                    $this->replaceBerkas($kendaraan, $request->file('file_stnk'), 'stnk');
                }
            });

            return redirect()
                ->route('wp.kendaraan.show', $kendaraan)
                ->with('success', 'Data kendaraan berhasil diperbarui dan diajukan ulang untuk verifikasi.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui kendaraan: '.$e->getMessage()]);
        }
    }

    public function destroy(Kendaraan $kendaraan): RedirectResponse
    {
        Gate::authorize('delete', $kendaraan);

        try {
            DB::transaction(function () use ($kendaraan): void {
                $kendaraan->load('berkas');
                foreach ($kendaraan->berkas as $berkas) {
                    Storage::disk('local')->delete($berkas->file_path);
                }
                $kendaraan->delete();
            });

            return redirect()
                ->route('wp.kendaraan.index')
                ->with('success', 'Data kendaraan berhasil dihapus.');
        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Gagal menghapus kendaraan: '.$e->getMessage()]);
        }
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveNjkb(array $validated): float
    {
        if (!empty($validated['njkb'])) {
            return (float) $validated['njkb'];
        }

        $estimate = $this->gateway->estimateNjkb(
            (string) $validated['jenis_kendaraan'],
            (string) $validated['merek'],
            (int) $validated['tahun_pembuatan'],
            (int) $validated['kapasitas_cc'],
        );

        return (float) $estimate['estimated_njkb'];
    }

    private function replaceBerkas(Kendaraan $kendaraan, UploadedFile $file, string $jenis): void
    {
        $existing = $kendaraan->berkas()->where('jenis_berkas', $jenis)->get();
        foreach ($existing as $item) {
            Storage::disk('local')->delete($item->file_path);
            $item->delete();
        }

        $this->storageService->store($kendaraan, $file, $jenis);
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function loadProvinces(): array
    {
        try {
            return $this->regions->getProvinces();
        } catch (Throwable) {
            return [];
        }
    }
}
