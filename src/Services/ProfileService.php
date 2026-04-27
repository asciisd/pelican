<?php

namespace Asciisd\Copytrade\Services;

use Illuminate\Support\Facades\Http;
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\DTOs\Profile\ProfileDTO;
use Asciisd\Copytrade\DTOs\Profile\UpdateProfileRequest;
use Asciisd\Copytrade\DTOs\Profile\UserInfoDTO;

class ProfileService implements ProfileServiceInterface
{
    protected ?string $token = null;

    public function __construct(
        protected string $baseUri,
        protected string $identityUri,
        protected int $timeout = 120
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): UserInfoDTO
    {
        $response = $this->makeRequest('GET', '/connect/userinfo', baseUri: $this->identityUri);

        return UserInfoDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile(string $profileId): ProfileDTO
    {
        $response = $this->makeRequest('GET', "/api/profiles/{$profileId}");

        return ProfileDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(string $profileId, array $data): ProfileDTO
    {
        $request = UpdateProfileRequest::fromArray($data);

        $response = $this->makeRequest('PUT', "/api/profiles/{$profileId}", $request->toArray());

        return ProfileDTO::fromResponse($response);
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