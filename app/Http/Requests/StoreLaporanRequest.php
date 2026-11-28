<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'jenis_objek' => ['required', 'string', Rule::in(array_keys(config('laporan.jenis_objek')))],
            'objek_id' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string', Rule::in(array_keys(config('laporan.kategori')))],
            'penjelasan' => ['required', 'string', 'min:10', 'max:1000'],
            'bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'bukti.mimes' => 'Bukti harus berformat JPG, PNG, atau PDF.',
            'bukti.max' => 'Ukuran bukti maksimal 5 MB.',
            'penjelasan.min' => 'Penjelasan minimal 10 karakter.',
        ];
    }
}
