<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use JsonSerializable;

class CopierStatsDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $copierId,
        public readonly array $rawData = []
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            copierId: $data['CopierId'] ?? $data['copier_id'] ?? $data['id'] ?? '',
            rawData: $data
        );
    }

    public function toArray(): array
    {
        return [
            'copier_id' => $this->copierId,
            'raw_data' => $this->rawData,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
