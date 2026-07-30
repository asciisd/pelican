# Pelican Laravel SDK

A modern, clean Laravel package for integrating with the CopyTrade (Pelican) API. Built with SOLID principles, type-safety, and developer experience in mind.

## Features

- **Full API coverage** — every endpoint in the CopyTrade API collection is available as a typed service method
- **Built-in OAuth2 login** — Authorization Code with PKCE, driven entirely server-side (no browser needed)
- **Type-safe DTOs** — fully typed data transfer objects for all API responses
- **Interface-based services** — easy mocking and testing
- **Laravel-native** — service container bindings, facade, config publishing, and package auto-discovery
- **Comprehensive error handling** — specific exceptions for different error types

## Requirements

- PHP 8.2 or higher
- Laravel 12.0 or 13.0

## Installation

Install the package via Composer:

```bash
composer require asciisd/pelican
```

Until the package is published on Packagist, you can install it directly from GitHub by adding the repository to your project's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/asciisd/pelican.git"
    }
  ],
  "require": {
    "asciisd/pelican": "dev-main"
  }
}
```

The package is auto-discovered by Laravel — no manual provider registration needed.

## Configuration

Publish the configuration file (optional — sensible defaults are provided):

```bash
php artisan vendor:publish --tag=copytrade-config
```

### Environment Variables

```env
COPYTRADE_BASE_URI=https://papi.copy-trade.io
COPYTRADE_IDENTITY_URI=https://identity.copy-trade.io
COPYTRADE_ASSET_URI=https://assets.copy-trade.io
COPYTRADE_CLIENT_ID=pelican

# Optional — defaults to "{client_id}://authenticated" (pelican://authenticated),
# which is what the credential-based login() requires. Only set this if you use
# the browser-based redirect flow with your own registered redirect URI.
COPYTRADE_CALLBACK_URL=

# Optional — a pre-issued access token applied to every service automatically
COPYTRADE_ACCESS_TOKEN=

# OAuth2 Authorization Code with PKCE
COPYTRADE_SCOPES="openid profile email copytrade"
COPYTRADE_CLIENT_SECRET=              # leave empty for the public Pelican client

# HTTP client
COPYTRADE_TIMEOUT=30
```

## Quick Start

```php
use Asciisd\Copytrade\Facades\Copytrade;

// 1. Log in and get a token
$token = Copytrade::auth()->login('user@example.com', 'secret-password');

// 2. Scope the SDK to that token
$copytrade = Copytrade::withToken($token->accessToken);

// 3. Call the API through the domain services
$user       = $copytrade->profiles()->getUserInfo();
$profile    = $copytrade->profiles()->getProfile($user->profileId);
$copiers    = $copytrade->copiers()->getCopiers($user->profileId);
$strategies = $copytrade->strategies()->getStrategies($user->profileId);
```

Every API area is exposed as a service off the facade:

| Accessor                  | Service           | Responsibility                                          |
| ------------------------- | ----------------- | ------------------------------------------------------- |
| `Copytrade::auth()`       | `AuthService`     | OAuth2 login, token exchange, refresh, revoke           |
| `Copytrade::profiles()`   | `ProfileService`  | User info and profile management                        |
| `Copytrade::servers()`    | `ServerService`   | Available trading servers                               |
| `Copytrade::strategies()` | `StrategyService` | Strategies, stats, search, signals, images              |
| `Copytrade::copiers()`    | `CopierService`   | Copiers, copy settings, copier signals, images          |
| `Copytrade::sections()`   | `SectionService`  | Discover sections                                       |

## Authentication

The CopyTrade API uses the OAuth2 Authorization Code flow with PKCE. The package
drives the whole flow for you — log in with an email and password and get a
token back in one call.

### Login with credentials (recommended)

```php
use Asciisd\Copytrade\Facades\Copytrade;

$token = Copytrade::auth()->login('user@example.com', 'secret-password');

$token->accessToken;   // "eyJhbGciOi..." — use this for API calls
$token->refreshToken;  // used to renew the access token
$token->expiresIn;     // lifetime in seconds (e.g. 3600)
$token->expiresAt;     // CarbonImmutable expiry timestamp
$token->tokenType;     // "Bearer"
```

Behind the scenes the package walks the Pelican identity server's hosted login
page server-side (authorize → login form → authorization code → token
exchange), so no browser, iframe, or callback route is needed.

A failed login (wrong credentials, misconfigured client, etc.) throws an
`Asciisd\Copytrade\Exceptions\AuthenticationException`:

```php
use Asciisd\Copytrade\Exceptions\AuthenticationException;

try {
    $token = Copytrade::auth()->login($email, $password);
} catch (AuthenticationException $e) {
    // e.g. "Login failed: invalid email or password."
    report($e);
}
```

### Storing and reusing the token

`TokenDTO` serializes cleanly, so you can cache it and refresh it when it
expires:

```php
use Asciisd\Copytrade\DTOs\Auth\TokenDTO;
use Illuminate\Support\Facades\Cache;

$token = Cache::get('copytrade_token')
    ? TokenDTO::fromArray(Cache::get('copytrade_token'))
    : null;

if ($token === null) {
    $token = Copytrade::auth()->login($email, $password);
} elseif ($token->isExpired(buffer: 60)) {
    $token = $token->canRefresh()
        ? Copytrade::auth()->refresh($token->refreshToken)
        : Copytrade::auth()->login($email, $password);
}

Cache::put('copytrade_token', $token->toArray(), $token->expiresAt);
```

Revoke a token when you are done with it:

```php
Copytrade::auth()->revoke($token->accessToken);
Copytrade::auth()->revoke($token->refreshToken, 'refresh_token');
```

### Browser-based login (advanced)

If you prefer to send the user to Pelican's hosted login page in their own
browser, generate an authorization request, store its state and PKCE verifier
in the session, and redirect:

```php
use Asciisd\Copytrade\Facades\Copytrade;

$authorization = Copytrade::auth()->authorizationRequest(
    redirectUri: 'https://your-app.example.com/copytrade/callback',
);

session([
    'copytrade_oauth_state' => $authorization->state,
    'copytrade_code_verifier' => $authorization->codeVerifier,
]);

return redirect()->away($authorization->authorizationUrl);
```

Then validate the returned state on your callback route before exchanging the
one-time authorization code:

```php
use Illuminate\Http\Request;

public function callback(Request $request)
{
    abort_unless(
        hash_equals(
            (string) $request->session()->pull('copytrade_oauth_state'),
            (string) $request->query('state'),
        ),
        403,
        'Invalid OAuth state.',
    );

    $token = Copytrade::auth()->exchangeCode(
        code: (string) $request->query('code'),
        codeVerifier: (string) $request->session()->pull('copytrade_code_verifier'),
        redirectUri: 'https://your-app.example.com/copytrade/callback',
    );

    $request->session()->put('copytrade_access_token', $token->accessToken);

    return redirect('/dashboard');
}
```

The redirect URI must exactly match a redirect URI registered for the Pelican
OAuth client. Note: the default public `pelican` client only allows its custom
scheme callback (`pelican://authenticated`), which is why the credential-based
`login()` above is the recommended path.

### Token scoping

`withToken()` returns token-scoped copies of the services and never mutates
the shared singletons, so it is safe to work with several accounts side by
side:

```php
$accountA = Copytrade::withToken($tokenA->accessToken);
$accountB = Copytrade::withToken($tokenB->accessToken);

$profileA = $accountA->profiles()->getProfile($profileIdA);
$profileB = $accountB->profiles()->getProfile($profileIdB);
```

If `COPYTRADE_ACCESS_TOKEN` is set, that token is applied to every service
automatically and `withToken()` is only needed for overrides.

## API Reference

### ProfileService — `Copytrade::profiles()`

Manage user profiles and account information.

| Method                             | Returns       | API Endpoint                        |
| ---------------------------------- | ------------- | ----------------------------------- |
| `getUserInfo()`                    | `UserInfoDTO` | `GET {identity}/connect/userinfo`   |
| `getProfile($profileId)`           | `ProfileDTO`  | `GET /api/profiles/{id}`            |
| `updateProfile($profileId, $data)` | `ProfileDTO`  | `PUT /api/profiles/{id}`            |

```php
// The user info response contains your profile ID
$user = Copytrade::withToken($accessToken)->profiles()->getUserInfo();

$profile = Copytrade::withToken($accessToken)->profiles()->getProfile($user->profileId);

$updated = Copytrade::withToken($accessToken)->profiles()->updateProfile($user->profileId, [
    'name' => 'New Name',          // optional, string
    'riskProfile' => 'Moderate',   // optional, string
    'countryCode' => 'EG',         // optional, ISO country code
]);
```

### ServerService — `Copytrade::servers()`

| Method         | Returns       | API Endpoint       |
| -------------- | ------------- | ------------------ |
| `getServers()` | `ServerDTO[]` | `GET /api/servers` |

```php
$servers = Copytrade::withToken($accessToken)->servers()->getServers();
```

Use the returned server codes when creating copier or strategy connections.

### StrategyService — `Copytrade::strategies()`

Manage trading strategies, stats, search, signals, and strategy images.

| Method                                                                  | Returns               | API Endpoint                                                     |
| ----------------------------------------------------------------------- | --------------------- | ----------------------------------------------------------------- |
| `getStrategies($profileId)`                                             | `StrategyDTO[]`       | `GET /api/profiles/{id}/strategies`                               |
| `addStrategy($profileId, $data)`                                        | `StrategyDTO`         | `POST /api/profiles/{id}/strategies`                              |
| `updateStrategy($profileId, $strategyId, $data)`                        | `StrategyDTO`         | `PUT /api/profiles/{id}/strategies/{strategyId}`                  |
| `getStrategyStats($strategyId)`                                         | `StrategyStatsDTO`    | `GET /api/strategies/{id}/stats`                                  |
| `searchStrategies($filter)`                                             | `SearchStrategyDTO[]` | `GET /api/strategies?filter=`                                     |
| `getStrategyCopiers($strategyId)`                                       | `CopierDTO[]`         | `GET /api/strategies/{id}/copiers`                                |
| `getStrategyOpenSignals($strategyId)`                                   | `SignalDTO[]`         | `GET /api/strategies/{id}/signals/open`                           |
| `getStrategyClosedSignals($strategyId, $startDate, $endDate)`           | `SignalDTO[]`         | `GET /api/strategies/{id}/signals/closed?startDate=&endDate=`     |
| `uploadStrategyImage($profileId, $strategyId, $fileContent, $filename)` | `array`               | `PUT /api/profiles/{id}/strategies/{strategyId}/image`            |
| `getStrategyImageUrl($strategyId)`                                      | `string`              | `{assets}/images/strategies/thumbnails/{id}` (URL builder)        |

```php
$strategies = Copytrade::withToken($accessToken)->strategies();

// Create a strategy (a master account whose trades will be copied)
$strategy = $strategies->addStrategy($profileId, [
    'name' => 'My Trading Strategy',    // required, string
    'riskProfile' => 'Moderate',        // required, string
    'fee' => 15.0,                      // required, float (percentage)
    'connection' => [
        'brokerCode' => 'XM',           // required, string
        'serverCode' => 'XM-Real',      // required, string
        'username' => '12345678',       // required, string (MT login)
        'password' => 'MySecurePass',   // required, string (MT password)
    ],
]);

// Discover strategies
$results = $strategies->searchStrategies('forex');
$stats = $strategies->getStrategyStats($strategy->id);

// Signal history
$open = $strategies->getStrategyOpenSignals($strategy->id);
$closed = $strategies->getStrategyClosedSignals($strategy->id, '2024-01-01', '2024-01-31');

// Images
$strategies->uploadStrategyImage($profileId, $strategy->id, file_get_contents($path), 'logo.png');
$thumbnailUrl = $strategies->getStrategyImageUrl($strategy->id); // public URL, no auth needed
```

### CopierService — `Copytrade::copiers()`

Manage copiers (follower accounts), copy settings, copier signals, and copier images.

| Method                                                              | Returns           | API Endpoint                                                        |
| ------------------------------------------------------------------- | ----------------- | -------------------------------------------------------------------- |
| `getCopiers($profileId)`                                            | `CopierDTO[]`     | `GET /api/profiles/{id}/copiers`                                     |
| `addCopier($profileId, $data)`                                      | `CopierDTO`       | `POST /api/profiles/{id}/copiers`                                    |
| `updateCopier($profileId, $copierId, $data)`                        | `CopierDTO`       | `PUT /api/profiles/{id}/copiers/{copierId}`                          |
| `removeCopier($profileId, $copierId)`                               | `bool`            | `DELETE /api/profiles/{id}/copiers/{copierId}`                       |
| `getCopierStats($copierId)`                                         | `CopierStatsDTO`  | `GET /api/copiers/{id}/stats`                                        |
| `getCopierStrategies($copierId)`                                    | `StrategyDTO[]`   | `GET /api/copiers/{id}/strategies`                                   |
| `copyStrategy($copierId, $strategyId, $data)`                       | `CopySettingsDTO` | `POST /api/copiers/{id}/strategies/{strategyId}/copy-settings`       |
| `getCopySettings($copierId, $strategyId)`                           | `CopySettingsDTO` | `GET /api/copiers/{id}/strategies/{strategyId}/copy-settings`        |
| `updateCopySettings($copierId, $strategyId, $data)`                 | `CopySettingsDTO` | `PUT /api/copiers/{id}/strategies/{strategyId}/copy-settings`        |
| `stopCopying($copierId, $strategyId)`                               | `bool`            | `DELETE /api/copiers/{id}/strategies/{strategyId}/copy-settings`     |
| `getCopierOpenSignals($copierId)`                                   | `SignalDTO[]`     | `GET /api/copiers/{id}/signals/open`                                 |
| `getCopierClosedSignals($copierId, $startDate, $endDate)`           | `SignalDTO[]`     | `GET /api/copiers/{id}/signals/closed?startDate=&endDate=`           |
| `getMissedSignals($profileId, $copierId)`                           | `SignalDTO[]`     | `GET /api/profiles/{id}/copiers/{copierId}/signals/missed`           |
| `uploadCopierImage($profileId, $copierId, $fileContent, $filename)` | `array`           | `PUT /api/profiles/{id}/copiers/{copierId}/image`                    |
| `getCopierImageUrl($copierId)`                                      | `string`          | `{assets}/images/copiers/thumbnails/{id}` (URL builder)              |

```php
$copiers = Copytrade::withToken($accessToken)->copiers();

// Create a copier (a follower account that copies strategies)
$copier = $copiers->addCopier($profileId, [
    'name' => 'My Copier Account',      // required, string
    'connection' => [
        'brokerCode' => 'XM',           // required, string
        'serverCode' => 'XM-Real',      // required, string
        'username' => '87654321',       // required, string (MT login)
        'password' => 'MyPassword',     // required, string (MT password)
    ],
    'drawdown' => [
        'currentLevel' => 0.0,          // required, float
        'softStopLevel' => 15.0,        // required, float (percentage)
        'hardStopLevel' => 25.0,        // required, float (percentage)
    ],
]);

// Start copying a strategy
$settings = $copiers->copyStrategy($copier->id, $strategyId, [
    'TradeSizeType' => 'Fixed',         // required, string (Fixed, Multiplier, Balance)
    'TradeSizeValue' => 0.01,           // required, float
    'IsOpenExistingTrades' => true,     // optional, bool (default: false)
    'IsRoundUpToMinimumSize' => false,  // optional, bool (default: false)
]);

// Adjust or stop copying
$copiers->updateCopySettings($copier->id, $strategyId, [
    'TradeSizeType' => 'Multiplier',
    'TradeSizeValue' => 2.0,
    'IsRoundUpToMinimumSize' => true,
]);
$copiers->stopCopying($copier->id, $strategyId);

// Monitor the copier
$stats = $copiers->getCopierStats($copier->id);
$copied = $copiers->getCopierStrategies($copier->id);
$open = $copiers->getCopierOpenSignals($copier->id);
$closed = $copiers->getCopierClosedSignals($copier->id, '2024-05-01', '2024-05-28');
$missed = $copiers->getMissedSignals($profileId, $copier->id);
```

### SectionService — `Copytrade::sections()`

Retrieve the "Discover" sections used to browse featured strategies.

| Method              | Returns        | API Endpoint               |
| ------------------- | -------------- | -------------------------- |
| `getSections()`     | `SectionDTO[]` | `GET /api/discover/`       |
| `getSection($code)` | `SectionDTO`   | `GET /api/discover/{code}` |

```php
$sections = Copytrade::withToken($accessToken)->sections()->getSections();

$spotlight = Copytrade::withToken($accessToken)->sections()->getSection('Spotlight');
```

## Endpoint Coverage

Every request in the CopyTrade API Postman collection maps to a package method:

| Postman Request                              | Package Method                                              |
| -------------------------------------------- | ----------------------------------------------------------- |
| Get userInfo                                 | `profiles()->getUserInfo()`                                 |
| Get profile                                  | `profiles()->getProfile($profileId)`                        |
| Update profile                               | `profiles()->updateProfile($profileId, $data)`              |
| Get list of available servers                | `servers()->getServers()`                                   |
| Get copiers connected to profile             | `copiers()->getCopiers($profileId)`                         |
| Add copier to a profile                      | `copiers()->addCopier($profileId, $data)`                   |
| Update copier                                | `copiers()->updateCopier($profileId, $copierId, $data)`     |
| Remove copier                                | `copiers()->removeCopier($profileId, $copierId)`            |
| Get copiers stats                            | `copiers()->getCopierStats($copierId)`                      |
| Get strategies being copied by a copier      | `copiers()->getCopierStrategies($copierId)`                 |
| Copy strategy                                | `copiers()->copyStrategy($copierId, $strategyId, $data)`    |
| Get copied strategy settings                 | `copiers()->getCopySettings($copierId, $strategyId)`        |
| Update copied strategy settings              | `copiers()->updateCopySettings($copierId, $strategyId, $data)` |
| Stop copying a strategy                      | `copiers()->stopCopying($copierId, $strategyId)`            |
| Get open copied signals                      | `copiers()->getCopierOpenSignals($copierId)`                |
| Get closed copied signals                    | `copiers()->getCopierClosedSignals($copierId, $start, $end)` |
| Get missed signals                           | `copiers()->getMissedSignals($profileId, $copierId)`        |
| Upload copier image                          | `copiers()->uploadCopierImage($profileId, $copierId, $file, $name)` |
| Get copier image                             | `copiers()->getCopierImageUrl($copierId)`                   |
| Get strategies connected to profile          | `strategies()->getStrategies($profileId)`                   |
| Add strategy to profile                      | `strategies()->addStrategy($profileId, $data)`              |
| Update strategy                              | `strategies()->updateStrategy($profileId, $strategyId, $data)` |
| Get strategy stats                           | `strategies()->getStrategyStats($strategyId)`               |
| Search for a strategy                        | `strategies()->searchStrategies($filter)`                   |
| Get copiers that are copying a strategy      | `strategies()->getStrategyCopiers($strategyId)`             |
| Get strategy open signals                    | `strategies()->getStrategyOpenSignals($strategyId)`         |
| Get strategy closed signals                  | `strategies()->getStrategyClosedSignals($strategyId, $start, $end)` |
| Upload strategy image                        | `strategies()->uploadStrategyImage($profileId, $strategyId, $file, $name)` |
| Get strategy image                           | `strategies()->getStrategyImageUrl($strategyId)`            |
| Get discover sections                        | `sections()->getSections()`                                 |
| Get discover section                         | `sections()->getSection($code)`                             |

## Data Transfer Objects

All API responses are wrapped in DTOs. Every DTO exposes typed properties for
the common fields plus a `rawData` array with the complete, untouched API
response, and serializes with `toArray()` / `json_encode()`:

```php
$profile = Copytrade::withToken($accessToken)->profiles()->getProfile($profileId);

$profile->name;      // typed accessor
$profile->rawData;   // full raw API response
$profile->toArray(); // array representation
```

| DTO                 | Key Properties                                                        |
| ------------------- | --------------------------------------------------------------------- |
| `TokenDTO`          | `accessToken`, `refreshToken`, `expiresIn`, `expiresAt`, `tokenType`  |
| `UserInfoDTO`       | `profileId`                                                            |
| `ProfileDTO`        | `id`, `name`, `riskProfile`, `countryCode`                             |
| `ServerDTO`         | `id`, `name`                                                           |
| `StrategyDTO`       | `id`, `name`, `riskProfile`, `fee`, `connection`                       |
| `SearchStrategyDTO` | search result fields                                                   |
| `StrategyStatsDTO`  | strategy performance statistics                                        |
| `CopierDTO`         | `id`, `name`, `connection`, `drawdown`                                 |
| `CopierStatsDTO`    | copier performance statistics                                          |
| `CopySettingsDTO`   | trade size type/value and copy flags                                   |
| `SignalDTO`         | `rawData` (signal payload)                                             |
| `SectionDTO`        | `code`, `title`, `strategies`                                          |

## Error Handling

The package throws specific exception types, all extending `CopytradeException`:

| Exception                 | When                                  |
| ------------------------- | ------------------------------------- |
| `AuthenticationException` | Login/token failures, invalid tokens  |
| `NotFoundException`       | Resource not found                    |
| `ValidationException`     | Invalid request data                  |
| `RateLimitException`      | API rate limit exceeded               |
| `CopytradeException`      | Any other API error                   |

```php
use Asciisd\Copytrade\Exceptions\AuthenticationException;
use Asciisd\Copytrade\Exceptions\CopytradeException;
use Asciisd\Copytrade\Exceptions\NotFoundException;

try {
    $profile = Copytrade::withToken($accessToken)->profiles()->getProfile($profileId);
} catch (AuthenticationException $e) {
    // Invalid or expired access token — re-login or refresh
} catch (NotFoundException $e) {
    abort(404, 'Profile not found');
} catch (CopytradeException $e) {
    Log::error('CopyTrade API error', ['message' => $e->getMessage()]);
}
```

## Testing Your Application

All services are bound to interfaces in the container, so they are trivial to
mock in your application tests:

```php
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\DTOs\Profile\ProfileDTO;

$this->mock(ProfileServiceInterface::class, function ($mock) {
    $mock->shouldReceive('getProfile')
        ->with('profile-123')
        ->andReturn(ProfileDTO::fromResponse([
            'id' => 'profile-123',
            'name' => 'Test User',
        ]));
});
```

Because the services use Laravel's HTTP client internally, you can also fake
at the HTTP layer:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'papi.copy-trade.io/api/servers' => Http::response([
        ['id' => 'srv-1', 'name' => 'Demo Server'],
    ]),
]);
```

### Running the package test suite

```bash
composer test
```

## Architecture

```
src/
├── Config/
│   └── copytrade.php              # Configuration
├── Contracts/                     # Service interfaces
│   ├── AuthServiceInterface.php
│   ├── CopierServiceInterface.php
│   ├── ProfileServiceInterface.php
│   ├── SectionServiceInterface.php
│   ├── ServerServiceInterface.php
│   └── StrategyServiceInterface.php
├── DTOs/                          # Typed request/response objects
│   ├── Auth/
│   ├── Copier/
│   ├── Profile/
│   ├── Section/
│   ├── Server/
│   └── Strategy/
├── Exceptions/                    # Exception hierarchy
├── Facades/
│   └── Copytrade.php
├── Services/                      # Service implementations
│   ├── AbstractService.php        # Shared HTTP + token handling
│   ├── AuthService.php
│   ├── CopierService.php
│   ├── ProfileService.php
│   ├── SectionService.php
│   ├── ServerService.php
│   └── StrategyService.php
├── Copytrade.php                  # Main entry point behind the facade
└── CopytradeServiceProvider.php
```

Each service handles exactly one API domain, every service is consumed through
its interface, and token scoping is immutable (`withToken()` returns a copy),
so a token from one request can never leak into another.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
