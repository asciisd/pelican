<?php

namespace Asciisd\Copytrade\DTOs\Copier;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use InvalidArgumentException;
use JsonSerializable;

class CopierConnectionDTO implements JsonSerializable
{
    use SerializesToArray;

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
        if (empty($data['brokerCode']) && empty($data['broker_code'])) {
            throw new InvalidArgumentException('Broker code is required and cannot be empty');
        }

        if (empty($data['serverCode']) && empty($data['server_code'])) {
            throw new InvalidArgumentException('Server code is required and cannot be empty');
        }

        if (empty($data['username'])) {
            throw new InvalidArgumentException('Username is required and cannot be empty');
        }

        if (empty($data['password'])) {
            throw new InvalidArgumentException('Password is required and cannot be empty');
        }

        return new self(
            brokerCode: $data['brokerCode'] ?? $data['broker_code'],
            serverCode: $data['serverCode'] ?? $data['server_code'],
            username: $data['username'],
            password: $data['password']
        );
    }
}