<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use InvalidArgumentException;

class CopyStrategyRequest
{
    public function __construct(
        public readonly string $tradeSizeType,
        public readonly float $tradeSizeValue,
        public readonly bool $isOpenExistingTrades = true,
        public readonly bool $isRoundUpToMinimumSize = true
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
        if (empty($data['tradeSizeType']) && empty($data['TradeSizeType'])) {
            throw new InvalidArgumentException('Trade size type is required');
        }

        if (! isset($data['tradeSizeValue']) && ! isset($data['TradeSizeValue'])) {
            throw new InvalidArgumentException('Trade size value is required');
        }

        return new self(
            tradeSizeType: $data['tradeSizeType'] ?? $data['TradeSizeType'],
            tradeSizeValue: (float) ($data['tradeSizeValue'] ?? $data['TradeSizeValue']),
            isOpenExistingTrades: $data['isOpenExistingTrades'] ?? $data['IsOpenExistingTrades'] ?? true,
            isRoundUpToMinimumSize: $data['isRoundUpToMinimumSize'] ?? $data['IsRoundUpToMinimumSize'] ?? true
        );
    }
}