<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use InvalidArgumentException;

class CopyStrategyRequest
{
    public function __construct(
        public readonly string $tradeSizeType,
        public readonly float $tradeSizeValue,
        public readonly bool $isOpenExistingTrades,
        public readonly bool $isRoundUpToMinimumSize
    ) {}

    public function toArray(): array
    {
        return [
            'TradeSizeType' => $this->tradeSizeType,
            'TradeSizeValue' => $this->tradeSizeValue,
            'IsOpenExistingTrades' => $this->isOpenExistingTrades,
            'IsRoundUpToMinimumSize' => $this->isRoundUpToMinimumSize,
        ];
    }

    public static function fromArray(array $data): self
    {
        if (empty($data['TradeSizeType'])) {
            throw new InvalidArgumentException('TradeSizeType is required and cannot be empty');
        }

        if (! isset($data['TradeSizeValue']) || ! is_numeric($data['TradeSizeValue'])) {
            throw new InvalidArgumentException('TradeSizeValue is required and must be numeric');
        }

        return new self(
            tradeSizeType: $data['TradeSizeType'],
            tradeSizeValue: (float) $data['TradeSizeValue'],
            isOpenExistingTrades: (bool) ($data['IsOpenExistingTrades'] ?? false),
            isRoundUpToMinimumSize: (bool) ($data['IsRoundUpToMinimumSize'] ?? false)
        );
    }
}