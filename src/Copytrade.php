<?php

namespace Mohanad\Copytrade;

use Mohanad\Copytrade\Contracts\CopierServiceInterface;
use Mohanad\Copytrade\Contracts\ProfileServiceInterface;
use Mohanad\Copytrade\Contracts\ServerServiceInterface;

class Copytrade
{
    /**
     * The configuration array.
     */
    protected array $config;

    /**
     * Create a new Copytrade instance.
     */
    public function __construct(
        array $config = [],
        protected ?ProfileServiceInterface $profileService = null,
        protected ?ServerServiceInterface $serverService = null,
        protected ?CopierServiceInterface $copierService = null
    ) {
        $this->config = $config;
    }

    /**
     * Get the profile service.
     */
    public function profiles(): ProfileServiceInterface
    {
        return $this->profileService ?? app(ProfileServiceInterface::class);
    }

    /**
     * Get the server service.
     */
    public function servers(): ServerServiceInterface
    {
        return $this->serverService ?? app(ServerServiceInterface::class);
    }

    /**
     * Get the copier service.
     */
    public function copiers(): CopierServiceInterface
    {
        return $this->copierService ?? app(CopierServiceInterface::class);
    }

    /**
     * Set the access token for API requests.
     */
    public function withToken(string $token): self
    {
        $this->profiles()->withToken($token);

        if ($this->serverService) {
            $this->servers()->withToken($token);
        }

        if ($this->copierService) {
            $this->copiers()->withToken($token);
        }

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
