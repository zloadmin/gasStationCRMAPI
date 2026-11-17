<?php

namespace App\Providers;

use App\Services\Usrbb\ServiceSynchronizer;
use App\Services\Usrbb\TerminalServiceSynchronizer;
use App\Services\Usrbb\TerminalSynchronizer;
use App\Services\Usrbb\TransactionSynchronizer;
use App\Services\Usrbb\UsrbbClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UsrbbClient::class, fn () => new UsrbbClient(
            baseUrl: config('services.usrbb.base_url'),
            cookie: config('services.usrbb.cookie'),
            timeout: config('services.usrbb.timeout'),
        ));

        $this->app->singleton(
            ServiceSynchronizer::class,
            fn () => new ServiceSynchronizer()
        );
        $this->app->singleton(
            TerminalSynchronizer::class,
            fn () => new TerminalSynchronizer()
        );
        $this->app->singleton(
            TransactionSynchronizer::class,
            fn () => new TransactionSynchronizer()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
