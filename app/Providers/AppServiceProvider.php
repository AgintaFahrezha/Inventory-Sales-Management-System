<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
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
        // Gates for role and permission control
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('manage-master', fn (User $user) => $user->isAdmin());
        Gate::define('cancel-transaction', fn (User $user) => $user->isAdmin());
        Gate::define('manage-purchases', fn (User $user) => true);
        Gate::define('manage-sales', fn (User $user) => true);
    }
}
