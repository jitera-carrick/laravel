<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\PasswordResetInterface;
use App\Services\PasswordResetService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the PasswordResetInterface to PasswordResetService
        $this->app->bind(PasswordResetInterface::class, PasswordResetService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
