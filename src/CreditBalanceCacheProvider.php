<?php

namespace Tokenly\CreditsCache;

use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Tokenly\CreditsCache\CreditBalanceCache;
use Tokenly\CreditsCache\CreditBalanceChanged;
use Tokenly\TokenpassClient\TokenpassAPI;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CreditBalanceCacheProvider extends ServiceProvider
{


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CreditBalanceCache::class, function ($app) {
            return new CreditBalanceCache(app(CacheRepository::class), app(TokenpassAPI::class));
        });

        // handle the cache changed event
        Event::listen(CreditBalanceChanged::class, 'Tokenly\CreditsCache\CreditBalanceCache@handleChangedEvent');
    }

}
