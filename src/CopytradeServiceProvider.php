<?php

namespace Asciisd\Copytrade;

use Asciisd\Copytrade\Contracts\CopierServiceInterface;
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\Contracts\SectionServiceInterface;
use Asciisd\Copytrade\Contracts\ServerServiceInterface;
use Asciisd\Copytrade\Contracts\StrategyServiceInterface;
use Asciisd\Copytrade\Services\CopierService;
use Asciisd\Copytrade\Services\ProfileService;
use Asciisd\Copytrade\Services\SectionService;
use Asciisd\Copytrade\Services\ServerService;
use Asciisd\Copytrade\Services\StrategyService;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class CopytradeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/Config/copytrade.php',
            'copytrade'
        );

        // Validate required configuration
        $this->validateConfiguration();

        // Get common configuration
        $baseUri = config('copytrade.base_uri');
        $identityUri = config('copytrade.identity_uri');
        $timeout = config('copytrade.timeout', 120);
        $token = config('copytrade.access_token');

        // Register Services
        $this->app->singleton(ProfileServiceInterface::class, function ($app) use ($baseUri, $identityUri, $timeout, $token) {
            $service = new ProfileService(
                baseUri: $baseUri,
                identityUri: $identityUri,
                timeout: $timeout
            );

            if ($token) {
                $service->withToken($token);
            }

            return $service;
        });

        $this->app->singleton(ServerServiceInterface::class, function ($app) use ($baseUri, $timeout, $token) {
            $service = new ServerService(
                baseUri: $baseUri,
                timeout: $timeout
            );

            if ($token) {
                $service->withToken($token);
            }

            return $service;
        });

        $this->app->singleton(CopierServiceInterface::class, function ($app) use ($baseUri, $timeout, $token) {
            $service = new CopierService(
                baseUri: $baseUri,
                timeout: $timeout
            );

            if ($token) {
                $service->withToken($token);
            }

            return $service;
        });

        $this->app->singleton(SectionServiceInterface::class, function ($app) use ($baseUri, $timeout, $token) {
            $service = new SectionService(
                baseUri: $baseUri,
                timeout: $timeout
            );

            if ($token) {
                $service->withToken($token);
            }

            return $service;
        });

        $this->app->singleton(StrategyServiceInterface::class, function ($app) use ($baseUri, $timeout, $token) {
            $service = new StrategyService(
                baseUri: $baseUri,
                timeout: $timeout
            );

            if ($token) {
                $service->withToken($token);
            }

            return $service;
        });

        // Register the main class to use with the facade
        $this->app->singleton('copytrade', function ($app) {
            return new Copytrade(
                config: $app['config']['copytrade'],
                profileService: $app->make(ProfileServiceInterface::class),
                serverService: $app->make(ServerServiceInterface::class),
                copierService: $app->make(CopierServiceInterface::class),
                sectionService: $app->make(SectionServiceInterface::class),
                strategyService: $app->make(StrategyServiceInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register facade alias
        $loader = AliasLoader::getInstance();
        $loader->alias('Copytrade', Facades\Copytrade::class);

        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Config/copytrade.php' => config_path('copytrade.php'),
            ], 'copytrade-config');
        }
    }

    /**
     * Validate required configuration values.
     *
     * @throws RuntimeException
     */
    protected function validateConfiguration(): void
    {
        $required = ['base_uri', 'identity_uri', 'client_id'];

        foreach ($required as $key) {
            if (empty(config("copytrade.{$key}"))) {
                throw new RuntimeException(
                    "Copytrade configuration missing required key: {$key}. ".'Please publish and configure the copytrade config file.'
                );
            }
        }
    }
}