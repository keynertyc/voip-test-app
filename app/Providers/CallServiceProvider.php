<?php

namespace App\Providers;

use App\Services\CallManager;
use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\CallProviderInterface;

class CallServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $provider = CallManager::$providers[config('cpass.provider')] ?? reset(CallManager::$providers);

        $this->app->bind(
            CallProviderInterface::class,
            $provider
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
