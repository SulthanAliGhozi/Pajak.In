<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nik_npwp' => ['required', 'string', 'max:20', 'unique:wajib_pajak_profiles,nik_npwp'],
            'jenis_entitas' => ['required', 'string', Rule::in(['pribadi', 'usaha'])],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:1000'],
            'no_telepon' => ['required', 'string', 'max:25'],
        ];
    }
}
