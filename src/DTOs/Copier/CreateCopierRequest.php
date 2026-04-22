<?php

namespace Mohanad\Copytrade\DTOs\Copier;

use InvalidArgumentException;

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
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Copier name is required and cannot be empty');
        }

        if (! isset($data['connection']) || ! is_array($data['connection'])) {
            throw new InvalidArgumentException('Connection configuration is required');
        }

        if (! isset($data['drawdown']) || ! is_array($data['drawdown'])) {
            throw new InvalidArgumentException('Drawdown configuration is required');
        }

        return new self(
            name: $data['name'],
            connection: CopierConnectionDTO::fromArray($data['connection']),
            drawdown: CopierDrawdownDTO::fromArray($data['drawdown'])
        );
    }
}