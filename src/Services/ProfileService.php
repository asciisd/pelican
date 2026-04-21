<?php

namespace Mohanad\Copytrade\Services;

use Mohanad\Copytrade\Contracts\HttpClientInterface;
use Mohanad\Copytrade\Contracts\ProfileServiceInterface;
use Mohanad\Copytrade\DTOs\Profile\ProfileDTO;
use Mohanad\Copytrade\DTOs\Profile\UpdateProfileRequest;
use Mohanad\Copytrade\DTOs\Profile\UserInfoDTO;

class ProfileService implements ProfileServiceInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected HttpClientInterface $identityClient,
        protected string $baseUri
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): UserInfoDTO
    {
        $response = $this->identityClient->get('/connect/userinfo');

        return UserInfoDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile(string $profileId): ProfileDTO
    {
        $response = $this->httpClient->get("/api/profiles/{$profileId}");

        return ProfileDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(string $profileId, array $data): ProfileDTO
    {
        $request = UpdateProfileRequest::fromArray($data);

        $response = $this->httpClient->put(
            "/api/profiles/{$profileId}",
            $request->toArray()
        );

        return ProfileDTO::fromResponse($response);
    }

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): self
    {
        $this->httpClient->withToken($token);
        $this->identityClient->withToken($token);

        return $this;
    }
}