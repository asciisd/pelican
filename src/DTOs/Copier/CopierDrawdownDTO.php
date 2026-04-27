<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use JsonSerializable;

class CopierDrawdownDTO implements JsonSerializable
{
    public function __construct(
        public readonly float $currentLevel,
        public readonly float $softStopLevel,
        public readonly float $hardStopLevel
    ) {}

    public function toArray(): array
    {
        return [
            'currentLevel' => $this->currentLevel,
            'softStopLevel' => $this->softStopLevel,
            'hardStopLevel' => $this->hardStopLevel,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            currentLevel: (float) ($data['currentLevel'] ?? $data['current_level'] ?? 0),
            softStopLevel: (float) ($data['softStopLevel'] ?? $data['soft_stop_level'] ?? 0),
            hardStopLevel: (float) ($data['hardStopLevel'] ?? $data['hard_stop_level'] ?? 0)
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}