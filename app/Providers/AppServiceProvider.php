<?php

namespace App\Providers;

use App\Models\Lamaran;
use App\Models\Laporan;
use App\Models\Lowongan;
use App\Models\Pesanan;
use App\Models\ProfilJasa;
use App\Policies\LamaranPolicy;
use App\Policies\LaporanPolicy;
use App\Policies\LowonganPolicy;
use App\Policies\PesananPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Pesanan::class, PesananPolicy::class);
        Gate::policy(Lowongan::class, LowonganPolicy::class);
        Gate::policy(Lamaran::class, LamaranPolicy::class);
        Gate::policy(Laporan::class, LaporanPolicy::class);

        Relation::morphMap([
            'profil_jasa' => ProfilJasa::class,
            'lowongan' => Lowongan::class,
        ]);
    }
}
