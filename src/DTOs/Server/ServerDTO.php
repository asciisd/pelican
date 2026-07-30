<?php

namespace Asciisd\Copytrade\DTOs\Server;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use JsonSerializable;

class ServerDTO implements JsonSerializable
{
    use SerializesToArray;

    public function __construct(
        public readonly array $rawData = []
    ) {}

    /**
     * Create from API response.
     */
    public static function fromResponse(array $data): self
    {
        return new self(
            rawData: $data
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'raw_data' => $this->rawData,
        ];
    }
}
