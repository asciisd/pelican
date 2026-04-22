<?php

namespace Mohanad\Copytrade\DTOs\Strategy;

use InvalidArgumentException;
use JsonSerializable;

class StrategyConnectionDTO implements JsonSerializable
{
    public function __construct(
        public readonly string $brokerCode,
        public readonly string $serverCode,
        public readonly string $username,
        public readonly string $password
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['brokerCode'])) {
            throw new InvalidArgumentException('Broker code is required and cannot be empty');
        }

        if (empty($data['serverCode'])) {
            throw new InvalidArgumentException('Server code is required and cannot be empty');
        }

        if (empty($data['username'])) {
            throw new InvalidArgumentException('Username is required and cannot be empty');
        }

        if (empty($data['password'])) {
            throw new InvalidArgumentException('Password is required and cannot be empty');
        }

        return new self(
            brokerCode: $data['brokerCode'],
            serverCode: $data['serverCode'],
            username: $data['username'],
            password: $data['password']
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'brokerCode' => $this->brokerCode,
            'serverCode' => $this->serverCode,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}