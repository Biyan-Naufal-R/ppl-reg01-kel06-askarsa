<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLowonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('bayaran')) {
            $this->merge([
                'bayaran' => preg_replace('/\D/', '', (string) $this->input('bayaran')),
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:150'],
            'perusahaan' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', Rule::in(array_keys(config('lowongan.kategori')))],
            'jenis' => ['required', 'string', Rule::in(array_keys(config('lowongan.jenis')))],
            'metode' => ['required', 'string', Rule::in(array_keys(config('lowongan.metode')))],
            'kecamatan' => ['required', 'string', Rule::in(config('domisili.kecamatan'))],
            'deskripsi' => ['required', 'string', 'min:20', 'max:2000'],
            'persyaratan' => ['required', 'string', 'min:10', 'max:2000'],
            'durasi' => ['required', 'string', 'max:80'],
            'batas_lamaran' => ['required', 'date', 'after_or_equal:today'],
            'bayaran' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul lowongan wajib diisi.',
            'perusahaan.required' => 'Nama pemberi kerja wajib diisi.',
            'kategori.required' => 'Jenis lowongan wajib dipilih.',
            'jenis.required' => 'Kategori pekerjaan wajib dipilih.',
            'metode.required' => 'Metode kerja wajib dipilih.',
            'kecamatan.required' => 'Kecamatan wajib dipilih.',
            'deskripsi.required' => 'Deskripsi pekerjaan wajib diisi.',
            'deskripsi.min' => 'Deskripsi pekerjaan minimal 20 karakter.',
            'persyaratan.required' => 'Persyaratan pelamar wajib diisi.',
            'persyaratan.min' => 'Persyaratan pelamar minimal 10 karakter.',
            'durasi.required' => 'Durasi proyek wajib diisi.',
            'batas_lamaran.required' => 'Batas lamaran wajib diisi.',
            'batas_lamaran.after_or_equal' => 'Batas lamaran tidak boleh sebelum hari ini.',
            'bayaran.required' => 'Bayaran proyek wajib diisi.',
            'bayaran.numeric' => 'Bayaran proyek harus berupa angka.',
        ];
    }
}
