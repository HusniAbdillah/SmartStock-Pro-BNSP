<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Pastikan helper tersedia di queue worker & artisan (termasuk setelah restart worker)
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        Paginator::useTailwind();
    }
}
