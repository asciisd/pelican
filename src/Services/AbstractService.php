<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Exceptions\CopytradeException;
use Illuminate\Support\Facades\Http;

abstract class AbstractService
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUri,
        protected int $timeout = 120
    ) {}

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Make HTTP request.
     */
    protected function makeRequest(string $method, string $uri, array $data = [], ?string $baseUri = null): array
    {
        $client = Http::baseUrl($baseUri ?? $this->baseUri)
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->token) {
            $client->withToken($this->token);
        }

        $response = $client->send($method, $uri, [
            'json' => $data,
        ]);

        if ($response->failed()) {
            throw new CopytradeException(
                "API request failed: {$response->status()} - {$response->body()}",
                $response->status()
            );
        }

        $result = $response->json();

        return is_array($result) ? $result : [];
    }
}