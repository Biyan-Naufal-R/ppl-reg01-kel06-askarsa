<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaporanRequest;
use App\Models\Laporan;
use App\Models\Lowongan;
use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /** Form laporan dari halaman detail profil jasa. */
    public function createProfilJasa(Request $request, ProfilJasa $profilJasa): View|RedirectResponse
    {
        return $this->form($request, 'profil_jasa', $profilJasa);
    }

    /** Form laporan dari halaman detail lowongan. */
    public function createLowongan(Request $request, Lowongan $lowongan): View|RedirectResponse
    {
        return $this->form($request, 'lowongan', $lowongan);
    }

    private function form(Request $request, string $jenis, ProfilJasa|Lowongan $objek): View|RedirectResponse
    {
        if ($this->objekMilikUser($objek, $request->user())) {
            return back()->withErrors(['objek' => 'Tidak bisa melapor objek milik sendiri.']);
        }

        return view('laporan.create', [
            'jenisObjek' => $jenis,
            'objek' => $objek,
            'labelObjek' => $jenis === 'profil_jasa' ? $objek->nama_profil : $objek->judul,
            'labelJenis' => config('laporan.jenis_objek.'.$jenis),
            'kategoriList' => config('laporan.kategori'),
        ]);
    }

    /** Kirim laporan baru dengan status awal menunggu pemeriksaan. */
    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $objek = $this->resolveObjek($data['jenis_objek'], (int) $data['objek_id']);
        abort_unless($objek, 404);

        if ($this->objekMilikUser($objek, $request->user())) {
            throw ValidationException::withMessages([
                'objek' => 'Tidak bisa melapor objek milik sendiri.',
            ]);
        }

        $sudahAda = Laporan::where('pelapor_id', $request->user()->id)
            ->where('objek_type', $objek->getMorphClass())
            ->where('objek_id', $objek->id)
            ->where('status', '!=', Laporan::STATUS_SELESAI)
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'laporan' => 'Laporan untuk objek ini masih menunggu pemeriksaan.',
            ]);
        }

        $buktiPath = null;
        $buktiNama = null;
        if ($request->hasFile('bukti')) {
            $bukti = $request->file('bukti');
            $buktiNama = $bukti->getClientOriginalName();
            $buktiPath = $bukti->store('bukti-laporan', 'private');
        }

        Laporan::create([
            'pelapor_id' => $request->user()->id,
            'objek_type' => $objek->getMorphClass(),
            'objek_id' => $objek->id,
            'kategori' => $data['kategori'],
            'penjelasan' => $data['penjelasan'],
            'bukti_path' => $buktiPath,
            'bukti_nama' => $buktiNama,
            'status' => Laporan::STATUS_MENUNGGU,
        ]);

        return redirect()->route('dashboard.reports')
            ->with('sukses', 'Laporan berhasil dikirim. Status awal: Menunggu Pemeriksaan.');
    }

    /** Laporan Saya: hanya laporan milik user yang login. */
    public function index(Request $request): View
    {
        $laporan = Laporan::where('pelapor_id', $request->user()->id)
            ->with('objek')
            ->latest()
            ->paginate(10);

        return view('dashboard.laporan-saya', compact('laporan'));
    }

    /** Unduh bukti: hanya pelapor yang boleh membuka berkasnya. */
    public function unduhBukti(Request $request, Laporan $laporan)
    {
        $this->authorize('unduh', $laporan);

        abort_unless($laporan->bukti_path, 404, 'Laporan ini tidak memiliki bukti.');

        return Storage::disk('private')->download(
            $laporan->bukti_path,
            $laporan->bukti_nama ?? 'bukti-laporan'
        );
    }

    private function resolveObjek(string $jenis, int $id): ProfilJasa|Lowongan|null
    {
        return match ($jenis) {
            'profil_jasa' => ProfilJasa::find($id),
            'lowongan' => Lowongan::find($id),
            default => null,
        };
    }

    private function objekMilikUser(ProfilJasa|Lowongan $objek, User $user): bool
    {
        return (int) $objek->user_id === (int) $user->id;
    }
}
