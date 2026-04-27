<?php

namespace Asciisd\Copytrade;

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
    ) {
        $this->config = $config;
    }

    /**
     * Get the profile service.
     */
    public function profiles(): ProfileServiceInterface
    {
        $service = $this->profileService ?? app(ProfileServiceInterface::class);

        return $this->token ? $service->withToken($this->token) : $service;
    }

    /**
     * Get the server service.
     */
    public function servers(): ServerServiceInterface
    {
        $service = $this->serverService ?? app(ServerServiceInterface::class);

        return $this->token ? $service->withToken($this->token) : $service;
    }

    /**
     * Get the copier service.
     */
    public function copiers(): CopierServiceInterface
    {
        $service = $this->copierService ?? app(CopierServiceInterface::class);

        return $this->token ? $service->withToken($this->token) : $service;
    }

    /**
     * Get the section service.
     */
    public function sections(): SectionServiceInterface
    {
        $service = $this->sectionService ?? app(SectionServiceInterface::class);

        return $this->token ? $service->withToken($this->token) : $service;
    }

    /**
     * Get the strategy service.
     */
    public function strategies(): StrategyServiceInterface
    {
        $service = $this->strategyService ?? app(StrategyServiceInterface::class);

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

    /**
     * Test method to verify the package is working.
     */
    public function test(): string
    {
        return 'CopyTrade package is working! Client ID: '.$this->getClientId();
    }
}