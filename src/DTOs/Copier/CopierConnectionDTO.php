<?php

namespace Mohanad\Copytrade\DTOs\Copier;

use JsonSerializable;

class CopierConnectionDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $brokerCode,
        public readonly string $serverCode,
        public readonly string $username,
        public readonly string $password
    ) {}

    public function toArray(): array
    {
        return [
            'brokerCode' => $this->brokerCode,
            'serverCode' => $this->serverCode,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            brokerCode: $data['brokerCode'] ?? $data['broker_code'] ?? '',
            serverCode: $data['serverCode'] ?? $data['server_code'] ?? '',
            username: $data['username'] ?? '',
            password: $data['password'] ?? ''
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
