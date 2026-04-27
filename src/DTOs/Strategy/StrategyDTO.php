<?php

namespace Asciisd\Copytrade\DTOs\Strategy;

use JsonSerializable;

class StrategyDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $riskProfile,
        public readonly float $fee,
        public readonly ?StrategyConnectionDTO $connection = null,
        public readonly array $rawData = []
    ) {}

    /**
     * Create from API response.
     */
    public static function fromResponse(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: $data['name'] ?? '',
            riskProfile: $data['riskProfile'] ?? '',
            fee: (float) ($data['fee'] ?? 0),
            connection: isset($data['connection']) ? StrategyConnectionDTO::fromArray($data['connection']) : null,
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
            'fee' => $this->fee,
            'connection' => $this->connection?->toArray(),
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
