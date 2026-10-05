<?php

namespace App\Providers;

use App\Models\User;
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
        // L'administrateur national a un accès complet, sans passer par chaque policy.
        Gate::before(fn (User $user) => $user->hasRole('Administrateur national') ?: null);
    }
}
