<?php

namespace Mohanad\Copytrade\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mohanad\Copytrade\Contracts\HttpClientInterface;
use Mohanad\Copytrade\Exceptions\AuthenticationException;
use Mohanad\Copytrade\Exceptions\CopytradeException;
use Mohanad\Copytrade\Exceptions\NotFoundException;
use Mohanad\Copytrade\Exceptions\RateLimitException;
use Mohanad\Copytrade\Exceptions\ValidationException;

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
            $this->handleFailedResponse($method, $uri, $response);
        }

        return $response->json() ?? [];
    }

    /**
     * Handle failed HTTP response.
     *
     * @throws CopytradeException
     */
    protected function handleFailedResponse(string $method, string $uri, $response): void
    {
        $status = $response->status();
        $errorMessage = $response->json('error') ?? $response->json('message') ?? 'Unknown error';

        // Log the error (sanitized)
        Log::error('Copytrade API request failed', [
            'method' => $method,
            'uri' => $uri,
            'status' => $status,
            'error' => $errorMessage,
        ]);

        // Throw specific exception based on status code
        $message = "API request failed: {$method} {$uri} returned {$status}";

        match (true) {
            $status === 401 || $status === 403 => throw new AuthenticationException($message, $status),
            $status === 404 => throw new NotFoundException($message, $status),
            $status === 422 => throw new ValidationException($message, $status),
            $status === 429 => throw new RateLimitException($message, $status),
            default => throw new CopytradeException($message, $status),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function uploadFile(string $method, string $uri, string $fileContent, string $filename, string $fieldName = 'file', array $additionalData = []): array
    {
        // Create a temporary file to attach
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        fwrite($tempFile, $fileContent);
        fseek($tempFile, 0);

        try {
            // Build the client without Content-Type header (let Laravel set it for multipart)
            $client = Http::baseUrl($this->baseUri)
                ->timeout($this->timeout)
                ->withHeaders(array_merge(
                    ['Accept' => 'application/json'],
                    array_filter($this->defaultHeaders, fn ($key) => $key !== 'Content-Type', ARRAY_FILTER_USE_KEY)
                ));

            if ($this->token) {
                $client->withToken($this->token);
            }

            // Attach the file
            $client->attach($fieldName, $fileContent, $filename);

            // Add any additional form data
            foreach ($additionalData as $key => $value) {
                $client->withBody($value, 'multipart/form-data');
            }

            $response = $client->send($method, $uri);

            if ($response->failed()) {
                $this->handleFailedResponse($method, $uri, $response);
            }

            return $response->json() ?? [];
        } finally {
            // Always clean up temp file
            fclose($tempFile);
        }
    }

    /**
     * Create a new instance with a different base URI.
     */
    public function withBaseUri(string $baseUri): self
    {
        return new self($baseUri, $this->timeout);
    }
}