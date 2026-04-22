<?php

namespace Mohanad\Copytrade;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Mohanad\Copytrade\Contracts\CopierServiceInterface;
use Mohanad\Copytrade\Contracts\ProfileServiceInterface;
use Mohanad\Copytrade\Contracts\SectionServiceInterface;
use Mohanad\Copytrade\Contracts\ServerServiceInterface;
use Mohanad\Copytrade\Contracts\StrategyServiceInterface;
use Mohanad\Copytrade\Http\HttpClient;
use Mohanad\Copytrade\Services\CopierService;
use Mohanad\Copytrade\Services\ProfileService;
use Mohanad\Copytrade\Services\SectionService;
use Mohanad\Copytrade\Services\ServerService;
use Mohanad\Copytrade\Services\StrategyService;
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

        // Register HTTP Clients
        $this->app->singleton('copytrade.http.api', function ($app) {
            $client = new HttpClient(
                baseUri: config('copytrade.base_uri'),
                timeout: config('copytrade.timeout', 120)
            );

            if ($token = config('copytrade.access_token')) {
                $client->withToken($token);
            }

            return $client;
        });

        $this->app->singleton('copytrade.http.identity', function ($app) {
            $client = new HttpClient(
                baseUri: config('copytrade.identity_uri'),
                timeout: config('copytrade.timeout', 30)
            );

            if ($token = config('copytrade.access_token')) {
                $client->withToken($token);
            }

            return $client;
        });

        // Register Services
        $this->app->singleton(ProfileServiceInterface::class, function ($app) {
            return new ProfileService(
                httpClient: $app->make('copytrade.http.api'),
                identityClient: $app->make('copytrade.http.identity'),
                baseUri: config('copytrade.base_uri')
            );
        });

        $this->app->singleton(ServerServiceInterface::class, function ($app) {
            return new ServerService(
                httpClient: $app->make('copytrade.http.api')
            );
        });

        $this->app->singleton(CopierServiceInterface::class, function ($app) {
            return new CopierService(
                httpClient: $app->make('copytrade.http.api')
            );
        });

        $this->app->singleton(SectionServiceInterface::class, function ($app) {
            return new SectionService(
                httpClient: $app->make('copytrade.http.api')
            );
        });

        $this->app->singleton(StrategyServiceInterface::class, function ($app) {
            return new StrategyService(
                httpClient: $app->make('copytrade.http.api')
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