<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLamaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'pesan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cv.required' => 'CV / portofolio wajib diunggah.',
            'cv.mimes' => 'Berkas harus berformat PDF.',
            'cv.max' => 'Ukuran berkas melebihi 5 MB.',
        ];
    }
}
