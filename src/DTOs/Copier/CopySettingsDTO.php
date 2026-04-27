<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use JsonSerializable;

class CopySettingsDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $tradeSizeType,
        public readonly float $tradeSizeValue,
        public readonly bool $isRoundUpToMinimumSize,
        public readonly ?bool $isOpenExistingTrades = null,
        public readonly array $rawData = []
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            tradeSizeType: $data['TradeSizeType'] ?? $data['tradeSizeType'] ?? '',
            tradeSizeValue: (float) ($data['TradeSizeValue'] ?? $data['tradeSizeValue'] ?? 0),
            isRoundUpToMinimumSize: (bool) ($data['IsRoundUpToMinimumSize'] ?? $data['isRoundUpToMinimumSize'] ?? false),
            isOpenExistingTrades: isset($data['IsOpenExistingTrades']) || isset($data['isOpenExistingTrades'])
                ? (bool) ($data['IsOpenExistingTrades'] ?? $data['isOpenExistingTrades'])
                : null,
            rawData: $data
        );
    }

    public function toArray(): array
    {
        $array = [
            'trade_size_type' => $this->tradeSizeType,
            'trade_size_value' => $this->tradeSizeValue,
            'is_round_up_to_minimum_size' => $this->isRoundUpToMinimumSize,
            'raw_data' => $this->rawData,
        ];

        if ($this->isOpenExistingTrades !== null) {
            $array['is_open_existing_trades'] = $this->isOpenExistingTrades;
        }

        return $array;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}