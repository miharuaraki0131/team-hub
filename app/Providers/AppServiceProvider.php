<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::share('colorPalette', [
            '#bfdbfe' => 'Blue',
            '#fecaca' => 'Red',
            '#fed7aa' => 'Orange',
            '#fef08a' => 'Yellow',
            '#bbf7d0' => 'Green',
            '#a5f3fc' => 'Cyan',
            '#c7d2fe' => 'Indigo',
            '#e9d5ff' => 'Purple',
            '#fbcfe8' => 'Pink',
            '#d1d5db' => 'Gray',
        ]);
    }
}
