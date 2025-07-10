<?php

namespace App\Providers;

use App\Services\CrawlerService;
use Illuminate\Support\ServiceProvider;
use League\CommonMark\CommonMarkConverter;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        app()->factory(CommonMarkConverter::class);

        $this->app->singleton(CrawlerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
