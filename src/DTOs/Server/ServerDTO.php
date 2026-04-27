<?php

namespace Asciisd\Copytrade\DTOs\Server;

use JsonSerializable;

class ServerDTO implements JsonSerializable
{
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

    /**
     * JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
