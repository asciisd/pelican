<?php

namespace Asciisd\Copytrade\DTOs\Auth;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use Carbon\CarbonImmutable;
use JsonSerializable;

class TokenDTO implements JsonSerializable
{
    use SerializesToArray;

    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly int $expiresIn = 3600,
        public readonly string $tokenType = 'Bearer',
        public readonly ?string $scope = null,
        public readonly ?CarbonImmutable $expiresAt = null,
    ) {}

    /**
     * Create from an OAuth2 token endpoint response.
     */
    public static function fromResponse(array $data): self
    {
        $expiresIn = (int) ($data['expires_in'] ?? 3600);

        return new self(
            accessToken: $data['access_token'] ?? '',
            refreshToken: $data['refresh_token'] ?? null,
            expiresIn: $expiresIn,
            tokenType: $data['token_type'] ?? 'Bearer',
            scope: $data['scope'] ?? null,
            expiresAt: CarbonImmutable::now()->addSeconds($expiresIn),
        );
    }

    /**
     * Rehydrate from a cached array (produced by toArray()).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: $data['access_token'] ?? '',
            refreshToken: $data['refresh_token'] ?? null,
            expiresIn: (int) ($data['expires_in'] ?? 3600),
            tokenType: $data['token_type'] ?? 'Bearer',
            scope: $data['scope'] ?? null,
            expiresAt: isset($data['expires_at'])
                ? CarbonImmutable::parse($data['expires_at'])
                : null,
        );
    }

    /**
     * Determine whether the access token is expired.
     *
     * @param  int  $buffer  Seconds of safety margin before actual expiry.
     */
    public function isExpired(int $buffer = 0): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return CarbonImmutable::now()->addSeconds($buffer)->greaterThanOrEqualTo($this->expiresAt);
    }

    /**
     * Determine whether this token can be refreshed.
     */
    public function canRefresh(): bool
    {
        return ! empty($this->refreshToken);
    }

    /**
     * Convert to array (safe for cache serialization).
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
            'token_type' => $this->tokenType,
            'scope' => $this->scope,
            'expires_at' => $this->expiresAt?->toIso8601String(),
        ];
    }
}
