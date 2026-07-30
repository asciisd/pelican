<?php

namespace Asciisd\Copytrade\DTOs\Auth;

use Asciisd\Copytrade\DTOs\Concerns\SerializesToArray;
use JsonSerializable;

final class AuthorizationRequestDTO implements JsonSerializable
{
    use SerializesToArray;

    public function __construct(
        public readonly string $authorizationUrl,
        public readonly string $state,
        public readonly string $codeVerifier,
        public readonly string $redirectUri,
    ) {}

    /**
     * Convert the authorization request to an array.
     */
    public function toArray(): array
    {
        return [
            'authorization_url' => $this->authorizationUrl,
            'state' => $this->state,
            'code_verifier' => $this->codeVerifier,
            'redirect_uri' => $this->redirectUri,
        ];
    }
}
