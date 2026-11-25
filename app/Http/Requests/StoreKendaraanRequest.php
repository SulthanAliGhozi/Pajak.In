<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKendaraanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isWajibPajak() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('plat_nomor')) {
            $normalized = preg_replace('/\s+/', ' ', strtoupper(trim((string) $this->input('plat_nomor'))));
            $this->merge(['plat_nomor' => $normalized]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxYear = (int) date('Y') + 1;

        return [
            'jenis_kendaraan' => ['required', 'string', Rule::in(['motor', 'mobil'])],
            'plat_nomor' => ['required', 'string', 'max:20', 'unique:kendaraan,plat_nomor'],
            'merek' => ['required', 'string', 'max:80'],
            'model_kendaraan' => ['required', 'string', 'max:100'],
            'tahun_pembuatan' => ['required', 'integer', 'min:1970', "max:{$maxYear}"],
            'kapasitas_cc' => ['required', 'integer', 'min:50', 'max:10000'],
            'provinsi' => ['required', 'string', 'max:100'],
            'jatuh_tempo_stnk' => ['required', 'date'],
            'njkb' => ['nullable', 'numeric', 'min:1000000'],
            'file_ktp' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'file_stnk' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }
}
