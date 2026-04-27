<?php

namespace Asciisd\Copytrade;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
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

        // Register Services
        $this->app->singleton(ProfileServiceInterface::class, function ($app) {
            return new ProfileService(
                baseUri: config('copytrade.base_uri'),
                identityUri: config('copytrade.identity_uri'),
                timeout: config('copytrade.timeout', 120)
            );
        });

        $this->app->singleton(ServerServiceInterface::class, function ($app) {
            return new ServerService(
                baseUri: config('copytrade.base_uri'),
                timeout: config('copytrade.timeout', 120)
            );
        });

        $this->app->singleton(CopierServiceInterface::class, function ($app) {
            return new CopierService(
                baseUri: config('copytrade.base_uri'),
                timeout: config('copytrade.timeout', 120)
            );
        });

        $this->app->singleton(SectionServiceInterface::class, function ($app) {
            return new SectionService(
                baseUri: config('copytrade.base_uri'),
                timeout: config('copytrade.timeout', 120)
            );
        });

        $this->app->singleton(StrategyServiceInterface::class, function ($app) {
            return new StrategyService(
                baseUri: config('copytrade.base_uri'),
                timeout: config('copytrade.timeout', 120)
            );
        });

        // Register the main class to use with the facade
        $this->app->singleton('copytrade', function ($app) {
            return new Copytrade(
                config: $app['config']['copytrade'],
                profileService: $app->make(ProfileServiceInterface::class),
                serverService: $app->make(ServerServiceInterface::class),
                copierService: $app->make(CopierServiceInterface::class),
                sectionService: $app->make(SectionServiceInterface::class),
                strategyService: $app->make(StrategyServiceInterface::class),
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