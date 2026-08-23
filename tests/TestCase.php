<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Services\KitchenService;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // KitchenService memoizes inventory lookups per request; the static
        // cache outlives the per-test application, so clear it between tests.
        KitchenService::resetInventoryCache();
    }
}
