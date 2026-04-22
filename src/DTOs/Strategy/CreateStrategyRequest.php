<?php

namespace Mohanad\Copytrade\DTOs\Strategy;

use InvalidArgumentException;

class CreateStrategyRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $riskProfile,
        public readonly float $fee,
        public readonly StrategyConnectionDTO $connection
    ) {}

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'riskProfile' => $this->riskProfile,
            'fee' => $this->fee,
            'connection' => $this->connection->toArray(),
        ];
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Strategy name is required and cannot be empty');
        }

        if (empty($data['riskProfile'])) {
            throw new InvalidArgumentException('Risk profile is required and cannot be empty');
        }

        if (! isset($data['fee']) || ! is_numeric($data['fee'])) {
            throw new InvalidArgumentException('Fee is required and must be numeric');
        }

        if (! isset($data['connection']) || ! is_array($data['connection'])) {
            throw new InvalidArgumentException('Connection configuration is required');
        }

        return new self(
            name: $data['name'],
            riskProfile: $data['riskProfile'],
            fee: (float) $data['fee'],
            connection: StrategyConnectionDTO::fromArray($data['connection'])
        );
    }
}