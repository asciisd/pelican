<?php

namespace Mohanad\Copytrade\DTOs\Copier;

class UpdateCopierRequest
{
    public function __construct(
        public readonly string $name,
        public readonly CopierConnectionDTO $connection,
        public readonly CopierDrawdownDTO $drawdown
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'connection' => $this->connection->toArray(),
            'drawdown' => $this->drawdown->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            connection: CopierConnectionDTO::fromArray($data['connection'] ?? []),
            drawdown: CopierDrawdownDTO::fromArray($data['drawdown'] ?? [])
        );
    }
}
