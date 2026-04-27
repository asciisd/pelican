<?php

namespace Asciisd\Copytrade\Services;

use Illuminate\Support\Facades\Http;

use Asciisd\Copytrade\Contracts\ServerServiceInterface;
use Asciisd\Copytrade\DTOs\Server\ServerDTO;

class ServerService implements ServerServiceInterface
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUri,
        protected int $timeout = 120
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getServers(): array
    {
        $response = $this->makeRequest('GET', '/api/servers');

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
        $this->token = $token;

        return $this;
    }

    /**
     * Make HTTP request.
     */
    protected function makeRequest(string $method, string $uri, array $data = []): array
    {
        $client = Http::baseUrl($this->baseUri)
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->token) {
            $client->withToken($this->token);
        }

        $response = $client->send($method, $uri, [
            'json' => $data,
        ]);

        // Check if response is successful
        if ($response->failed()) {
            throw new \Asciisd\Copytrade\Exceptions\CopytradeException(
                "API request failed: {$response->status()} - {$response->body()}",
                $response->status()
            );
        }

        $result = $response->json();

        // Ensure we always return an array
        return is_array($result) ? $result : [];
    }
}