<?php

namespace App\Providers;

use App\Services\TransaksiService;
use App\Services\Impl\TransaksiServiceImpl;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TransaksiServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public array $singletons = [
        TransaksiService::class => TransaksiServiceImpl::class,
    ];
    public function provides()
    {
        return [
            TransaksiService::class,
        ];
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
