<?php

namespace Mohanad\Copytrade\DTOs\Profile;

class UpdateProfileRequest
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $riskProfile = null,
        public readonly ?string $countryCode = null
    ) {}

    /**
     * Convert to array for API request.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'riskProfile' => $this->riskProfile,
            'countryCode' => $this->countryCode,
        ], fn ($value) => $value !== null);
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            riskProfile: $data['riskProfile'] ?? null,
            countryCode: $data['countryCode'] ?? null
        );
    }
}
