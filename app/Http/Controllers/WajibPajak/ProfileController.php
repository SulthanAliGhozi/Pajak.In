<?php

declare(strict_types=1);

namespace App\Http\Controllers\WajibPajak;

use App\Contracts\RegionProviderInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(
        private readonly RegionProviderInterface $regions,
    ) {
    }

    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');
        $profile = $user->profile;

        try {
            $provinces = $this->regions->getProvinces();
        } catch (Throwable) {
            $provinces = [];
        }

        return view('wajib-pajak.profil', compact('user', 'profile', 'provinces'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $validated): void {
                $user->update(['name' => $validated['name']]);
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nik_npwp' => $validated['nik_npwp'],
                        'jenis_entitas' => $validated['jenis_entitas'],
                        'provinsi' => strtoupper(trim($validated['provinsi'])),
                        'kota' => strtoupper(trim($validated['kota'])),
                        'alamat' => $validated['alamat'],
                        'no_telepon' => $validated['no_telepon'],
                    ],
                );
            });

            return redirect()
                ->route('wp.profil.edit')
                ->with('success', 'Profil wajib pajak berhasil diperbarui.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui profil: '.$e->getMessage()]);
        }
    }
}
