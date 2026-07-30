<?php

namespace Asciisd\Copytrade\Services;

use Asciisd\Copytrade\Contracts\AuthServiceInterface;
use Asciisd\Copytrade\DTOs\Auth\AuthorizationRequestDTO;
use Asciisd\Copytrade\DTOs\Auth\TokenDTO;
use Asciisd\Copytrade\Exceptions\AuthenticationException;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AuthService extends AbstractService implements AuthServiceInterface
{
    protected const MAX_REDIRECTS = 10;

    public function __construct(
        string $identityUri,
        protected string $clientId,
        protected string $scopes,
        protected string $acrValues,
        protected string $callbackUrl,
        protected ?string $clientSecret = null,
        int $timeout = 120,
    ) {
        parent::__construct($identityUri, $timeout);
    }

    /**
     * {@inheritdoc}
     *
     * Reproduces what the identity server's hosted login popup does: request
     * authorization, walk to the hosted login page, submit the credentials,
     * capture the authorization code from the callback redirect, and exchange
     * it for an access token — all server-side, in a single call.
     *
     * @throws AuthenticationException on any step failure.
     */
    public function login(string $email, string $password): TokenDTO
    {
        $jar = new CookieJar;
        $codeVerifier = $this->randomUrlSafeString(32);
        $state = $this->randomUrlSafeString(16);

        $loginPageUrl = $this->requestAuthorization($jar, $codeVerifier, $state);
        $loginHtml = $this->fetchLoginPage($jar, $loginPageUrl);
        $code = $this->submitCredentials($jar, $loginPageUrl, $loginHtml, $email, $password, $state);

        return $this->exchangeCode($code, $codeVerifier);
    }

    /**
     * {@inheritdoc}
     */
    public function authorizationRequest(?string $redirectUri = null, ?string $state = null): AuthorizationRequestDTO
    {
        $redirectUri ??= $this->callbackUrl;
        $state ??= $this->randomUrlSafeString(32);
        $codeVerifier = $this->randomUrlSafeString(64);

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $this->scopes,
            'acr_values' => $this->acrValues,
            'state' => $state,
            'code_challenge' => $this->challengeFor($codeVerifier),
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return new AuthorizationRequestDTO(
            authorizationUrl: rtrim($this->baseUri, '/').'/connect/authorize?'.$query,
            state: $state,
            codeVerifier: $codeVerifier,
            redirectUri: $redirectUri,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function exchangeCode(
        string $code,
        string $codeVerifier,
        ?string $redirectUri = null,
    ): TokenDTO {
        $response = $this->postForm('/connect/token', $this->payload([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri ?? $this->callbackUrl,
            'code_verifier' => $codeVerifier,
        ]));

        return TokenDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh(string $refreshToken): TokenDTO
    {
        $response = $this->postForm('/connect/token', $this->payload([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]));

        return TokenDTO::fromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(string $token, string $tokenTypeHint = 'access_token'): void
    {
        $this->postForm('/connect/revocation', $this->payload([
            'token' => $token,
            'token_type_hint' => $tokenTypeHint,
        ]));
    }

    /**
     * Step 1: hit /connect/authorize and capture the redirect to the hosted
     * login page, returning that login URL.
     *
     * @throws AuthenticationException
     */
    protected function requestAuthorization(CookieJar $jar, string $codeVerifier, string $state): string
    {
        $response = $this->browser($jar)->get(rtrim($this->baseUri, '/').'/connect/authorize', [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'scope' => $this->scopes,
            'acr_values' => $this->acrValues,
            'redirect_uri' => $this->callbackUrl,
            'code_challenge' => $this->challengeFor($codeVerifier),
            'code_challenge_method' => 'S256',
            'state' => $state,
        ]);

        $location = $this->locationHeader($response);

        if ($location === null || ! str_contains($location, '/Account/Login')) {
            throw new AuthenticationException(
                'Unexpected response from the authorize endpoint. The client or scope may be misconfigured.',
                $response->status() ?: 401,
            );
        }

        return $this->absoluteUrl($location);
    }

    /**
     * Step 2: GET the hosted login page so its anti-forgery fields can be read.
     *
     * @throws AuthenticationException
     */
    protected function fetchLoginPage(CookieJar $jar, string $loginPageUrl): string
    {
        $response = $this->browser($jar, followRedirects: true)->get($loginPageUrl);

        if ($response->failed()) {
            throw new AuthenticationException(
                'Failed to load the identity login page.',
                $response->status() ?: 401,
            );
        }

        return $response->body();
    }

    /**
     * Step 3: POST the credentials and follow redirects until the identity
     * server sends us to the callback URL, returning the authorization code.
     *
     * @throws AuthenticationException
     */
    protected function submitCredentials(
        CookieJar $jar,
        string $loginPageUrl,
        string $loginHtml,
        string $email,
        string $password,
        string $expectedState,
    ): string {
        $response = $this->browser($jar)->asForm()->post($loginPageUrl, [
            '__RequestVerificationToken' => $this->extractField($loginHtml, '__RequestVerificationToken'),
            'ReturnUrl' => $this->extractField($loginHtml, 'ReturnUrl'),
            'Email' => $email,
            'Password' => $password,
            'Tenant' => $this->clientId,
            'button' => 'login',
        ]);

        $location = $this->locationHeader($response);

        if ($location === null) {
            throw new AuthenticationException('Login failed: invalid email or password.', 401);
        }

        return $this->followUntilCode($jar, $location, $expectedState);
    }

    /**
     * Follow the post-login redirect chain until the callback redirect, then
     * pull the authorization code out of it.
     *
     * @throws AuthenticationException
     */
    protected function followUntilCode(CookieJar $jar, string $location, string $expectedState): string
    {
        for ($hop = 0; $hop < static::MAX_REDIRECTS; $hop++) {
            $location = html_entity_decode($location, ENT_QUOTES | ENT_HTML5);

            if (str_starts_with($location, $this->callbackUrl)) {
                return $this->parseCode($location, $expectedState);
            }

            if (str_contains($location, '/home/error') || str_contains($location, '/Account/Login')) {
                throw new AuthenticationException(
                    'Login failed: the identity server rejected the credentials.',
                    401,
                );
            }

            $response = $this->browser($jar)->get($this->absoluteUrl($location));
            $next = $this->locationHeader($response);

            if ($next === null) {
                break;
            }

            $location = $next;
        }

        throw new AuthenticationException('Login flow did not return an authorization code.', 401);
    }

    /**
     * Extract and validate the authorization code from the callback URL.
     *
     * @throws AuthenticationException
     */
    protected function parseCode(string $callbackUrl, string $expectedState): string
    {
        parse_str((string) parse_url($callbackUrl, PHP_URL_QUERY), $params);

        if (empty($params['code'])) {
            throw new AuthenticationException('No authorization code present in the callback.', 401);
        }

        if (isset($params['state']) && $params['state'] !== $expectedState) {
            throw new AuthenticationException('State mismatch — possible CSRF, aborting.', 401);
        }

        return (string) $params['code'];
    }

    /**
     * A browser-like HTTP client that shares the given cookie jar and, by
     * default, does NOT auto-follow redirects (so Location can be read).
     */
    protected function browser(CookieJar $jar, bool $followRedirects = false): PendingRequest
    {
        return Http::withOptions([
            'cookies' => $jar,
            'allow_redirects' => $followRedirects
                ? ['max' => static::MAX_REDIRECTS, 'referer' => true]
                : false,
        ])->timeout($this->timeout);
    }

    /**
     * Read the Location header from a redirect response, if present.
     */
    protected function locationHeader($response): ?string
    {
        $location = $response->header('Location');

        return $location !== '' ? $location : null;
    }

    /**
     * Resolve a possibly relative redirect location against the identity URI.
     */
    protected function absoluteUrl(string $location): string
    {
        if (str_starts_with($location, 'http://') || str_starts_with($location, 'https://')) {
            return $location;
        }

        return rtrim($this->baseUri, '/').'/'.ltrim($location, '/');
    }

    /**
     * Extract a hidden input value from the login HTML by field name.
     *
     * The value is returned exactly as rendered (still HTML-entity encoded):
     * the identity server validates the posted ReturnUrl against the raw
     * string it issued, so decoding "&amp;" back to "&" makes the login fail.
     *
     * @throws AuthenticationException
     */
    protected function extractField(string $html, string $name): string
    {
        $pattern = '/name="'.preg_quote($name, '/').'"[^>]*value="([^"]*)"/i';

        if (preg_match($pattern, $html, $matches) !== 1) {
            // Some fields render value before name; try the reverse order too.
            $pattern = '/value="([^"]*)"[^>]*name="'.preg_quote($name, '/').'"/i';

            if (preg_match($pattern, $html, $matches) !== 1) {
                throw new AuthenticationException(
                    "Could not find the \"{$name}\" field on the login page.",
                    401,
                );
            }
        }

        return $matches[1];
    }

    /**
     * Merge the client credentials into a token endpoint payload.
     */
    protected function payload(array $data): array
    {
        $data['client_id'] = $this->clientId;

        if ($this->clientSecret !== null && $this->clientSecret !== '') {
            $data['client_secret'] = $this->clientSecret;
        }

        return $data;
    }

    /**
     * Generate a cryptographically secure, URL-safe value.
     */
    protected function randomUrlSafeString(int $bytes): string
    {
        return $this->base64UrlEncode(random_bytes($bytes));
    }

    /**
     * Derive the S256 PKCE code challenge for a verifier.
     */
    protected function challengeFor(string $codeVerifier): string
    {
        return $this->base64UrlEncode(hash('sha256', $codeVerifier, true));
    }

    /**
     * Encode binary data using unpadded base64url.
     */
    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * Send a form-encoded POST to the identity server.
     *
     * @throws AuthenticationException
     */
    protected function postForm(string $uri, array $payload): array
    {
        $response = Http::baseUrl($this->baseUri)
            ->timeout($this->timeout)
            ->acceptJson()
            ->asForm()
            ->post($uri, $payload);

        if ($response->failed()) {
            throw new AuthenticationException(
                $this->errorMessage($response),
                $response->status() ?: 401,
            );
        }

        $result = $response->json();

        return is_array($result) ? $result : [];
    }

    /**
     * Extract a meaningful error message from a failed token response.
     */
    protected function errorMessage($response): string
    {
        $body = $response->json();

        if (is_array($body)) {
            $description = $body['error_description'] ?? null;
            $error = $body['error'] ?? null;

            if ($description) {
                return $description;
            }

            if ($error) {
                return "Authentication failed: {$error}";
            }
        }

        return 'Authentication failed: '.$response->status();
    }
}
