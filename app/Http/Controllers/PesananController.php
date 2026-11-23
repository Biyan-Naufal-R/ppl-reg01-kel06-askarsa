<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PesananController extends Controller
{
    /** Buat pesanan baru, dengan pembatasan peran penyedia/pelanggan. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'penyedia_id' => ['required', 'integer', 'exists:users,id'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ]);

        $pemesan = $request->user();
        $penyedia = User::findOrFail($data['penyedia_id']);
        $profil = $penyedia->profilJasa;

        if ((int) $penyedia->id === (int) $pemesan->id) {
            return back()->withErrors(['jasa' => 'Tidak bisa memesan jasa milik sendiri.']);
        }

        abort_unless($profil && $profil->aktif, 404, 'Profil jasa ini tidak tersedia.');

        if ($pemesan->punyaJasaAktif()) {
            return back()->withErrors(['jasa' => 'Nonaktifkan profil jasa Anda terlebih dahulu untuk memesan jasa orang lain.']);
        }

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pemesan->id,
            'penyedia_id' => $penyedia->id,
            'judul' => 'Pesan jasa: '.$profil->nama_profil,
            'deskripsi' => $data['deskripsi'] ?? null,
            'harga' => $profil->tarif,
            'status' => Pesanan::STATUS_MENUNGGU,
        ]);

        return redirect()->route('pesanan.show', $pesanan)
            ->with('sukses', 'Pesanan berhasil dibuat.');
    }

    /** Riwayat pesanan (selesai/dibatalkan) milik user yang login, dengan filter status. */
    public function riwayat(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', Rule::in([Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])],
        ]);

        $pesanan = Pesanan::milik($request->user())
            ->riwayat()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->with(['pelanggan', 'penyedia'])
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('pesanan.riwayat', [
            'pesanan' => $pesanan,
            'statusAktif' => $request->input('status'),
        ]);
    }

    /** Detail satu pesanan, hanya untuk pelanggan atau penyedia yang terlibat. */
    public function show(Request $request, Pesanan $pesanan): View
    {
        abort_unless($pesanan->dimilikiOleh($request->user()), 403);

        $pesanan->load(['pelanggan', 'penyedia']);

        return view('pesanan.show', [
            'pesanan' => $pesanan,
            'bisaSelesaikan' => (int) $pesanan->penyedia_id === $request->user()->id
                && $pesanan->bisaDiselesaikan(),
        ]);
    }

    /** Penyedia menandai pesanan berstatus "diproses" menjadi "selesai". */
    public function selesaikan(Request $request, Pesanan $pesanan): RedirectResponse
    {
        abort_unless((int) $pesanan->penyedia_id === $request->user()->id, 403);

        if (! $pesanan->bisaDiselesaikan()) {
            return back()->withErrors([
                'status' => 'Hanya pesanan berstatus "diproses" yang bisa diselesaikan.',
            ]);
        }

        $data = $request->validate([
            'catatan_penyelesaian' => ['nullable', 'string', 'max:500'],
        ]);

        $pesanan->update([
            'status' => Pesanan::STATUS_SELESAI,
            'catatan_penyelesaian' => $data['catatan_penyelesaian'] ?? null,
            'selesai_pada' => now(),
        ]);

        return redirect()
            ->route('pesanan.show', $pesanan)
            ->with('sukses', 'Pesanan berhasil ditandai selesai.');
    }
}