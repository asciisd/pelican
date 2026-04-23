<?php

namespace Mohanad\Copytrade\DTOs\Copier;

use JsonSerializable;

class CopySettingsDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $tradeSizeType,
        public readonly float $tradeSizeValue,
        public readonly ?bool $isOpenExistingTrades = null,
        public readonly ?bool $isRoundUpToMinimumSize = null,
        public readonly array $rawData = []
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            tradeSizeType: $data['TradeSizeType'] ?? $data['tradeSizeType'] ?? '',
            tradeSizeValue: (float) ($data['TradeSizeValue'] ?? $data['tradeSizeValue'] ?? 0),
            isOpenExistingTrades: $data['IsOpenExistingTrades'] ?? $data['isOpenExistingTrades'] ?? null,
            isRoundUpToMinimumSize: $data['IsRoundUpToMinimumSize'] ?? $data['isRoundUpToMinimumSize'] ?? null,
            rawData: $data
        );
    }

    public function toArray(): array
    {
        return [
            'tradeSizeType' => $this->tradeSizeType,
            'tradeSizeValue' => $this->tradeSizeValue,
            'isOpenExistingTrades' => $this->isOpenExistingTrades,
            'isRoundUpToMinimumSize' => $this->isRoundUpToMinimumSize,
            'raw_data' => $this->rawData,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}