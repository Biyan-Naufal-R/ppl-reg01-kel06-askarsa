<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLamaranRequest;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Notifications\LamaranDirespon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LamaranController extends Controller
{
    public function create(Request $request, string $slug): View|RedirectResponse
    {
        $lowongan = Lowongan::where('slug', $slug)->firstOrFail();

        if ((int) $lowongan->user_id === (int) $request->user()->id) {
            return redirect()->route('jobs.show', $slug)->withErrors(['lamaran' => 'Anda tidak bisa melamar lowongan milik sendiri.']);
        }

        if (! $lowongan->bisaDilamar()) {
            return redirect()->route('jobs.show', $slug)->withErrors(['lamaran' => 'Lowongan ini tidak sedang menerima lamaran.']);
        }

        $sudahMelamar = Lamaran::where('user_id', $request->user()->id)
            ->where('lowongan_id', $lowongan->id)
            ->exists();

        if ($sudahMelamar) {
            return redirect()->route('jobs.show', $slug)->withErrors(['lamaran' => 'Anda sudah pernah melamar lowongan ini.']);
        }

        return view('lamaran.create', [
            'lowongan' => $lowongan,
            'job' => $lowongan->dataKartu(),
        ]);
    }

    public function store(StoreLamaranRequest $request, string $slug): RedirectResponse
    {
        $lowongan = Lowongan::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        if ((int) $lowongan->user_id === (int) $user->id) {
            return back()->withErrors(['lamaran' => 'Anda tidak bisa melamar lowongan milik sendiri.']);
        }

        if (! $lowongan->bisaDilamar()) {
            return back()->withErrors(['lamaran' => 'Lowongan ini tidak sedang menerima lamaran.']);
        }

        $sudahMelamar = Lamaran::where('user_id', $user->id)
            ->where('lowongan_id', $lowongan->id)
            ->exists();

        if ($sudahMelamar) {
            return redirect()->route('jobs.show', $slug)->withErrors(['lamaran' => 'Anda sudah pernah melamar lowongan ini.']);
        }

        $file = $request->file('cv');
        $nama = $file?->getClientOriginalName() ?: 'cv.pdf';
        $path = $file->store('cv', 'local');

        Lamaran::create([
            'user_id' => $user->id,
            'lowongan_id' => $lowongan->id,
            'nama_pelamar' => $user->name,
            'email_pelamar' => $user->email,
            'cv_path' => $path,
            'cv_nama' => $nama,
            'pesan' => $request->input('pesan'),
            'status' => Lamaran::STATUS_MENUNGGU,
        ]);

        return redirect()->route('dashboard.applications')->with('sukses', 'Lamaran berhasil dikirim.');
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $lamarans = Lamaran::query()
            ->with('lowongan')
            ->where('user_id', $request->user()->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nama_pelamar', 'like', '%'.$q.'%')
                        ->orWhereHas('lowongan', fn ($l) => $l->where('judul', 'like', '%'.$q.'%')->orWhere('perusahaan', 'like', '%'.$q.'%'));
                });
            })
            ->when(in_array($status, array_keys(Lamaran::labelStatus()), true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('dashboard.applications', [
            'lamarans' => $lamarans,
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function masuk(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $lamarans = Lamaran::query()
            ->with(['lowongan', 'user'])
            ->whereHas('lowongan', fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nama_pelamar', 'like', '%'.$q.'%')
                        ->orWhereHas('lowongan', fn ($l) => $l->where('judul', 'like', '%'.$q.'%'));
                });
            })
            ->when(in_array($status, array_keys(Lamaran::labelStatus()), true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('dashboard.candidates', [
            'lamarans' => $lamarans,
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function downloadCv(Request $request, Lamaran $lamaran): StreamedResponse|BinaryFileResponse
    {
        $this->authorize('downloadCv', $lamaran);

        abort_unless(Storage::disk('local')->exists($lamaran->cv_path), 404);

        return Storage::disk('local')->download($lamaran->cv_path, $lamaran->cv_nama);
    }

    public function proses(Request $request, Lamaran $lamaran): RedirectResponse
    {
        $this->authorize('proses', $lamaran);
        $this->ubahStatus($lamaran, Lamaran::STATUS_DIPROSES);

        return back()->with('sukses', 'Lamaran ditandai sedang diproses.');
    }

    public function terima(Request $request, Lamaran $lamaran): RedirectResponse
    {
        $this->authorize('terima', $lamaran);
        $this->ubahStatus($lamaran, Lamaran::STATUS_DITERIMA);

        return back()->with('sukses', 'Lamaran telah diterima.');
    }

    public function tolak(Request $request, Lamaran $lamaran): RedirectResponse
    {
        $this->authorize('tolak', $lamaran);
        $this->ubahStatus($lamaran, Lamaran::STATUS_DITOLAK);

        return back()->with('sukses', 'Lamaran telah ditolak.');
    }

    private function ubahStatus(Lamaran $lamaran, string $status): void
    {
        $lamaran->update([
            'status' => $status,
            'direspon_at' => now(),
        ]);

        $lamaran->user?->notify(new LamaranDirespon($lamaran->fresh(['lowongan'])));
    }
}
