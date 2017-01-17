[![Build Status](https://travis-ci.org/tokenly/credits-cache.svg?branch=master)](https://travis-ci.org/tokenly/credits-cache)

## App Credits Cache

A library to cache Tokenpass app credits locally.  Requires Laravel.

## Installation

- `composer require tokenly/credits-cache`
- Add `Tokenly\CreditsCache\CreditBalanceCacheProvider::class` to your list of service providers

## Usage


### Fetching a balance

```php
$credits_cache = app(\Tokenly\CreditsCache\CreditBalanceCache::class);
$credit_balance = $credits_cache->getCredits($credits_group_id, $user_account_uuid);

```

If no local cache balance is present, the cache will call the Tokenpass API and populate it.



### Clearing the cache

To clear the local cache, fire a CreditBalanceChanged event.  This will force a reload from Tokenpass on the next `getCredits` call.

```php
use Tokenly\CreditsCache\CreditBalanceChanged;

event(new CreditBalanceChanged($credits_group_id, $user_account_uuid));
```

