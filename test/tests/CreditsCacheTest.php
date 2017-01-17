<?php

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Tokenly\CreditsCache\CreditBalanceCache;
use Tokenly\CreditsCache\CreditBalanceChanged;
use Tokenly\TokenpassClient\TokenpassAPI;
use \PHPUnit_Framework_Assert as PHPUnit;

/*
* 
*/
class CreditsCacheTest extends PHPUnit_Framework_TestCase {

    const EXAMPLE_ACCOUNT_ID_1 = '9ba20fa2-326f-4cf4-b3a2-000000000001';

    public function testGetCredits() {
        $tokenpass_mock = Mockery::mock(TokenpassAPI::class)->shouldIgnoreMissing();
        $get_balance_expectation = $tokenpass_mock->shouldReceive('getAppCreditAccountBalance')->andReturn(123);
        $get_balance_expectation->times(1);

        list($laravel_cache_mock) = $this->mockLaravelCache();
        $cache = new CreditBalanceCache($laravel_cache_mock, $tokenpass_mock);

        $credits = $cache->getCredits('001', self::EXAMPLE_ACCOUNT_ID_1);
        PHPUnit::assertEquals(123, $credits);

        // second time won't call tokenpass
        $credits = $cache->getCredits('001', self::EXAMPLE_ACCOUNT_ID_1);
        PHPUnit::assertEquals(123, $credits);
    }

    public function testPutCredits() {
        $tokenpass_mock = Mockery::mock(TokenpassAPI::class)->shouldIgnoreMissing();
        list($laravel_cache_mock) = $this->mockLaravelCache();
        $cache = new CreditBalanceCache($laravel_cache_mock, $tokenpass_mock);

        $cache->putCredits('001', self::EXAMPLE_ACCOUNT_ID_1, 543);
        $credits = $cache->getCredits('001', self::EXAMPLE_ACCOUNT_ID_1);
        PHPUnit::assertEquals(543, $credits);
    }

    public function testClearCredits() {
        $tokenpass_mock = Mockery::mock(TokenpassAPI::class)->shouldIgnoreMissing();
        list($laravel_cache_mock) = $this->mockLaravelCache();
        $cache = new CreditBalanceCache($laravel_cache_mock, $tokenpass_mock);

        $cache->putCredits('001', self::EXAMPLE_ACCOUNT_ID_1, 543);
        $credits = $cache->clear('001', self::EXAMPLE_ACCOUNT_ID_1);
        $credits = $cache->getCredits('001', self::EXAMPLE_ACCOUNT_ID_1);
        PHPUnit::assertEquals(0, $credits);
    }

    public function testHandleChangedEvent() {
        $tokenpass_mock = Mockery::mock(TokenpassAPI::class)->shouldIgnoreMissing();
        list($laravel_cache_mock) = $this->mockLaravelCache();
        $cache = new CreditBalanceCache($laravel_cache_mock, $tokenpass_mock);

        $cache->putCredits('001', self::EXAMPLE_ACCOUNT_ID_1, 543);
        $credits = $cache->getCredits('001', self::EXAMPLE_ACCOUNT_ID_1);
        PHPUnit::assertEquals(543, $credits);

        // set value
        $cache->handleChangedEvent(new CreditBalanceChanged('001', self::EXAMPLE_ACCOUNT_ID_1, 987));
        $credits = $cache->getCredits('001', self::EXAMPLE_ACCOUNT_ID_1);
        PHPUnit::assertEquals(987, $credits);

        // clear
        $cache->handleChangedEvent(new CreditBalanceChanged('001', self::EXAMPLE_ACCOUNT_ID_1));
        $credits = $cache->getCredits('001', self::EXAMPLE_ACCOUNT_ID_1);
        PHPUnit::assertEquals(0, $credits);

    }

    // ------------------------------------------------------------------------
    
    protected function mockLaravelCache() {
        $cache_mock = Mockery::mock(CacheRepository::class);

        $expectations = [];

        $data = new ArrayObject();
        $expectations['get'] = $cache_mock->shouldReceive('get')->andReturnUsing(function($key) use ($data) {
            return isset($data[$key]) ? $data[$key] : null;
        });
        $expectations['put'] = $cache_mock->shouldReceive('put')->andReturnUsing(function($key, $value, $length) use ($data) {
            $data[$key] = $value;
            return;
        });
        $expectations['forget'] = $cache_mock->shouldReceive('forget')->andReturnUsing(function($key) use ($data) {
            unset($data[$key]);
            return;
        });

        return [$cache_mock, $expectations];
    }

}
