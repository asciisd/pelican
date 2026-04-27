<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use JsonSerializable;

class CopierDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?CopierConnectionDTO $connection = null,
        public readonly ?CopierDrawdownDTO $drawdown = null,
        public readonly array $rawData = []
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            id: $data['Id'] ?? $data['id'] ?? '',
            name: $data['Name'] ?? $data['name'] ?? null,
            connection: isset($data['Connection']) || isset($data['connection'])
                ? CopierConnectionDTO::fromArray($data['Connection'] ?? $data['connection'])
                : null,
            drawdown: isset($data['Drawdown']) || isset($data['drawdown'])
                ? CopierDrawdownDTO::fromArray($data['Drawdown'] ?? $data['drawdown'])
                : null,
            rawData: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'connection' => $this->connection?->toArray(),
            'drawdown' => $this->drawdown?->toArray(),
            'raw_data' => $this->rawData,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}