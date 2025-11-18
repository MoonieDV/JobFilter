<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Smalot\PdfParser\Parser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Parser::class, fn () => new Parser());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
