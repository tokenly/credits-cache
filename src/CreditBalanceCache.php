<?php

namespace Tokenly\CreditsCache;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Tokenly\CreditsCache\CreditBalanceChanged;
use Tokenly\TokenpassClient\TokenpassAPI;

class CreditBalanceCache {

    const CACHE_TTL_MINUTES = 1440; // 24 hours

    function __construct(CacheRepository $cache, TokenpassAPI $tokenpass) {
        $this->cache     = $cache;
        $this->tokenpass = $tokenpass;
    }

    public function getCredits($group_id, $account_uuid) {
        $cache_key = $this->buildCacheKey($group_id, $account_uuid);

        // try cached value
        $cached_value = $this->cache->get($cache_key);
        if ($cached_value !== null) {
            return $cached_value;
        }

        // build live value
        $live_value = $this->tokenpass->getAppCreditAccountBalance($group_id, $account_uuid);
        if ($live_value === null) { $live_value = 0; }
        $live_value = intval($live_value);

        // save live value
        $this->cache->put($cache_key, $live_value, self::CACHE_TTL_MINUTES);

        // return value
        return $live_value;
    }

    public function putCredits($group_id, $account_uuid, $value) {
        $cache_key = $this->buildCacheKey($group_id, $account_uuid);

        // save live value
        $this->cache->put($cache_key, $value, self::CACHE_TTL_MINUTES);

        // return value
        return $value;
    }

    public function clear($group_id, $account_uuid) {
        $cache_key = $this->buildCacheKey($group_id, $account_uuid);
        $this->cache->forget($cache_key);
    }

    public function handleChangedEvent(CreditBalanceChanged $event) {
        $event->group_id;
        $event->account_uuid;

        if ($event->new_balance === null) {
            $this->clear($event->group_id, $event->account_uuid);
        } else {
            $this->putCredits($event->group_id, $event->account_uuid, $event->new_balance);
        }
    }

    // ------------------------------------------------------------------------
    
    protected function buildCacheKey($group_id, $account_uuid) {
        return 'crdbal.'.$group_id.'.'.$account_uuid;
    }
}
