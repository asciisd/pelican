<?php

namespace Mohanad\Copytrade\Contracts;

use Mohanad\Copytrade\DTOs\Profile\ProfileDTO;
use Mohanad\Copytrade\DTOs\Profile\UserInfoDTO;

interface ProfileServiceInterface
{
    /**
     * Get user information and extract profile ID.
     */
    public function getUserInfo(): UserInfoDTO;

    /**
     * Get profile by ID.
     */
    public function getProfile(string $profileId): ProfileDTO;

    /**
     * Update profile.
     */
    public function updateProfile(string $profileId, array $data): ProfileDTO;

    /**
     * Set authorization token for requests.
     */
    public function withToken(string $token): self;
}
