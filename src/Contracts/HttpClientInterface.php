<?php

namespace Mohanad\Copytrade\Contracts;

interface HttpClientInterface
{
    /**
     * Send a GET request.
     */
    public function get(string $uri, array $headers = []): array;

    /**
     * Send a POST request.
     */
    public function post(string $uri, array $data = [], array $headers = []): array;

    /**
     * Send a PUT request.
     */
    public function put(string $uri, array $data = [], array $headers = []): array;

    /**
     * Send a DELETE request.
     */
    public function delete(string $uri, array $headers = []): array;

    /**
     * Set the authorization token.
     */
    public function withToken(string $token): self;
}
