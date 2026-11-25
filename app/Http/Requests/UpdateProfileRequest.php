<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $profileId = $this->user()?->profile?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'nik_npwp' => [
                'required',
                'string',
                'max:20',
                Rule::unique('wajib_pajak_profiles', 'nik_npwp')->ignore($profileId),
            ],
            'jenis_entitas' => ['required', 'string', Rule::in(['pribadi', 'usaha'])],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:1000'],
            'no_telepon' => ['required', 'string', 'max:25'],
        ];
    }
}
