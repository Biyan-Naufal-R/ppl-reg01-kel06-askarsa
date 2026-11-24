<?php
namespace App\Http\Controllers;

use App\Models\ProfilJasa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Data kartu jasa. Secara default memasukkan seluruh profil aktif, termasuk
     * yang sedang tidak tersedia, agar tetap muncul di katalog dengan label.
     * Beranda memakai $hanyaTersedia = true sehingga hanya menampilkan yang siap menerima pesanan.
     */
    private function serviceData(bool $hanyaTersedia = false): array
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
            ];
        })->values()->all();
    }

    private function jobsData(){return [
    ['slug'=>'desainer-konten-media-sosial','company'=>'Studio Karsa Banyumas','title'=>'Desainer Konten Media Sosial','type'=>'Freelance','mode'=>'Remote','location'=>'Purwokerto Utara, Banyumas','salary'=>'Rp1.500.000 / proyek'],
    ['slug'=>'staf-administrasi','company'=>'CV Satria Niaga','title'=>'Staf Administrasi','type'=>'Full-Time','mode'=>'On-site','location'=>'Purwokerto Selatan, Banyumas','salary'=>'Rp2.500.000–3.000.000 / bulan'],
    ['slug'=>'fotografer-produk-umkm','company'=>'Kedai Kopi Lereng','title'=>'Fotografer Produk UMKM','type'=>'Freelance','mode'=>'On-site','location'=>'Baturaden, Banyumas','salary'=>'Rp800.000 / proyek'],
    ['slug'=>'barista','company'=>'Kedai Kopi Lereng','title'=>'Barista','type'=>'Full-Time','mode'=>'On-site','location'=>'Baturaden, Banyumas','salary'=>'Rp2.300.000–2.800.000 / bulan']];}
    public function home(){return view('pages.home',['services'=>$this->serviceData(true),'jobs'=>$this->jobsData()]);}
    public function services(){return view('pages.services',['services'=>$this->serviceData()]);}
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
    public function jobs(Request $request,$type='all'){ $jobs=collect($this->jobsData())->when($type!=='all',fn($q)=>$q->where('type',$type==='freelance'?'Freelance':'Full-Time'))->values()->all();return view('pages.jobs',['jobs'=>$jobs,'type'=>$type]);}
    public function job($slug){$job=collect($this->jobsData())->firstWhere('slug',$slug);abort_unless($job,404);return view('pages.job-detail',compact('job'));}
    public function auth($mode){return view('auth.form',compact('mode'));}
    public function dashboard($page){return view('dashboard.index',compact('page'));}
    public function admin($page){return view('admin.index',compact('page'));}
}
