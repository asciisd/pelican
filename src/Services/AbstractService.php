<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Exceptions\CopytradeException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class AbstractService
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUri,
        protected int $timeout = 120
    ) {}

    /**
     * Return a copy of the service authenticated with the given token.
     *
     * Immutable on purpose: services are shared singletons, so mutating the
     * instance would leak one caller's token into every other caller.
     */
    public function withToken(string $token): static
    {
        $clone = clone $this;
        $clone->token = $token;

        return $clone;
    }

    /**
     * Send a GET request and return the decoded JSON array.
     */
    protected function get(string $uri, array $query = [], ?string $baseUri = null): array
    {
        return $this->request('GET', $uri, query: $query, baseUri: $baseUri);
    }

    /**
     * Send a POST request and return the decoded JSON array.
     */
    protected function post(string $uri, array $data = []): array
    {
        return $this->request('POST', $uri, data: $data);
    }

    /**
     * Send a PUT request and return the decoded JSON array.
     */
    protected function put(string $uri, array $data = []): array
    {
        return $this->request('PUT', $uri, data: $data);
    }

    /**
     * Send a DELETE request and return the decoded JSON array.
     */
    protected function delete(string $uri): array
    {
        return $this->request('DELETE', $uri);
    }

    /**
     * Upload a file via multipart PUT and return the decoded response.
     */
    protected function upload(string $uri, mixed $fileContent, string $filename): array
    {
        $response = $this->handle(
            $this->client()->attach('file', $fileContent, $filename)->put($uri)
        );

        $result = $response->json();

        if (is_string($result)) {
            return ['url' => $result];
        }

        return is_array($result) ? $result : [];
    }

    /**
     * Convert a list response into an array of DTOs, accepting both bare
     * arrays and "{ data: [...] }" envelopes.
     *
     * @param  class-string  $dtoClass
     */
    protected function mapList(array $response, string $dtoClass): array
    {
        $items = isset($response[0]) ? $response : ($response['data'] ?? []);

        return array_map(
            fn (array $item) => $dtoClass::fromResponse($item),
            $items
        );
    }

    /**
     * Send an HTTP request and return the decoded JSON array.
     */
    protected function request(string $method, string $uri, array $data = [], array $query = [], ?string $baseUri = null): array
    {
        $options = [];

        if ($query !== []) {
            $options['query'] = $query;
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options['json'] = $data;
        }

        $response = $this->handle($this->client($baseUri)->send($method, $uri, $options));

        $result = $response->json();

        return is_array($result) ? $result : [];
    }

    /**
     * Build a configured HTTP client for this service.
     */
    protected function client(?string $baseUri = null): PendingRequest
    {
        $client = Http::baseUrl($baseUri ?? $this->baseUri)
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->token) {
            $client->withToken($this->token);
        }

        return $client;
    }

    /**
     * Throw when the response failed, otherwise return it untouched.
     *
     * @throws CopytradeException
     */
    protected function handle(Response $response): Response
    {
        if ($response->failed()) {
            throw new CopytradeException(
                "API request failed: {$response->status()} - {$response->body()}",
                $response->status()
            );
        }

        return $response;
    }
}
