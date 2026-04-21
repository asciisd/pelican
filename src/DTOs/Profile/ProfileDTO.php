<?php

namespace Mohanad\Copytrade\DTOs\Profile;

use JsonSerializable;

class ProfileDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $riskProfile = null,
        public readonly ?string $joined = null,
        public readonly ?string $countryCode = null,
        public readonly array $rawData = []
    ) {}

    /**
     * Create from API response.
     */
    public static function fromResponse(array $data): self
    {
        return new self(
            id: $data['Id'] ?? $data['id'] ?? '',
            name: $data['Name'] ?? $data['name'] ?? null,
            riskProfile: $data['RiskProfile'] ?? $data['riskProfile'] ?? null,
            joined: $data['Joined'] ?? $data['joined'] ?? null,
            countryCode: $data['CountryCode'] ?? $data['countryCode'] ?? null,
            rawData: $data
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'risk_profile' => $this->riskProfile,
            'country_code' => $this->countryCode,
            'raw_data' => $this->rawData, // Debug: see actual API response
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