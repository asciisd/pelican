<?php

use Asciisd\Copytrade\Contracts\AuthServiceInterface;
use Asciisd\Copytrade\DTOs\Auth\AuthorizationRequestDTO;
use Asciisd\Copytrade\DTOs\Auth\TokenDTO;
use Asciisd\Copytrade\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Http;

/**
 * Fake the identity server's hosted-login flow: authorize redirect, login
 * page, credential POST, and token endpoint.
 */
function fakeHostedLoginFlow(array $overrides = []): void
{
    Http::fake(function ($request) use ($overrides) {
        $url = $request->url();

        if (str_contains($url, '/connect/authorize/callback')) {
            return $overrides['callback'] ?? Http::response('', 302, [
                'Location' => 'pelican://authenticated?code=auth-code-123',
            ]);
        }

        if (str_contains($url, '/connect/authorize')) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            return $overrides['authorize'] ?? Http::response('', 302, [
                'Location' => '/Account/Login?ReturnUrl=%2Fconnect%2Fauthorize%3Fstate%3D'.($query['state'] ?? ''),
            ]);
        }

        if (str_contains($url, '/Account/Login') && $request->method() === 'GET') {
            return $overrides['login_page'] ?? Http::response(<<<'HTML'
                <form>
                    <input name="ReturnUrl" type="hidden" value="/connect/authorize?client_id=pelican&amp;state=abc" />
                    <input name="__RequestVerificationToken" type="hidden" value="anti-forgery-token" />
                </form>
                HTML, 200);
        }

        if (str_contains($url, '/Account/Login') && $request->method() === 'POST') {
            return $overrides['login_post'] ?? Http::response('', 302, [
                'Location' => '/connect/authorize/callback?state=pending',
            ]);
        }

        if (str_contains($url, '/connect/token')) {
            return $overrides['token'] ?? Http::response([
                'access_token' => 'access-123',
                'refresh_token' => 'refresh-123',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
                'scope' => 'openid profile email copytrade',
            ], 200);
        }

        return Http::response('Unexpected request: '.$url, 500);
    });
}

it('logs in with credentials and returns a token', function () {
    fakeHostedLoginFlow();

    $token = app(AuthServiceInterface::class)->login('user@example.com', 'secret');

    expect($token)->toBeInstanceOf(TokenDTO::class)
        ->and($token->accessToken)->toBe('access-123')
        ->and($token->refreshToken)->toBe('refresh-123')
        ->and($token->canRefresh())->toBeTrue()
        ->and($token->isExpired())->toBeFalse();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/connect/token')
            && $request['grant_type'] === 'authorization_code'
            && $request['code'] === 'auth-code-123'
            && $request['redirect_uri'] === 'pelican://authenticated'
            && $request['client_id'] === 'pelican'
            && ! empty($request['code_verifier']);
    });
});

it('submits the credentials with the anti-forgery fields from the login page', function () {
    fakeHostedLoginFlow();

    app(AuthServiceInterface::class)->login('user@example.com', 'secret');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/Account/Login')
            && $request->method() === 'POST'
            && $request['Email'] === 'user@example.com'
            && $request['Password'] === 'secret'
            && $request['Tenant'] === 'pelican'
            && $request['__RequestVerificationToken'] === 'anti-forgery-token'
            && $request['ReturnUrl'] === '/connect/authorize?client_id=pelican&amp;state=abc';
    });
});

it('throws when the credentials are rejected', function () {
    fakeHostedLoginFlow([
        // No Location header means the login page re-rendered with errors.
        'login_post' => Http::response('<form>Invalid credentials</form>', 200),
    ]);

    app(AuthServiceInterface::class)->login('user@example.com', 'wrong-password');
})->throws(AuthenticationException::class, 'invalid email or password');

it('throws when the post-login redirect loops back to the login page', function () {
    fakeHostedLoginFlow([
        'login_post' => Http::response('', 302, [
            'Location' => '/Account/Login?ReturnUrl=%2Fconnect%2Fauthorize',
        ]),
    ]);

    app(AuthServiceInterface::class)->login('user@example.com', 'wrong-password');
})->throws(AuthenticationException::class, 'rejected the credentials');

it('throws when the authorize endpoint does not redirect to the login page', function () {
    fakeHostedLoginFlow([
        'authorize' => Http::response(['error' => 'invalid_client'], 400),
    ]);

    app(AuthServiceInterface::class)->login('user@example.com', 'secret');
})->throws(AuthenticationException::class, 'authorize endpoint');

it('creates a Pelican authorization URL with PKCE', function () {
    $request = app(AuthServiceInterface::class)->authorizationRequest(
        redirectUri: 'https://app.example.com/oauth/callback',
        state: 'known-state',
    );

    parse_str((string) parse_url($request->authorizationUrl, PHP_URL_QUERY), $query);

    expect($request)->toBeInstanceOf(AuthorizationRequestDTO::class)
        ->and($request->authorizationUrl)->toStartWith('https://identity.copy-trade.io/connect/authorize?')
        ->and($request->state)->toBe('known-state')
        ->and($request->redirectUri)->toBe('https://app.example.com/oauth/callback')
        ->and(strlen($request->codeVerifier))->toBeGreaterThanOrEqual(43)
        ->and($query['client_id'])->toBe('pelican')
        ->and($query['redirect_uri'])->toBe('https://app.example.com/oauth/callback')
        ->and($query['response_type'])->toBe('code')
        ->and($query['scope'])->toBe('openid profile email copytrade')
        ->and($query['acr_values'])->toBe('tenant:pelican')
        ->and($query['state'])->toBe('known-state')
        ->and($query['code_challenge_method'])->toBe('S256')
        ->and($query['code_challenge'])->toBe(
            rtrim(strtr(base64_encode(hash('sha256', $request->codeVerifier, true)), '+/', '-_'), '=')
        );
});

it('uses configured callback URL and generates a secure state', function () {
    $request = app(AuthServiceInterface::class)->authorizationRequest();

    expect($request->redirectUri)->toBe('pelican://authenticated')
        ->and($request->state)->not->toBeEmpty()
        ->and($request->state)->toMatch('/^[A-Za-z0-9_-]+$/');
});

it('exchanges an authorization code for a token', function () {
    Http::fake([
        'identity.copy-trade.io/connect/token' => Http::response([
            'access_token' => 'access-123',
            'refresh_token' => 'refresh-123',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
            'scope' => 'openid email profile offline_access',
        ], 200),
    ]);

    $token = app(AuthServiceInterface::class)->exchangeCode(
        code: 'authorization-code',
        codeVerifier: 'pkce-verifier',
        redirectUri: 'https://app.example.com/oauth/callback',
    );

    expect($token)->toBeInstanceOf(TokenDTO::class)
        ->and($token->accessToken)->toBe('access-123')
        ->and($token->refreshToken)->toBe('refresh-123')
        ->and($token->canRefresh())->toBeTrue()
        ->and($token->isExpired())->toBeFalse();
});

it('sends the correct authorization code grant parameters', function () {
    Http::fake([
        'identity.copy-trade.io/connect/token' => Http::response([
            'access_token' => 'access-123',
            'expires_in' => 3600,
        ], 200),
    ]);

    app(AuthServiceInterface::class)->exchangeCode(
        code: 'authorization-code',
        codeVerifier: 'pkce-verifier',
        redirectUri: 'https://app.example.com/oauth/callback',
    );

    Http::assertSent(function ($request) {
        return $request->url() === 'https://identity.copy-trade.io/connect/token'
            && $request['grant_type'] === 'authorization_code'
            && $request['code'] === 'authorization-code'
            && $request['code_verifier'] === 'pkce-verifier'
            && $request['redirect_uri'] === 'https://app.example.com/oauth/callback'
            && $request['client_id'] === 'pelican'
            && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded');
    });
});

it('throws an authentication exception when code exchange fails', function () {
    Http::fake([
        'identity.copy-trade.io/connect/token' => Http::response([
            'error' => 'invalid_grant',
            'error_description' => 'Invalid authorization code',
        ], 400),
    ]);

    app(AuthServiceInterface::class)->exchangeCode('invalid-code', 'pkce-verifier');
})->throws(AuthenticationException::class, 'Invalid authorization code');

it('exchanges a refresh token for a new access token', function () {
    Http::fake([
        'identity.copy-trade.io/connect/token' => Http::response([
            'access_token' => 'access-new',
            'refresh_token' => 'refresh-new',
            'expires_in' => 3600,
        ], 200),
    ]);

    $token = app(AuthServiceInterface::class)->refresh('refresh-old');

    expect($token->accessToken)->toBe('access-new');

    Http::assertSent(function ($request) {
        return $request['grant_type'] === 'refresh_token'
            && $request['refresh_token'] === 'refresh-old'
            && $request['client_id'] === 'pelican';
    });
});
