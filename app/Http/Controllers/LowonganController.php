<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLowonganRequest;
use App\Models\Lowongan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LowonganController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $lowongans = Lowongan::query()
            ->withCount('lamarans')
            ->where('user_id', $request->user()->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('judul', 'like', '%'.$q.'%')
                        ->orWhere('perusahaan', 'like', '%'.$q.'%');
                });
            })
            ->when(in_array($status, [Lowongan::STATUS_AKTIF, Lowongan::STATUS_DITUTUP, Lowongan::STATUS_NONAKTIF], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('dashboard.posts', [
            'lowongans' => $lowongans,
            'q' => $q,
            'status' => $status,
            'total' => Lowongan::where('user_id', $request->user()->id)->count(),
            'aktif' => Lowongan::where('user_id', $request->user()->id)->where('status', Lowongan::STATUS_AKTIF)->count(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.create-job', ['lowongan' => null]);
    }

    public function store(StoreLowonganRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $gaji = (int) $data['bayaran'];

        $fullTime = $data['kategori'] === 'Full-Time';

        Lowongan::create([
            'user_id' => $request->user()->id,
            'judul' => $data['judul'],
            'perusahaan' => $data['perusahaan'],
            'slug' => Lowongan::buatSlug($data['judul']),
            'kategori' => $data['kategori'],
            'jenis' => $fullTime ? '' : $data['jenis'],
            'metode' => $data['metode'],
            'kecamatan' => $data['kecamatan'],
            'deskripsi' => $data['deskripsi'],
            'persyaratan' => $data['persyaratan'],
            'pendidikan_minimum' => $fullTime ? $data['pendidikan_minimum'] : null,
            'jurusan' => $fullTime ? ($data['jurusan'] ?? null) : null,
            'durasi' => $fullTime ? '' : $data['durasi'],
            'batas_lamaran' => $data['batas_lamaran'],
            'gaji_min' => $gaji,
            'gaji_max' => $gaji,
            'status' => Lowongan::STATUS_AKTIF,
        ]);

        return redirect()->route('dashboard.posts')->with('sukses', 'Lowongan berhasil dipublikasikan.');
    }

    public function edit(Lowongan $lowongan): View
    {
        $this->authorize('update', $lowongan);

        return view('dashboard.create-job', compact('lowongan'));
    }

    public function update(StoreLowonganRequest $request, Lowongan $lowongan): RedirectResponse
    {
        $this->authorize('update', $lowongan);
        $data = $request->validated();
        $gaji = (int) $data['bayaran'];

        $fullTime = $data['kategori'] === 'Full-Time';

        $lowongan->update([
            'judul' => $data['judul'],
            'perusahaan' => $data['perusahaan'],
            'kategori' => $data['kategori'],
            'jenis' => $fullTime ? '' : $data['jenis'],
            'metode' => $data['metode'],
            'kecamatan' => $data['kecamatan'],
            'deskripsi' => $data['deskripsi'],
            'persyaratan' => $data['persyaratan'],
            'pendidikan_minimum' => $fullTime ? $data['pendidikan_minimum'] : null,
            'jurusan' => $fullTime ? ($data['jurusan'] ?? null) : null,
            'durasi' => $fullTime ? '' : $data['durasi'],
            'batas_lamaran' => $data['batas_lamaran'],
            'gaji_min' => $gaji,
            'gaji_max' => $gaji,
        ]);

        return redirect()->route('dashboard.posts')->with('sukses', 'Lowongan berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Lowongan $lowongan): RedirectResponse
    {
        $this->authorize('toggleStatus', $lowongan);

        $lowongan->update([
            'status' => $lowongan->status === Lowongan::STATUS_NONAKTIF ? Lowongan::STATUS_AKTIF : Lowongan::STATUS_NONAKTIF,
        ]);

        return back()->with('sukses', 'Status lowongan diperbarui.');
    }

    public function tutup(Request $request, Lowongan $lowongan): RedirectResponse
    {
        $this->authorize('tutup', $lowongan);

        $lowongan->update([
            'status' => $lowongan->status === Lowongan::STATUS_DITUTUP ? Lowongan::STATUS_AKTIF : Lowongan::STATUS_DITUTUP,
        ]);

        return back()->with('sukses', 'Status lamaran lowongan diperbarui.');
    }
}
