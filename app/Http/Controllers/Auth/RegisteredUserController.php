<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Contracts\RegionProviderInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\WajibPajakProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly RegionProviderInterface $regions,
    ) {
    }

    public function create(): View
    {
        try {
            $provinces = $this->regions->getProvinces();
        } catch (Throwable) {
            $provinces = [];
        }

        return view('auth.register', compact('provinces'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $user = DB::transaction(function () use ($validated): User {
                $user = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role' => 'wajib_pajak',
                ]);

                WajibPajakProfile::query()->create([
                    'user_id' => $user->id,
                    'nik_npwp' => $validated['nik_npwp'],
                    'jenis_entitas' => $validated['jenis_entitas'],
                    'provinsi' => strtoupper(trim($validated['provinsi'])),
                    'kota' => strtoupper(trim($validated['kota'])),
                    'alamat' => $validated['alamat'],
                    'no_telepon' => $validated['no_telepon'],
                ]);

                return $user;
            });

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('wp.dashboard');
        } catch (Throwable) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Registrasi gagal diproses. Silakan coba beberapa saat lagi.']);
        }
    }
}
