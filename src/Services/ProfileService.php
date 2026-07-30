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
        return UserInfoDTO::fromResponse(
            $this->get('/connect/userinfo', baseUri: $this->identityUri)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile(string $profileId): ProfileDTO
    {
        return ProfileDTO::fromResponse($this->get("/api/profiles/{$profileId}"));
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(string $profileId, array $data): ProfileDTO
    {
        $request = UpdateProfileRequest::fromArray($data);

        return ProfileDTO::fromResponse(
            $this->put("/api/profiles/{$profileId}", $request->toArray())
        );
    }
}
