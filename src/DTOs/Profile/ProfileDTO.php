<?php

namespace Asciisd\Copytrade\DTOs\Profile;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use JsonSerializable;

class ProfileDTO implements JsonSerializable
{
    use SerializesToArray;

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
            id: self::value($data, 'Id', 'id') ?? '',
            name: self::value($data, 'Name', 'name'),
            riskProfile: self::value($data, 'RiskProfile', 'riskProfile'),
            joined: self::value($data, 'Joined', 'joined'),
            countryCode: self::value($data, 'CountryCode', 'countryCode'),
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
            'raw_data' => $this->rawData,
        ];
    }
}
