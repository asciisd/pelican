<?php

namespace Mohanad\Copytrade\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Mohanad\Copytrade\Contracts\HttpClientInterface;
use Mohanad\Copytrade\Exceptions\CopytradeException;

class HttpClient implements HttpClientInterface
{
    protected ?string $token = null;

    protected array $defaultHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    public function __construct(
        protected string $baseUri,
        protected int $timeout = 120
    ) {}

    /**
     * Get HTTP client instance.
     */
    protected function client(): PendingRequest
    {
        $client = Http::baseUrl($this->baseUri)
            ->timeout($this->timeout)
            ->withHeaders($this->defaultHeaders);

        if ($this->token) {
            $client->withToken($this->token);
        }

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $uri, array $headers = []): array
    {
        return $this->request('GET', $uri, [], $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $uri, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $uri, $data, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $uri, array $data = [], array $headers = []): array
    {
        return $this->request('PUT', $uri, $data, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $uri, array $headers = []): array
    {
        return $this->request('DELETE', $uri, [], $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function withToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Send HTTP request.
     *
     *
     * @throws CopytradeException
     */
    protected function request(string $method, string $uri, array $data = [], array $headers = []): array
    {
        $response = $this->client()
            ->withHeaders($headers)
            ->send($method, $uri, [
                'json' => $data,
            ]);

        if ($response->failed()) {
            throw new CopytradeException(
                "API request failed: {$response->status()} - {$response->body()}",
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Create a new instance with a different base URI.
     */
    public function withBaseUri(string $baseUri): self
    {
        return new self($baseUri, $this->timeout);
    }
}