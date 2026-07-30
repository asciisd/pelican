<?php

namespace Asciisd\Copytrade;

use Asciisd\Copytrade\Contracts\AuthServiceInterface;
use Asciisd\Copytrade\Contracts\CopierServiceInterface;
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\Contracts\SectionServiceInterface;
use Asciisd\Copytrade\Contracts\ServerServiceInterface;
use Asciisd\Copytrade\Contracts\StrategyServiceInterface;

class Copytrade
{
    /**
     * The configuration array.
     */
    protected array $config;

    /**
     * The access token for API requests.
     */
    protected ?string $token = null;

    /**
     * Create a new Copytrade instance.
     */
    public function __construct(
        array $config = [],
        protected ?ProfileServiceInterface $profileService = null,
        protected ?ServerServiceInterface $serverService = null,
        protected ?CopierServiceInterface $copierService = null,
        protected ?SectionServiceInterface $sectionService = null,
        protected ?StrategyServiceInterface $strategyService = null,
        protected ?AuthServiceInterface $authService = null
    ) {
        $this->config = $config;
    }

    /**
     * Get the profile service.
     */
    public function profiles(): ProfileServiceInterface
    {
        return $this->service($this->profileService, ProfileServiceInterface::class);
    }

    /**
     * Get the server service.
     */
    public function servers(): ServerServiceInterface
    {
        return $this->service($this->serverService, ServerServiceInterface::class);
    }

    /**
     * Get the copier service.
     */
    public function copiers(): CopierServiceInterface
    {
        return $this->service($this->copierService, CopierServiceInterface::class);
    }

    /**
     * Get the section service.
     */
    public function sections(): SectionServiceInterface
    {
        return $this->service($this->sectionService, SectionServiceInterface::class);
    }

    /**
     * Get the strategy service.
     */
    public function strategies(): StrategyServiceInterface
    {
        return $this->service($this->strategyService, StrategyServiceInterface::class);
    }

    /**
     * Get the authentication service.
     */
    public function auth(): AuthServiceInterface
    {
        return $this->authService ?? app(AuthServiceInterface::class);
    }

    /**
     * Resolve a token-scoped service, preferring an injected instance.
     *
     * @param  class-string  $interface
     */
    protected function service(?object $injected, string $interface): object
    {
        $service = $injected ?? app($interface);

        return $this->token ? $service->withToken($this->token) : $service;
    }

    /**
     * Set the access token for API requests.
     */
    public function withToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the base URI.
     */
    public function getBaseUri(): string
    {
        return $this->config['base_uri'] ?? config('copytrade.base_uri');
    }

    /**
     * Get the identity URI.
     */
    public function getIdentityUri(): string
    {
        return $this->config['identity_uri'] ?? config('copytrade.identity_uri');
    }

    /**
     * Get the client ID.
     */
    public function getClientId(): string
    {
        return $this->config['client_id'] ?? config('copytrade.client_id');
    }

    /**
     * Get the ACR values.
     */
    public function getAcrValues(): string
    {
        return $this->config['acr_values'] ?? 'tenant:'.$this->getClientId();
    }

    /**
     * Get the callback URL.
     */
    public function getCallbackUrl(): string
    {
        return $this->config['callback_url'] ?? $this->getClientId().'://authenticated';
    }

    /**
     * Get all configuration.
     */
    public function getConfig(): array
    {
        return [
            'base_uri' => $this->getBaseUri(),
            'identity_uri' => $this->getIdentityUri(),
            'client_id' => $this->getClientId(),
            'acr_values' => $this->getAcrValues(),
            'callback_url' => $this->getCallbackUrl(),
        ];
    }
}