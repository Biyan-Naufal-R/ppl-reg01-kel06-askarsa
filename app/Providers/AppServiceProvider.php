<?php

namespace App\Providers;

use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Pesanan;
use App\Policies\LamaranPolicy;
use App\Policies\LowonganPolicy;
use App\Policies\PesananPolicy;
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
    }
}
