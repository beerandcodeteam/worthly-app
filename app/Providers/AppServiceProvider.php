<?php

namespace App\Providers;

use App\Contracts\SecureTokenStorage;
use App\Support\Storage\NativeSecureTokenStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SecureTokenStorage::class, NativeSecureTokenStorage::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
