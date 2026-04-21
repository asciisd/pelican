<?php

namespace Mohanad\Copytrade\DTOs\Profile;

use JsonSerializable;

class UserInfoDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $profileId,
        public readonly array $rawData = []
    ) {}

    /**
     * Create from API response.
     */
    public static function fromResponse(array $data): self
    {
        return new self(
            profileId: $data['https://copy-trade.io/profile'] ?? '',
            rawData: $data
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'profile_id' => $this->profileId,
            'raw_data' => $this->rawData,
        ];
    }

    /**
     * JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
