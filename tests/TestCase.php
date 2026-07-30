<?php

namespace Asciisd\Copytrade\Tests;

use Asciisd\Copytrade\CopytradeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CopytradeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('copytrade.base_uri', 'https://papi.copy-trade.io');
        $app['config']->set('copytrade.identity_uri', 'https://identity.copy-trade.io');
        $app['config']->set('copytrade.client_id', 'pelican');
        $app['config']->set('copytrade.acr_values', 'tenant:pelican');
        $app['config']->set('copytrade.callback_url', 'pelican://authenticated');
        $app['config']->set('copytrade.auth.scopes', 'openid profile email copytrade');
        $app['config']->set('copytrade.auth.client_secret', null);
        $app['config']->set('cache.default', 'array');
    }
}
