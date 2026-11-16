<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PesananController extends Controller
{
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