<?php

namespace App\Http\Requests;

use App\Models\Pesanan;
use Illuminate\Foundation\Http\FormRequest;

class TolakPesananRequest extends FormRequest
{
    /** Tolak 403 lebih awal untuk pihak ketiga, sebelum validasi alasan. */
    public function authorize(): bool
    {
        $pesanan = $this->route('pesanan');

        return $pesanan instanceof Pesanan
            && (int) $pesanan->penyedia_id === (int) $this->user()?->id;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
            'alasan.min' => 'Alasan penolakan minimal 10 karakter.',
            'alasan.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
