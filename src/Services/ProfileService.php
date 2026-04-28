<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\DTOs\Profile\ProfileDTO;
use Asciisd\Copytrade\DTOs\Profile\UpdateProfileRequest;
use Asciisd\Copytrade\DTOs\Profile\UserInfoDTO;

class ProfileService extends AbstractService implements ProfileServiceInterface
{
    public function __construct(
        string $baseUri,
        protected string $identityUri,
        int $timeout = 120
    ) {
        parent::__construct($baseUri, $timeout);
    }

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
}