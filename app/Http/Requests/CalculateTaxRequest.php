<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateTaxRequest extends FormRequest
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
        $maxYear = (int) date('Y') + 1;

        return [
            'jenis_kendaraan' => ['required', 'string', Rule::in(['motor', 'mobil'])],
            'jenis_entitas' => ['required', 'string', Rule::in(['pribadi', 'usaha'])],
            'provinsi' => ['required', 'string', 'max:100'],
            'merek' => ['required', 'string', 'max:80'],
            'tahun_pembuatan' => ['required', 'integer', 'min:1970', "max:{$maxYear}"],
            'kapasitas_cc' => ['required', 'integer', 'min:50', 'max:10000'],
            'njkb' => ['nullable', 'numeric', 'min:0'],
            'urutan_kepemilikan' => ['required', 'integer', 'min:1', 'max:10'],
            'jatuh_tempo_stnk' => ['required', 'date'],
        ];
    }
}
