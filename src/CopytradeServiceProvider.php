<?php

namespace Asciisd\Copytrade;

use Asciisd\Copytrade\Contracts\AuthServiceInterface;
use Asciisd\Copytrade\Contracts\CopierServiceInterface;
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\Contracts\SectionServiceInterface;
use Asciisd\Copytrade\Contracts\ServerServiceInterface;
use Asciisd\Copytrade\Contracts\StrategyServiceInterface;
use Asciisd\Copytrade\Services\AuthService;
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
        $this->mergeConfigFrom(__DIR__.'/Config/copytrade.php', 'copytrade');

        $this->registerApiServices();
        $this->registerAuthServices();
        $this->registerCopytrade();
    }

    /**
     * Register the API-backed services behind their interfaces.
     */
    protected function registerApiServices(): void
    {
        $factories = [
            ProfileServiceInterface::class => fn () => new ProfileService(
                baseUri: config('copytrade.base_uri'),
                identityUri: config('copytrade.identity_uri'),
                timeout: $this->timeout(),
            ),
            ServerServiceInterface::class => fn () => new ServerService(
                baseUri: config('copytrade.base_uri'),
                timeout: $this->timeout(),
            ),
            CopierServiceInterface::class => fn () => new CopierService(
                baseUri: config('copytrade.base_uri'),
                assetUri: config('copytrade.asset_uri'),
                timeout: $this->timeout(),
            ),
            SectionServiceInterface::class => fn () => new SectionService(
                baseUri: config('copytrade.base_uri'),
                timeout: $this->timeout(),
            ),
            StrategyServiceInterface::class => fn () => new StrategyService(
                baseUri: config('copytrade.base_uri'),
                assetUri: config('copytrade.asset_uri'),
                timeout: $this->timeout(),
            ),
        ];

        foreach ($factories as $interface => $factory) {
            $this->app->singleton($interface, function () use ($factory) {
                $service = $factory();
                $token = config('copytrade.access_token');

                return $token ? $service->withToken($token) : $service;
            });
        }
    }

    /**
     * Register the authentication service and token manager.
     */
    protected function registerAuthServices(): void
    {
        $this->app->singleton(AuthServiceInterface::class, function () {
            $clientId = (string) config('copytrade.client_id');

            return new AuthService(
                identityUri: config('copytrade.identity_uri'),
                clientId: $clientId,
                scopes: (string) config('copytrade.auth.scopes'),
                acrValues: $this->resolveAcrValues($clientId),
                callbackUrl: $this->resolveCallbackUrl($clientId),
                clientSecret: config('copytrade.auth.client_secret'),
                timeout: $this->timeout(),
            );
        });
    }

    /**
     * Register the main class used by the facade.
     */
    protected function registerCopytrade(): void
    {
        $this->app->singleton('copytrade', function ($app) {
            $this->validateConfiguration();

            return new Copytrade(
                config: $app['config']['copytrade'],
                profileService: $app->make(ProfileServiceInterface::class),
                serverService: $app->make(ServerServiceInterface::class),
                copierService: $app->make(CopierServiceInterface::class),
                sectionService: $app->make(SectionServiceInterface::class),
                strategyService: $app->make(StrategyServiceInterface::class),
                authService: $app->make(AuthServiceInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        AliasLoader::getInstance()->alias('Copytrade', Facades\Copytrade::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Config/copytrade.php' => config_path('copytrade.php'),
            ], 'copytrade-config');
        }
    }

    /**
     * Resolve the request timeout.
     */
    protected function timeout(): int
    {
        return (int) config('copytrade.timeout', 120);
    }

    /**
     * Resolve the ACR values, falling back to tenant:{client_id} when empty.
     */
    protected function resolveAcrValues(string $clientId): string
    {
        $acrValues = config('copytrade.acr_values');

        if (is_string($acrValues) && $acrValues !== '') {
            return $acrValues;
        }

        return 'tenant:'.$clientId;
    }

    /**
     * Resolve the OAuth callback URL.
     */
    protected function resolveCallbackUrl(string $clientId): string
    {
        $callbackUrl = config('copytrade.callback_url');

        if (is_string($callbackUrl) && $callbackUrl !== '') {
            return $callbackUrl;
        }

        return $clientId.'://authenticated';
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
