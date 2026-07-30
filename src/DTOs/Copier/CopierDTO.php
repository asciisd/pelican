<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use JsonSerializable;

class CopierDTO implements JsonSerializable
{
    use SerializesToArray;

    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?CopierConnectionDTO $connection = null,
        public readonly ?CopierDrawdownDTO $drawdown = null,
        public readonly array $rawData = []
    ) {}

    public static function fromResponse(array $data): self
    {
        $connection = self::value($data, 'Connection', 'connection');
        $drawdown = self::value($data, 'Drawdown', 'drawdown');

        return new self(
            id: self::value($data, 'Id', 'id') ?? '',
            name: self::value($data, 'Name', 'name'),
            connection: $connection ? CopierConnectionDTO::fromArray($connection) : null,
            drawdown: $drawdown ? CopierDrawdownDTO::fromArray($drawdown) : null,
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
}
