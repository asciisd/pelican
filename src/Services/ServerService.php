<?php

namespace Mohanad\Copytrade\Services;

use Mohanad\Copytrade\Contracts\HttpClientInterface;
use Mohanad\Copytrade\Contracts\ServerServiceInterface;
use Mohanad\Copytrade\DTOs\Server\ServerDTO;

class ServerService implements ServerServiceInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getServers(): array
    {
        $response = $this->httpClient->get('/api/servers');

        // Extract servers array (handle both direct array and wrapped response)
        $servers = isset($response[0]) ? $response : ($response['data'] ?? []);

        // Convert each server to DTO
        return array_map(
            fn (array $server) => ServerDTO::fromResponse($server),
            $servers
        );
    }

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): self
    {
        $this->httpClient->withToken($token);

        return $this;
    }
}
