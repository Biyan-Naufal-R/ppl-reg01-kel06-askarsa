<?php

namespace App\Http\Controllers;

use App\Http\Requests\TolakPesananRequest;
use App\Models\Pesanan;
use App\Models\ProfilJasa;
use App\Models\User;
use App\Notifications\PesananDirespon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PesananController extends Controller
{
    /** Form pemesanan jasa dari POV pemesan. */
    public function create(Request $request, string $slug): View|RedirectResponse
    {
        $service = $this->resolveServiceBySlug($slug);
        abort_unless($service, 404);

        $pemesan = $request->user();
        $penyedia = User::with('profilJasa')->findOrFail($service['penyedia_id']);
        $profil = $penyedia->profilJasa;

        if ((int) $penyedia->id === (int) $pemesan->id) {
            return redirect()->route('services.show', $slug)->withErrors(['jasa' => 'Tidak bisa memesan jasa milik sendiri.']);
        }
        if ($pemesan->punyaJasaAktif()) {
            return redirect()->route('services.show', $slug)->withErrors(['jasa' => 'Nonaktifkan profil jasa Anda terlebih dahulu untuk memesan jasa orang lain.']);
        }
        abort_unless($profil && $profil->aktif && $profil->tersedia, 404, 'Profil jasa ini tidak tersedia.');

        $kategori = collect($profil->kategori ?? [])->values()->all();
        $wajibTitikAntar = count(array_intersect($kategori, ['antar-jemput', 'antar-barang', 'titip-beli', 'temani'])) > 0;

        return view('pesanan.create', [
            'service' => $service,
            'penyedia' => $penyedia,
            'profil' => $profil,
            'jenisBantuan' => collect($kategori)->map(fn (string $k) => config('jasa.kategori.'.$k, $k))->values()->all(),
            'wajibTitikAntar' => $wajibTitikAntar,
        ]);
    }

    /** Buat pesanan baru, dengan pembatasan peran penyedia/pelanggan. */
    public function store(Request $request): RedirectResponse
    {
        $penyediaId = $request->integer('penyedia_id');
        $profilCek = User::with('profilJasa')->find($penyediaId)?->profilJasa;
        $kategoriCek = collect($profilCek?->kategori ?? [])->all();
        $butuhTitikAntar = count(array_intersect($kategoriCek, ['antar-jemput', 'antar-barang', 'titip-beli', 'temani'])) > 0;

        $data = $request->validate([
            'penyedia_id' => ['required', 'integer', 'exists:users,id'],
            'slug' => ['required', 'string'],
            'nama_pemesan' => ['required', 'string', 'max:255'],
            'nomor_telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'email_pemesan' => ['required', 'email', 'max:255'],
            'domisili_pemesan' => ['required', 'string', 'max:255', Rule::in(config('domisili.kecamatan'))],
            'titik_ambil' => ['required', 'string', 'max:1000'],
            'titik_antar' => [$butuhTitikAntar ? 'required' : 'nullable', 'string', 'max:1000'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'perkiraan_durasi' => ['required', 'string', Rule::in(['<1 jam', '1-2 jam', '2-4 jam', 'seharian', '>1 hari'])],
            'catatan_tambahan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nomor_telepon.regex' => 'Format nomor telepon tidak valid.',
            'titik_antar.required' => 'Titik antar/tujuan wajib diisi untuk layanan antar, titip beli, atau temani.',
        ]);

        $pemesan = $request->user();
        $penyedia = User::with('profilJasa')->findOrFail($data['penyedia_id']);
        $profil = $penyedia->profilJasa;

        if ((int) $penyedia->id === (int) $pemesan->id) {
            return back()->withErrors(['jasa' => 'Tidak bisa memesan jasa milik sendiri.'])->withInput();
        }

        abort_unless($profil && $profil->aktif && $profil->tersedia, 404, 'Profil jasa ini tidak tersedia.');

        if ($pemesan->punyaJasaAktif()) {
            return back()->withErrors(['jasa' => 'Nonaktifkan profil jasa Anda terlebih dahulu untuk memesan jasa orang lain.'])->withInput();
        }

        // Jenis bantuan otomatis dari sistem, bukan dari input pemesan.
        $jenisBantuan = collect($profil->kategori ?? [])->values()->all();

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pemesan->id,
            'penyedia_id' => $penyedia->id,
            'nama_pemesan' => $data['nama_pemesan'],
            'email_pemesan' => $data['email_pemesan'],
            'nomor_telepon' => $data['nomor_telepon'],
            'domisili_pemesan' => $data['domisili_pemesan'],
            'alamat_lengkap' => $data['titik_ambil'],
            'titik_ambil' => $data['titik_ambil'],
            'titik_antar' => $data['titik_antar'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'perkiraan_durasi' => $data['perkiraan_durasi'],
            'jenis_bantuan' => $jenisBantuan,
            'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            'judul' => 'Pesan jasa: '.$profil->nama_profil,
            'deskripsi' => $data['catatan_tambahan'] ?? null,
            'harga' => $profil->tarif,
            'status' => Pesanan::STATUS_MENUNGGU,
        ]);

        return redirect()->route('pesanan.show', $pesanan)
            ->with('sukses', 'Pesanan berhasil dibuat.');
    }

    private function resolveServiceBySlug(string $slug): ?array
    {
        $profilJasa = ProfilJasa::with('user')->where('aktif', true)->get();
        $slugCounts = [];
        foreach ($profilJasa as $profil) {
            $base = Str::slug($profil->user?->name ?? 'penyedia');
            $slugCounts[$base] = ($slugCounts[$base] ?? 0) + 1;
        }

        foreach ($profilJasa as $profil) {
            $user = $profil->user;
            $base = Str::slug($user?->name ?? 'penyedia');
            $candidate = ($slugCounts[$base] ?? 0) > 1 ? $base.'-'.$profil->user_id : $base;
            if ($candidate !== $slug) continue;

            return [
                'slug' => $slug,
                'name' => $profil->nama_profil,
                'district' => $user->domisili ?? '-',
                'category' => collect($profil->kategori ?? [])->map(fn (string $nilai) => config('jasa.kategori.'.$nilai, $nilai))->implode(', '),
                'description' => $profil->deskripsi ?? '',
                'price' => 'Rp'.number_format($profil->tarif, 0, ',', '.'),
                'penyedia_id' => $profil->user_id,
                'tersedia' => (bool) $profil->tersedia,
            ];
        }

        return null;
    }

    /** Riwayat pesanan lama dialihkan ke tampilan POV pemesan. */
    public function riwayat(Request $request): RedirectResponse
    {
        return redirect()->route('dashboard.pesanan.riwayat', $request->only('status'));
    }

    /** Detail satu pesanan, hanya untuk pelanggan atau penyedia yang terlibat. */
    public function show(Request $request, Pesanan $pesanan): View
    {
        $this->authorize('view', $pesanan);

        $pesanan->load(['pelanggan', 'penyedia']);
        $userId = (int) $request->user()->id;

        // Membuka detail menandai notifikasi terkait sebagai sudah dibaca.
        foreach ($request->user()->unreadNotifications as $notifikasi) {
            if (($notifikasi->data['pesanan_id'] ?? null) === $pesanan->id) {
                $notifikasi->markAsRead();
            }
        }

        return view('pesanan.show', [
            'pesanan' => $pesanan,
            'bisaSelesaikan' => (int) $pesanan->penyedia_id === $userId
                && $pesanan->bisaDiselesaikan(),
            'bisaTerima' => (int) $pesanan->penyedia_id === $userId
                && $pesanan->bisaDiterima(),
            'bisaTolak' => (int) $pesanan->penyedia_id === $userId
                && $pesanan->bisaDitolak(),
            'bisaBatalkan' => (int) $pesanan->pelanggan_id === $userId
                && $pesanan->bisaDibatalkan(),
        ]);
    }

    /** Penyedia menerima pesanan menunggu, pesanan diproses dan profil jadi tidak tersedia. */
    public function terima(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorize('terima', $pesanan);

        DB::transaction(function () use ($pesanan, $request) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (! $terkunci->bisaDiterima()) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pesanan berstatus "menunggu" yang bisa diterima.',
                ]);
            }

            $penyedia = $request->user();
            // Kunci baris profil agar dua transaksi tidak sama-sama membaca
            // "tersedia" sebelum salah satu menulisnya menjadi false.
            $profil = $penyedia->profilJasa()->lockForUpdate()->first();

            if (! $profil || ! $profil->aktif) {
                throw ValidationException::withMessages([
                    'jasa' => 'Aktifkan profil jasa Anda terlebih dahulu untuk menerima pesanan.',
                ]);
            }

            $sibuk = Pesanan::where('penyedia_id', $penyedia->id)
                ->where('status', Pesanan::STATUS_DIPROSES)
                ->exists();
            if ($sibuk) {
                throw ValidationException::withMessages([
                    'status' => 'Selesaikan pesanan yang sedang diproses terlebih dahulu.',
                ]);
            }

            if (! $profil->tersedia) {
                throw ValidationException::withMessages([
                    'jasa' => 'Profil jasa Anda sedang tidak tersedia.',
                ]);
            }

            $terkunci->update([
                'status' => Pesanan::STATUS_DIPROSES,
                'direspon_pada' => now(),
            ]);
            $profil->update(['tersedia' => false]);

            $terkunci->pelanggan->notify(new PesananDirespon($terkunci, Pesanan::STATUS_DIPROSES));
        });

        return back()->with('sukses', 'Pesanan diterima dan sedang diproses. Profil jasa Anda sementara tidak tersedia.');
    }

    /** Penyedia menolak pesanan menunggu beserta alasannya. */
    public function tolak(TolakPesananRequest $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorize('tolak', $pesanan);

        DB::transaction(function () use ($request, $pesanan) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (! $terkunci->bisaDitolak()) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pesanan berstatus "menunggu" yang bisa ditolak.',
                ]);
            }

            $terkunci->update([
                'status' => Pesanan::STATUS_DITOLAK,
                'alasan_penolakan' => $request->input('alasan'),
                'catatan_penyelesaian' => null,
                'direspon_pada' => now(),
            ]);

            $terkunci->pelanggan->notify(new PesananDirespon($terkunci, Pesanan::STATUS_DITOLAK));
        });

        return back()->with('sukses', 'Pesanan ditolak.');
    }

    /** Pelanggan membatalkan pesanan yang belum diterima penyedia. */
    public function batalkan(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorize('batalkan', $pesanan);

        DB::transaction(function () use ($pesanan) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (! $terkunci->bisaDibatalkan()) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan yang sudah diterima/diproses tidak bisa dibatalkan.',
                ]);
            }

            $terkunci->update([
                'status' => Pesanan::STATUS_DIBATALKAN,
                'catatan_penyelesaian' => 'Dibatalkan oleh pelanggan.',
            ]);
        });

        return back()->with('sukses', 'Pesanan dibatalkan.');
    }

    /** Penyedia menandai pesanan berstatus "diproses" menjadi "selesai". */
    public function selesaikan(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorize('selesaikan', $pesanan);

        $data = $request->validate([
            'catatan_penyelesaian' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $pesanan, $request) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (! $terkunci->bisaDiselesaikan()) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pesanan berstatus "diproses" yang bisa diselesaikan.',
                ]);
            }

            $terkunci->update([
                'status' => Pesanan::STATUS_SELESAI,
                'catatan_penyelesaian' => $data['catatan_penyelesaian'] ?? null,
                'selesai_pada' => now(),
            ]);

            $masihAdaProses = Pesanan::where('penyedia_id', $request->user()->id)
                ->where('status', Pesanan::STATUS_DIPROSES)
                ->exists();
            if (! $masihAdaProses) {
                // Kunci profil sebelum menulis ulang status ketersediaannya.
                // Profil yang dinonaktifkan tidak boleh otomatis tersedia lagi.
                $profil = $request->user()->profilJasa()->lockForUpdate()->first();
                if ($profil && $profil->aktif) {
                    $profil->update(['tersedia' => true]);
                }
            }
        });

        return redirect()
            ->route('dashboard.permintaan.riwayat')
            ->with('sukses', 'Pesanan berhasil ditandai selesai.');
    }

    /** POV Pemesan: pesanan jasa dalam proses. */
    public function pesananProses(Request $request): View
    {
        $pesanan = Pesanan::where('pelanggan_id', $request->user()->id)
            ->whereIn('status', [Pesanan::STATUS_MENUNGGU, Pesanan::STATUS_DIPROSES])
            ->with(['penyedia.profilJasa'])
            ->latest('updated_at')
            ->paginate(10);

        return view('dashboard.pesanan-proses', compact('pesanan'));
    }

    /** POV Pemesan: riwayat pesanan jasa. */
    public function pesananRiwayat(Request $request): View
    {
        $pesanan = Pesanan::where('pelanggan_id', $request->user()->id)
            ->riwayat()
            ->with(['penyedia.profilJasa'])
            ->latest('updated_at')
            ->paginate(10);

        return view('dashboard.pesanan-riwayat', compact('pesanan'));
    }

    /** POV Penyedia: permintaan jasa masuk dalam proses. */
    public function permintaanProses(Request $request): View
    {
        $pesanan = Pesanan::where('penyedia_id', $request->user()->id)
            ->whereIn('status', [Pesanan::STATUS_MENUNGGU, Pesanan::STATUS_DIPROSES])
            ->with(['pelanggan'])
            ->latest('updated_at')
            ->paginate(10);

        return view('dashboard.permintaan-proses', compact('pesanan'));
    }

    /** POV Penyedia: riwayat permintaan jasa masuk. */
    public function permintaanRiwayat(Request $request): View
    {
        $pesanan = Pesanan::where('penyedia_id', $request->user()->id)
            ->riwayat()
            ->with(['pelanggan'])
            ->latest('updated_at')
            ->paginate(10);

        return view('dashboard.permintaan-riwayat', compact('pesanan'));
    }
}
