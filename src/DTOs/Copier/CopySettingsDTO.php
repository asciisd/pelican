<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use JsonSerializable;

class CopySettingsDTO implements JsonSerializable
{
    use SerializesToArray;

    public function __construct(
        public readonly string $tradeSizeType,
        public readonly float $tradeSizeValue,
        public readonly bool $isRoundUpToMinimumSize,
        public readonly ?bool $isOpenExistingTrades = null,
        public readonly array $rawData = []
    ) {}

    public static function fromResponse(array $data): self
    {
        $isOpenExistingTrades = self::value($data, 'IsOpenExistingTrades', 'isOpenExistingTrades');

        return new self(
            tradeSizeType: self::value($data, 'TradeSizeType', 'tradeSizeType') ?? '',
            tradeSizeValue: (float) (self::value($data, 'TradeSizeValue', 'tradeSizeValue') ?? 0),
            isRoundUpToMinimumSize: (bool) (self::value($data, 'IsRoundUpToMinimumSize', 'isRoundUpToMinimumSize') ?? false),
            isOpenExistingTrades: $isOpenExistingTrades !== null ? (bool) $isOpenExistingTrades : null,
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
}
