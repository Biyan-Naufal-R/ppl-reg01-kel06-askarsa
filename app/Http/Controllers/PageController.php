<?php
namespace App\Http\Controllers;

use App\Models\Lowongan;
use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Data kartu jasa. Secara default memasukkan seluruh profil aktif, termasuk
     * yang sedang tidak tersedia, agar tetap muncul di katalog dengan label.
     * Beranda memakai $hanyaTersedia = true sehingga hanya menampilkan yang siap menerima pesanan.
     */
    private function serviceData(bool $hanyaTersedia = false, array $filters = []): array
    {
        $query = ProfilJasa::tampilDiKatalog()
            ->with('user')
            ->orderByDesc('tersedia')
            ->latest();

        // Slug dihitung dari semua profil aktif agar tidak berubah saat status ketersediaan berubah.
        $semuaProfil = $query->get();
        $slugCounts = [];
        foreach ($semuaProfil as $profil) {
            $base = Str::slug($profil->user?->name ?? 'penyedia');
            $slugCounts[$base] = ($slugCounts[$base] ?? 0) + 1;
        }

        $profilJasa = $hanyaTersedia
            ? (clone $query)->tersedia()->get()
            : $semuaProfil;

        return $profilJasa->map(function (ProfilJasa $profil) use ($slugCounts) {
            $user = $profil->user;
            $base = Str::slug($user?->name ?? 'penyedia');
            $slug = ($slugCounts[$base] ?? 0) > 1 ? $base.'-'.$profil->user_id : $base;

            $initials = collect(preg_split('/\s+/', trim($user->name ?? 'Penyedia')))
                ->map(fn (string $kata) => Str::upper(Str::substr($kata, 0, 1)))
                ->take(2)
                ->implode('');

            $kategori = collect($profil->kategori ?? [])
                ->map(fn (string $nilai) => config('jasa.kategori.'.$nilai, $nilai))
                ->implode(', ');

            return [
                'slug' => $slug,
                'initials' => $initials,
                'name' => $profil->nama_profil,
                'district' => $user->domisili ?? '-',
                'category' => $kategori,
                'kategori' => array_values($profil->kategori ?? []),
                'description' => $profil->deskripsi ?? '',
                'price' => 'Rp'.number_format($profil->tarif, 0, ',', '.'),
                'penyedia_id' => $profil->user_id,
                'penyedia_email' => $user->email,
                'tersedia' => (bool) $profil->tersedia,
                'status_label' => $profil->tersedia ? 'Tersedia' : 'Tidak tersedia',
                'tarif' => (int) $profil->tarif,
                'domisili' => (string) ($user->domisili ?? ''),
                'penyedia_nama' => (string) ($user->name ?? ''),
            ];
        })->values()->all();
    }

    /** Saring kartu katalog memakai query yang sudah divalidasi. */
    private function filterServices(array $services, array $filters): array
    {
        return collect($services)
            ->filter(function (array $service) use ($filters): bool {
                if (! empty($filters['q'])) {
                    $haystack = mb_strtolower(implode(' ', [
                        $service['name'],
                        $service['description'],
                        $service['penyedia_nama'],
                        $service['category'],
                    ]));

                    if (! str_contains($haystack, mb_strtolower($filters['q']))) {
                        return false;
                    }
                }

                if (! empty($filters['kecamatan']) && $service['domisili'] !== $filters['kecamatan']) {
                    return false;
                }

                if (! empty($filters['kategori']) && ! in_array($filters['kategori'], $service['kategori'], true)) {
                    return false;
                }

                if (isset($filters['tarif_min']) && $service['tarif'] < $filters['tarif_min']) {
                    return false;
                }

                if (isset($filters['tarif_max']) && $service['tarif'] > $filters['tarif_max']) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

    /**
     * Data lowongan contoh. Dua entri Full-Time sengaja tidak diberi field filter
     * supaya halaman /full-time tetap persis seperti sebelumnya.
     */
    private function jobsData(): array
    {
        $dummy = array_merge([
            [
                'slug' => 'desainer-konten-media-sosial',
                'company' => 'Studio Karsa Banyumas',
                'title' => 'Desainer Konten Media Sosial',
                'type' => 'Freelance',
                'mode' => 'Remote',
                'location' => 'Purwokerto Utara, Banyumas',
                'salary' => 'Rp1.500.000 / proyek',
                'kecamatan' => 'Purwokerto Utara',
                'jenis' => 'sosmed',
                'metode' => 'remote',
                'gaji_min' => 1500000,
                'gaji_max' => 1500000,
            ],
            ['slug'=>'staf-administrasi','company'=>'CV Satria Niaga','title'=>'Staf Administrasi','type'=>'Full-Time','mode'=>'On-site','location'=>'Purwokerto Selatan, Banyumas','salary'=>'Rp2.500.000–3.000.000 / bulan'],
            [
                'slug' => 'fotografer-produk-umkm',
                'company' => 'Kedai Kopi Lereng',
                'title' => 'Fotografer Produk UMKM',
                'type' => 'Freelance',
                'mode' => 'On-site',
                'location' => 'Baturaden, Banyumas',
                'salary' => 'Rp800.000 / proyek',
                'kecamatan' => 'Baturraden',
                'jenis' => 'seni',
                'metode' => 'onsite',
                'gaji_min' => 800000,
                'gaji_max' => 800000,
            ],
            ['slug'=>'barista','company'=>'Kedai Kopi Lereng','title'=>'Barista','type'=>'Full-Time','mode'=>'On-site','location'=>'Baturaden, Banyumas','salary'=>'Rp2.300.000–2.800.000 / bulan'],
        ], $this->contohLowonganPartTime());

        $db = Lowongan::query()
            ->tampilDiKatalog()
            ->latest()
            ->get()
            ->map(fn (Lowongan $l) => $l->dataKartu())
            ->all();

        return array_merge($db, $dummy);
    }

    /**
     * Contoh lowongan Part Time untuk mengisi seluruh jenis pekerjaan.
     * Method ini bisa dihapus begitu saja saat data asli sudah tersedia.
     */
    private function contohLowonganPartTime(): array
    {
        $contoh = [
            ['event', 'Staf Event Part Time', 'onsite', 'Purwokerto Selatan', 750000, 1000000, 'Banyumas Event Organizer'],
            ['barista', 'Barista Part Time', 'onsite', 'Baturraden', 600000, 900000, 'Kedai Kopi Lereng'],
            ['desain', 'Desainer Grafis Part Time', 'remote', 'Sokaraja', 500000, 800000, 'Studio Karsa Banyumas'],
            ['it', 'Teknisi IT Part Time', 'onsite', 'Purwokerto Utara', 1000000, 1500000, 'CV Solusi Digital'],
            ['sosial', 'Relawan Sosial Part Time', 'onsite', 'Kalibagor', 300000, 500000, 'Yayasan Peduli Banyumas'],
            ['sosmed', 'Admin Media Sosial Part Time', 'remote', 'Purwokerto Timur', 700000, 1000000, 'Studio Karsa Banyumas'],
            ['admin', 'Staf Administrasi Part Time', 'remote', 'Purwokerto Barat', 650000, 950000, 'CV Satria Niaga'],
            ['hospitality', 'Resepsionis Part Time', 'onsite', 'Banyumas', 600000, 850000, 'Hotel Lereng'],
            ['pendidikan', 'Tutor Pendidikan Part Time', 'remote', 'Purwokerto Selatan', 800000, 1200000, 'Lembaga Belajar Banyumas'],
            ['lainnya', 'Bantuan Umum Part Time', 'onsite', 'Ajibarang', 400000, 600000, 'Jasa Bantu Banyumas'],
        ];

        return collect($contoh)->map(function (array $item): array {
            [$jenis, $judul, $metode, $kecamatan, $gajiMin, $gajiMax, $perusahaan] = $item;
            $rentang = number_format($gajiMin, 0, ',', '.');
            if ($gajiMax > $gajiMin) {
                $rentang .= '–'.number_format($gajiMax, 0, ',', '.');
            }

            return [
                'slug' => Str::slug($judul),
                'company' => $perusahaan,
                'title' => $judul,
                'type' => 'Part Time',
                'mode' => $metode === 'remote' ? 'Remote' : 'On-site',
                'location' => $kecamatan.', Banyumas',
                'salary' => 'Rp'.$rentang.' / proyek',
                'kecamatan' => $kecamatan,
                'jenis' => $jenis,
                'metode' => $metode,
                'gaji_min' => $gajiMin,
                'gaji_max' => $gajiMax,
            ];
        })->all();
    }

    /** Saring lowongan freelance/part time memakai query yang sudah divalidasi. */
    private function filterJobs($jobs, array $filters)
    {
        return $jobs->filter(function (array $job) use ($filters): bool {
            if (! empty($filters['q'])) {
                $haystack = mb_strtolower(implode(' ', [
                    $job['title'],
                    $job['company'],
                    $job['location'],
                    config('lowongan.jenis.'.($job['jenis'] ?? '').'.label', ''),
                ]));

                if (! str_contains($haystack, mb_strtolower($filters['q']))) {
                    return false;
                }
            }

            if (! empty($filters['kecamatan']) && ($job['kecamatan'] ?? '') !== $filters['kecamatan']) {
                return false;
            }

            if (! empty($filters['kategori']) && $job['type'] !== $filters['kategori']) {
                return false;
            }

            if (! empty($filters['metode']) && ($job['metode'] ?? '') !== $filters['metode']) {
                return false;
            }

            if (! empty($filters['jenis']) && ($job['jenis'] ?? '') !== $filters['jenis']) {
                return false;
            }

            // Bayaran dianggap cocok kalau rentang lowongan beririsan dengan rentang filter.
            if (isset($filters['gaji_min']) && ($job['gaji_max'] ?? 0) < $filters['gaji_min']) {
                return false;
            }

            if (isset($filters['gaji_max']) && ($job['gaji_min'] ?? 0) > $filters['gaji_max']) {
                return false;
            }

            return true;
        })->values();
    }
    public function home(){return view('pages.home',['services'=>$this->serviceData(true),'jobs'=>$this->jobsData()]);}
    public function services(Request $request)
    {
        $filters = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
            'kecamatan' => ['sometimes', 'nullable', 'string', Rule::in(config('domisili.kecamatan'))],
            'kategori' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(config('jasa.kategori')))],
            'tarif_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'tarif_max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        $filters = array_filter($filters, fn ($nilai) => $nilai !== null && $nilai !== '');

        foreach (['tarif_min', 'tarif_max'] as $kunci) {
            if (array_key_exists($kunci, $filters)) {
                $filters[$kunci] = (float) $filters[$kunci];
            }
        }

        if (isset($filters['tarif_min'], $filters['tarif_max']) && $filters['tarif_min'] > $filters['tarif_max']) {
            [$filters['tarif_min'], $filters['tarif_max']] = [$filters['tarif_max'], $filters['tarif_min']];
        }

        return view('pages.services', [
            'services' => $this->filterServices($this->serviceData(), $filters),
            'filters' => $filters,
            'kecamatanList' => config('domisili.kecamatan'),
            'kategoriList' => config('jasa.kategori'),
        ]);
    }
    public function service($slug){
        $service=collect($this->serviceData())->firstWhere('slug',$slug);

        if (!$service) {
            $profil = ProfilJasa::tampilDiKatalog()->with('user')->get()->first(function (ProfilJasa $profil) use ($slug) {
                $base = Str::slug($profil->user?->name ?? 'penyedia');
                $sameBaseCount = ProfilJasa::tampilDiKatalog()->whereHas('user', fn ($q) => $q->where('name', $profil->user?->name))->count();
                $candidate = $sameBaseCount > 1 ? $base.'-'.$profil->user_id : $base;
                return $candidate === $slug;
            });
            abort_unless($profil, 404);
            $user = $profil->user;
            $service = [
                'slug' => $slug,
                'initials' => collect(preg_split('/\s+/', trim($user->name ?? 'Penyedia')))->map(fn (string $kata) => Str::upper(Str::substr($kata, 0, 1)))->take(2)->implode(''),
                'name' => $profil->nama_profil,
                'district' => $user->domisili ?? '-',
                'category' => collect($profil->kategori ?? [])->map(fn (string $nilai) => config('jasa.kategori.'.$nilai, $nilai))->implode(', '),
                'kategori' => array_values($profil->kategori ?? []),
                'description' => $profil->deskripsi ?? '',
                'price' => 'Rp'.number_format($profil->tarif, 0, ',', '.'),
                'penyedia_id' => $profil->user_id,
                'penyedia_email' => $user->email,
                'tersedia' => (bool) $profil->tersedia,
                'status_label' => $profil->tersedia ? 'Tersedia' : 'Tidak tersedia',
            ];
        }

        $penyedia=User::find($service['penyedia_id']);
        return view('pages.service-detail',compact('service','penyedia'));
    }
    public function jobs(Request $request, $type = 'all')
    {
        $jobs = collect($this->jobsData());

        if ($type !== 'freelance') {
            // Halaman full-time sengaja memakai logika lama tanpa filter.
            $jobs = $jobs->when($type !== 'all', fn ($q) => $q->where('type', 'Full-Time'))->values()->all();

            return view('pages.jobs', ['jobs' => $jobs, 'type' => $type]);
        }

        $filters = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
            'kecamatan' => ['sometimes', 'nullable', 'string', Rule::in(config('domisili.kecamatan'))],
            'kategori' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(config('lowongan.kategori')))],
            'metode' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(config('lowongan.metode')))],
            'jenis' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(config('lowongan.jenis')))],
            'gaji_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'gaji_max' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        $filters = array_filter($filters, fn ($nilai) => $nilai !== null && $nilai !== '');

        foreach (['gaji_min', 'gaji_max'] as $kunci) {
            if (array_key_exists($kunci, $filters)) {
                $filters[$kunci] = (float) $filters[$kunci];
            }
        }

        if (isset($filters['gaji_min'], $filters['gaji_max']) && $filters['gaji_min'] > $filters['gaji_max']) {
            [$filters['gaji_min'], $filters['gaji_max']] = [$filters['gaji_max'], $filters['gaji_min']];
        }

        return view('pages.jobs', [
            'jobs' => $this->filterJobs($jobs->whereIn('type', ['Freelance', 'Part Time']), $filters)->all(),
            'type' => $type,
            'filters' => $filters,
            'kecamatanList' => config('domisili.kecamatan'),
            'kategoriList' => collect(config('lowongan.kategori'))->only(['Freelance', 'Part Time'])->all(),
            'metodeList' => config('lowongan.metode'),
            'jenisList' => collect(config('lowongan.jenis'))->map(fn (array $info) => $info['label'])->all(),
        ]);
    }
    public function job($slug){
        $lowongan = Lowongan::with('user')->where('slug', $slug)->first();
        $job = $lowongan ? $lowongan->dataKartu() : collect($this->jobsData())->firstWhere('slug', $slug);
        abort_unless($job, 404);

        $lamaranSaya = null;
        if ($lowongan && auth()->check()) {
            $lamaranSaya = $lowongan->lamarans()->where('user_id', auth()->id())->first();
        }

        return view('pages.job-detail', compact('job', 'lowongan', 'lamaranSaya'));
    }
    public function auth($mode){return view('auth.form',compact('mode'));}
    public function dashboard($page){return view('dashboard.index',compact('page'));}
    public function admin($page){return view('admin.index',compact('page'));}
}
