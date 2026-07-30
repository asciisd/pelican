<?php

namespace Asciisd\Copytrade\Contracts;

use Asciisd\Copytrade\DTOs\Auth\AuthorizationRequestDTO;
use Asciisd\Copytrade\DTOs\Auth\TokenDTO;

interface AuthServiceInterface
{
    /**
     * Log a user in with their credentials and return the issued token.
     *
     * Drives the identity server's hosted-login Authorization Code + PKCE
     * flow entirely server-side, so callers only need an email and password.
     */
    public function login(string $email, string $password): TokenDTO;

    /**
     * Create a Pelican OAuth2 Authorization Code request with PKCE for
     * browser-based (redirect) login flows.
     */
    public function authorizationRequest(?string $redirectUri = null, ?string $state = null): AuthorizationRequestDTO;

    /**
     * Exchange an authorization code for an access token.
     */
    public function exchangeCode(
        string $code,
        string $codeVerifier,
        ?string $redirectUri = null,
    ): TokenDTO;

    /**
     * Exchange a refresh token for a new access token.
     */
    public function refresh(string $refreshToken): TokenDTO;

    /**
     * Revoke an access or refresh token.
     */
    public function revoke(string $token, string $tokenTypeHint = 'access_token'): void;
}
