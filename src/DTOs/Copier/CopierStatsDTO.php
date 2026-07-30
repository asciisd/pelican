<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use JsonSerializable;

class CopierStatsDTO implements JsonSerializable
{
    use SerializesToArray;

    public function __construct(
        public readonly string $copierId,
        public readonly array $rawData = []
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            copierId: self::value($data, 'CopierId', 'copier_id', 'id') ?? '',
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
}
