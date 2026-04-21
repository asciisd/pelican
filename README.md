# CopyTrade Laravel SDK

A clean, SOLID-principled Laravel package for integrating with the CopyTrade API.

## Features

- ✅ Clean Architecture with SOLID principles
- ✅ Type-safe DTOs for all API responses
- ✅ Interface-based design for easy testing
- ✅ Laravel Service Container integration
- ✅ Facade support for easy access
- ✅ Comprehensive error handling

## Installation

```bash
composer require mohanad/copytrade:@dev
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=copytrade-config
```

Add your credentials to `.env`:

```env
COPYTRADE_BASE_URI=https://papi.copy-trade.io
COPYTRADE_IDENTITY_URI=https://identity.copy-trade.io
COPYTRADE_ACCESS_TOKEN=your_access_token_here
COPYTRADE_CLIENT_ID=pelican
COPYTRADE_TIMEOUT=30
```

**Important:** The access token is automatically loaded from the config when the service is initialized. You don't need to manually pass it with `withToken()` unless you need to override it at runtime.

## Usage

### Using the Facade

```php
use Copytrade;

// Get user info and profile ID
$userInfo = Copytrade::profiles()->getUserInfo();
$profileId = $userInfo->profileId;

// Get profile
$profile = Copytrade::profiles()->getProfile($profileId);
echo $profile->name;
echo $profile->riskProfile; // integer (0-3)

// Update profile
$updated = Copytrade::profiles()->updateProfile($profileId, [
    'name' => 'John Doe',
    'riskProfile' => 1, // Integer: 0 = Low, 1 = Medium, 2 = High, etc.
    'countryCode' => 'US'
]);

// Get available servers
$servers = Copytrade::servers()->getServers();
foreach ($servers as $server) {
    echo $server->name;
}
```

### Using Dependency Injection

```php
use Mohanad\Copytrade\Contracts\ProfileServiceInterface;
use Mohanad\Copytrade\Contracts\ServerServiceInterface;

class YourController
{
    public function __construct(
        protected ProfileServiceInterface $profileService,
        protected ServerServiceInterface $serverService
    ) {}

    public function index()
    {
        $userInfo = $this->profileService->getUserInfo();
        $profile = $this->profileService->getProfile($userInfo->profileId);

        return view('profile', compact('profile'));
    }

    public function servers()
    {
        $servers = $this->serverService->getServers();

        return view('servers', compact('servers'));
    }
}
```

### With Custom Token

```php
// Set token at runtime
Copytrade::withToken('custom-token')
    ->profiles()
    ->getProfile($profileId);
```

## API Reference

### Profile Service

#### `getUserInfo(): UserInfoDTO`

Get user information from the identity server.

```php
$userInfo = Copytrade::profiles()->getUserInfo();
$profileId = $userInfo->profileId;
```

#### `getProfile(string $profileId): ProfileDTO`

Get profile details by ID.

```php
$profile = Copytrade::profiles()->getProfile($profileId);
```

#### `updateProfile(string $profileId, array $data): ProfileDTO`

Update profile information.

```php
$profile = Copytrade::profiles()->updateProfile($profileId, [
    'name' => 'Jane Doe',
    'riskProfile' => 'conservative',
    'countryCode' => 'UK'
]);
```

### Server Service

#### `getServers(): Collection`

Get list of available servers.

```php
$servers = Copytrade::servers()->getServers();
```

## DTOs

All API responses are wrapped in type-safe DTOs:

### UserInfoDTO

```php
$userInfo->profileId    // string
$userInfo->rawData      // array
```

### ProfileDTO

```php
$profile->id            // string
$profile->name          // ?string
$profile->riskProfile   // ?string
$profile->countryCode   // ?string
$profile->rawData       // array
```

### ServerDTO

```php
$server->id             // string
$server->name           // string
$server->region         // ?string
$server->status         // ?string
$server->rawData        // array
```

## Error Handling

```php
use Mohanad\Copytrade\Exceptions\CopytradeException;

try {
    $profile = Copytrade::profiles()->getProfile($profileId);
} catch (CopytradeException $e) {
    Log::error('CopyTrade API Error: ' . $e->getMessage());
    // Handle error
}
```

## Architecture

The package follows SOLID principles:

- **Single Responsibility**: Each service handles one domain
- **Open/Closed**: Extend via interfaces without modifying core
- **Liskov Substitution**: All services implement contracts
- **Interface Segregation**: Focused, specific interfaces
- **Dependency Inversion**: Depend on abstractions, not concretions

### Structure

```
src/
├── Config/
│   └── copytrade.php           # Configuration
├── Contracts/                   # Interfaces
│   ├── HttpClientInterface.php
│   ├── ProfileServiceInterface.php
│   └── ServerServiceInterface.php
├── DTOs/                        # Data Transfer Objects
│   ├── ProfileDTO.php
│   ├── ServerDTO.php
│   ├── UpdateProfileRequest.php
│   └── UserInfoDTO.php
├── Exceptions/
│   └── CopytradeException.php
├── Facades/
│   └── Copytrade.php
├── Http/
│   └── HttpClient.php          # HTTP client implementation
├── Services/                    # Service implementations
│   ├── ProfileService.php
│   └── ServerService.php
├── Copytrade.php               # Main class
└── CopytradeServiceProvider.php
```

## Testing

```php
use Mohanad\Copytrade\Contracts\ProfileServiceInterface;

// Mock in tests
$this->mock(ProfileServiceInterface::class, function ($mock) {
    $mock->shouldReceive('getProfile')
        ->once()
        ->andReturn(new ProfileDTO(
            id: '123',
            name: 'Test User'
        ));
});
```

## License

MIT
