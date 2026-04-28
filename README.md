# CopyTrade Laravel SDK

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/laravel-%5E12.0%20%7C%20%5E13.0-red)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A modern, clean Laravel package for integrating with the CopyTrade API. Built with SOLID principles, type-safety, and developer experience in mind.

## 🌟 Features

- ✅ **Clean Architecture** - SOLID principles throughout
- ✅ **Type-Safe DTOs** - Fully typed data transfer objects for all API responses
- ✅ **Interface-Based** - Easy mocking and testing
- ✅ **Laravel Integration** - Service Container, Facades, and auto-discovery
- ✅ **Comprehensive Error Handling** - Specific exceptions for different error types
- ✅ **Full API Coverage** - Profiles, Strategies, Copiers, Servers, and Sections
- ✅ **Developer Friendly** - Intuitive API with great IDE support

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 12.0 or 13.0
- Composer

## 📦 Installation

Add the repository to your project's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/asciisd/caveofx-copytrade.git"
    }
  ],
  "require": {
    "asciisd/copytrade": "dev-main"
  }
}
```

Then install via Composer:

```bash
composer install
```

The package will be auto-discovered by Laravel.

## ⚙️ Configuration

You can publish the configuration file (optional):

```bash
php artisan vendor:publish --provider="Asciisd\Copytrade\CopytradeServiceProvider"
```

## 🚀 Usage Guide

All services require authentication via access token. Set the token using `withToken()` method before making API calls.

### Basic Setup

```php
use Asciisd\Copytrade\Services\ProfileService;

$service = new ProfileService($baseUri, $identityUri);
$service->withToken('your-access-token');
```

---

## 📚 API Reference

### ProfileService

Manage user profiles and account information.

#### Methods

| Method                             | Parameters                         | Returns       | Description                        |
| ---------------------------------- | ---------------------------------- | ------------- | ---------------------------------- |
| `getUserInfo()`                    | None                               | `UserInfoDTO` | Get authenticated user information |
| `getProfile($profileId)`           | `string $profileId`                | `ProfileDTO`  | Get profile by ID                  |
| `updateProfile($profileId, $data)` | `string $profileId`, `array $data` | `ProfileDTO`  | Update profile information         |

#### Update Profile Parameters

```php
$data = [
    'name' => 'Profile Name',        // optional, string
    'riskProfile' => 1,              // optional, integer
    'countryCode' => 'US'            // optional, string (ISO country code)
];

$profile = $service->updateProfile($profileId, $data);
```

#### Example

```php
use Asciisd\Copytrade\Services\ProfileService;

$service = new ProfileService($baseUri, $identityUri);
$service->withToken('your-token');

// Get user info
$userInfo = $service->getUserInfo();

// Get specific profile
$profile = $service->getProfile('profile-id-123');

// Update profile
$updated = $service->updateProfile('profile-id-123', [
    'name' => 'New Name',
    'countryCode' => 'EG'
]);
```

---

### StrategyService

Manage trading strategies, signals, and strategy-related operations.

#### Methods

| Method                                                                  | Parameters                                                                          | Returns               | Description                         |
| ----------------------------------------------------------------------- | ----------------------------------------------------------------------------------- | --------------------- | ----------------------------------- |
| `getStrategies($profileId)`                                             | `string $profileId`                                                                 | `StrategyDTO[]`       | List all strategies for a profile   |
| `addStrategy($profileId, $data)`                                        | `string $profileId`, `array $data`                                                  | `StrategyDTO`         | Create a new strategy               |
| `updateStrategy($profileId, $strategyId, $data)`                        | `string $profileId`, `string $strategyId`, `array $data`                            | `StrategyDTO`         | Update an existing strategy         |
| `getStrategyStats($strategyId)`                                         | `string $strategyId`                                                                | `StrategyStatsDTO`    | Get strategy statistics             |
| `searchStrategies($filter)`                                             | `string $filter`                                                                    | `SearchStrategyDTO[]` | Search for strategies               |
| `getStrategyCopiers($strategyId)`                                       | `string $strategyId`                                                                | `CopierDTO[]`         | Get all copiers using this strategy |
| `uploadStrategyImage($profileId, $strategyId, $fileContent, $filename)` | `string $profileId`, `string $strategyId`, `mixed $fileContent`, `string $filename` | `array`               | Upload strategy image               |
| `getStrategyImageUrl($strategyId)`                                      | `string $strategyId`                                                                | `string`              | Get strategy image URL              |
| `getStrategyClosedSignals($strategyId, $startDate, $endDate)`           | `string $strategyId`, `string $startDate`, `string $endDate`                        | `SignalDTO[]`         | Get closed signals for date range   |
| `getStrategyOpenSignals($strategyId)`                                   | `string $strategyId`                                                                | `SignalDTO[]`         | Get currently open signals          |

#### Add/Update Strategy Parameters

```php
$data = [
    'name' => 'Strategy Name',           // required, string
    'riskProfile' => 'Conservative',     // required, string
    'fee' => 10.5,                       // required, float (percentage)
    'connection' => [
        'brokerCode' => 'BROKER123',     // required, string
        'serverCode' => 'SERVER456',     // required, string
        'username' => 'mt_username',     // required, string
        'password' => 'mt_password'      // required, string
    ]
];
```

#### Example

```php
use Asciisd\Copytrade\Services\StrategyService;

$service = new StrategyService($baseUri);
$service->withToken('your-token');

// List strategies
$strategies = $service->getStrategies('profile-id-123');

// Add a new strategy
$newStrategy = $service->addStrategy('profile-id-123', [
    'name' => 'My Trading Strategy',
    'riskProfile' => 'Moderate',
    'fee' => 15.0,
    'connection' => [
        'brokerCode' => 'XM',
        'serverCode' => 'XM-Real',
        'username' => '12345678',
        'password' => 'MySecurePassword'
    ]
]);

// Get strategy stats
$stats = $service->getStrategyStats('strategy-id-456');

// Search strategies
$results = $service->searchStrategies('forex');

// Get closed signals
$closedSignals = $service->getStrategyClosedSignals(
    'strategy-id-456',
    '2024-01-01',
    '2024-01-31'
);

// Get open signals
$openSignals = $service->getStrategyOpenSignals('strategy-id-456');
```

---

### CopierService

Manage copiers, copy settings, and copy trading operations.

#### Methods

| Method                                                              | Parameters                                                                        | Returns           | Description                    |
| ------------------------------------------------------------------- | --------------------------------------------------------------------------------- | ----------------- | ------------------------------ |
| `getCopiers($profileId)`                                            | `string $profileId`                                                               | `CopierDTO[]`     | List all copiers for a profile |
| `addCopier($profileId, $data)`                                      | `string $profileId`, `array $data`                                                | `CopierDTO`       | Create a new copier            |
| `updateCopier($profileId, $copierId, $data)`                        | `string $profileId`, `string $copierId`, `array $data`                            | `CopierDTO`       | Update an existing copier      |
| `removeCopier($profileId, $copierId)`                               | `string $profileId`, `string $copierId`                                           | `bool`            | Delete a copier                |
| `getCopierStats($copierId)`                                         | `string $copierId`                                                                | `CopierStatsDTO`  | Get copier statistics          |
| `uploadCopierImage($profileId, $copierId, $fileContent, $filename)` | `string $profileId`, `string $copierId`, `mixed $fileContent`, `string $filename` | `array`           | Upload copier image            |
| `getCopierImageUrl($copierId)`                                      | `string $copierId`                                                                | `string`          | Get copier image URL           |
| `getCopierStrategies($copierId)`                                    | `string $copierId`                                                                | `StrategyDTO[]`   | List strategies being copied   |
| `copyStrategy($copierId, $strategyId, $data)`                       | `string $copierId`, `string $strategyId`, `array $data`                           | `CopySettingsDTO` | Start copying a strategy       |
| `getCopySettings($copierId, $strategyId)`                           | `string $copierId`, `string $strategyId`                                          | `CopySettingsDTO` | Get copy settings              |
| `stopCopying($copierId, $strategyId)`                               | `string $copierId`, `string $strategyId`                                          | `bool`            | Stop copying a strategy        |
| `updateCopySettings($copierId, $strategyId, $data)`                 | `string $copierId`, `string $strategyId`, `array $data`                           | `CopySettingsDTO` | Update copy settings           |
| `getMissedSignals($profileId, $copierId)`                           | `string $profileId`, `string $copierId`                                           | `array`           | Get missed trading signals     |

#### Add/Update Copier Parameters

```php
$data = [
    'name' => 'Copier Name',             // required, string
    'connection' => [
        'brokerCode' => 'BROKER123',     // required, string
        'serverCode' => 'SERVER456',     // required, string
        'username' => 'mt_username',     // required, string
        'password' => 'mt_password'      // required, string
    ],
    'drawdown' => [
        'currentLevel' => 0.0,           // required, float
        'softStopLevel' => 20.0,         // required, float (percentage)
        'hardStopLevel' => 30.0          // required, float (percentage)
    ]
];
```

#### Copy Strategy Parameters

```php
$data = [
    'TradeSizeType' => 'FixedLot',       // required, string (FixedLot, Multiplier, Balance)
    'TradeSizeValue' => 0.01,            // required, float
    'IsOpenExistingTrades' => true,      // optional, bool (default: false)
    'IsRoundUpToMinimumSize' => false    // optional, bool (default: false)
];
```

#### Update Copy Settings Parameters

```php
$data = [
    'TradeSizeType' => 'FixedLot',       // required, string
    'TradeSizeValue' => 0.02,            // required, float
    'IsRoundUpToMinimumSize' => true     // optional, bool (default: false)
];
```

#### Example

```php
use Asciisd\Copytrade\Services\CopierService;

$service = new CopierService($baseUri);
$service->withToken('your-token');

// List copiers
$copiers = $service->getCopiers('profile-id-123');

// Add a new copier
$newCopier = $service->addCopier('profile-id-123', [
    'name' => 'My Copier Account',
    'connection' => [
        'brokerCode' => 'XM',
        'serverCode' => 'XM-Real',
        'username' => '87654321',
        'password' => 'MyPassword'
    ],
    'drawdown' => [
        'currentLevel' => 0.0,
        'softStopLevel' => 15.0,
        'hardStopLevel' => 25.0
    ]
]);

// Start copying a strategy
$copySettings = $service->copyStrategy('copier-id-789', 'strategy-id-456', [
    'TradeSizeType' => 'FixedLot',
    'TradeSizeValue' => 0.01,
    'IsOpenExistingTrades' => true,
    'IsRoundUpToMinimumSize' => false
]);

// Update copy settings
$updated = $service->updateCopySettings('copier-id-789', 'strategy-id-456', [
    'TradeSizeType' => 'Multiplier',
    'TradeSizeValue' => 2.0,
    'IsRoundUpToMinimumSize' => true
]);

// Stop copying
$service->stopCopying('copier-id-789', 'strategy-id-456');

// Get copier stats
$stats = $service->getCopierStats('copier-id-789');

// Get missed signals
$missedSignals = $service->getMissedSignals('profile-id-123', 'copier-id-789');
```

---

### ServerService

Retrieve available trading servers.

#### Methods

| Method         | Parameters | Returns       | Description                |
| -------------- | ---------- | ------------- | -------------------------- |
| `getServers()` | None       | `ServerDTO[]` | List all available servers |

#### Example

```php
use Asciisd\Copytrade\Services\ServerService;

$service = new ServerService($baseUri);
$service->withToken('your-token');

// Get all servers
$servers = $service->getServers();

foreach ($servers as $server) {
    echo $server->name . ' - ' . $server->code . PHP_EOL;
}
```

---

### SectionService

Retrieve discovery sections and categories.

#### Methods

| Method              | Parameters     | Returns        | Description                    |
| ------------------- | -------------- | -------------- | ------------------------------ |
| `getSections()`     | None           | `SectionDTO[]` | List all available sections    |
| `getSection($code)` | `string $code` | `SectionDTO`   | Get a specific section by code |

#### Example

```php
use Asciisd\Copytrade\Services\SectionService;

$service = new SectionService($baseUri);
$service->withToken('your-token');

// List all sections
$sections = $service->getSections();

// Get specific section
$section = $service->getSection('forex-strategies');
```

---

## 🔒 Error Handling

The package provides specific exception types:

- `AuthenticationException` - Authentication failures
- `NotFoundException` - Resource not found
- `RateLimitException` - API rate limit exceeded
- `ValidationException` - Invalid request data
- `CopytradeException` - General API errors

```php
use Asciisd\Copytrade\Exceptions\AuthenticationException;
use Asciisd\Copytrade\Exceptions\NotFoundException;

try {
    $profile = $service->getProfile('invalid-id');
} catch (NotFoundException $e) {
    // Handle not found
} catch (AuthenticationException $e) {
    // Handle auth error
}
```

## 📝 License

This package is open-sourced software licensed under the MIT license
$sections = $service->getSections();

// Get a section by code
$section = $service->getSection($code);

````

### 1. Publish Configuration

```bash
php artisan vendor:publish --tag=copytrade-config
````

This creates `config/copytrade.php` in your Laravel application.

### 2. Environment Variables

Add your CopyTrade API credentials to `.env`:

```env
COPYTRADE_BASE_URI=https://papi.copy-trade.io
COPYTRADE_IDENTITY_URI=https://identity.copy-trade.io
COPYTRADE_ACCESS_TOKEN=your_access_token_here
COPYTRADE_CLIENT_ID=pelican
COPYTRADE_TIMEOUT=30
```

> **Important:** The access token is automatically loaded from config. You only need `withToken()` to override it at runtime.

## 🚀 Quick Start

```php
use Copytrade;

// Get your profile
$userInfo = Copytrade::profiles()->getUserInfo();
$profile = Copytrade::profiles()->getProfile($userInfo->profileId);

// List strategies
$strategies = Copytrade::strategies()->getStrategies($userInfo->profileId);

// Search for strategies
$results = Copytrade::strategies()->searchStrategies('high-frequency');

// Get available servers
$servers = Copytrade::servers()->getServers();
```

## 📖 Usage Guide

### Using the Facade

The easiest way to interact with the API:

```php
use Copytrade;

// Profile operations
$userInfo = Copytrade::profiles()->getUserInfo();
$profile = Copytrade::profiles()->getProfile($userInfo->profileId);

// Update profile
$updated = Copytrade::profiles()->updateProfile($userInfo->profileId, [
    'name' => 'John Doe',
    'riskProfile' => 1,
    'countryCode' => 'US'
]);

// Strategy operations
$strategies = Copytrade::strategies()->getStrategies($userInfo->profileId);
$strategyStats = Copytrade::strategies()->getStrategyStats($strategyId);

// Server operations
$servers = Copytrade::servers()->getServers();
```

### Using Dependency Injection

For better testability and type-hinting:

```php
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\Contracts\StrategyServiceInterface;
use Asciisd\Copytrade\Contracts\CopierServiceInterface;

class TradingController extends Controller
{
    public function __construct(
        protected ProfileServiceInterface $profileService,
        protected StrategyServiceInterface $strategyService,
        protected CopierServiceInterface $copierService
    ) {}

    public function dashboard()
    {
        $userInfo = $this->profileService->getUserInfo();
        $profile = $this->profileService->getProfile($userInfo->profileId);
        $strategies = $this->strategyService->getStrategies($userInfo->profileId);

        return view('dashboard', compact('profile', 'strategies'));
    }
}
```

### Runtime Token Override

Override the access token for specific requests:

```php
// Use a different token for this request
$profile = Copytrade::withToken('custom-token')
    ->profiles()
    ->getProfile($profileId);

// Chain multiple calls with the same token
$copytrade = Copytrade::withToken('custom-token');
$profile = $copytrade->profiles()->getProfile($profileId);
$strategies = $copytrade->strategies()->getStrategies($profileId);
```

## 📚 API Reference

### Profile Service

**Get User Info**

```php
$userInfo = Copytrade::profiles()->getUserInfo();
// Returns: UserInfoDTO { profileId, rawData }
```

**Get Profile**

```php
$profile = Copytrade::profiles()->getProfile($profileId);
// Returns: ProfileDTO { id, name, riskProfile, countryCode, rawData }
```

**Update Profile**

```php
$profile = Copytrade::profiles()->updateProfile($profileId, [
    'name' => 'Jane Doe',
    'riskProfile' => 2,
    'countryCode' => 'UK'
]);
// Returns: ProfileDTO
```

---

### Strategy Service

**Get All Strategies**

```php
$strategies = Copytrade::strategies()->getStrategies($profileId);
// Returns: StrategyDTO[]
```

**Add Strategy**

```php
$strategy = Copytrade::strategies()->addStrategy($profileId, [
    'name' => 'My Trading Strategy',
    'riskProfile' => 'medium',
    'fee' => 20.0,
    'connection' => [
        'serverId' => 'server-123',
        'login' => '12345',
        'password' => 'secret'
    ]
]);
// Returns: StrategyDTO
```

**Update Strategy**

```php
$strategy = Copytrade::strategies()->updateStrategy($profileId, $strategyId, [
    'name' => 'Updated Strategy Name',
    'fee' => 25.0
]);
// Returns: StrategyDTO
```

**Get Strategy Statistics**

```php
$stats = Copytrade::strategies()->getStrategyStats($strategyId);
// Returns: StrategyStatsDTO
```

**Search Strategies**

```php
$results = Copytrade::strategies()->searchStrategies('scalping');
// Returns: SearchStrategyDTO[]
```

**Get Strategy Copiers**

```php
$copiers = Copytrade::strategies()->getStrategyCopiers($strategyId);
// Returns: CopierDTO[]
```

**Get Strategy Signals**

```php
$signals = Copytrade::strategies()->getStrategySignals($strategyId);
// Returns: SignalDTO[]
```

---

### Copier Service

**Get All Copiers**

```php
$copiers = Copytrade::copiers()->getCopiers($profileId);
// Returns: CopierDTO[]
```

**Add Copier**

```php
$copier = Copytrade::copiers()->addCopier($profileId, [
    'name' => 'My Copier',
    'connection' => [
        'serverId' => 'server-123',
        'login' => '67890',
        'password' => 'secret'
    ]
]);
// Returns: CopierDTO
```

**Update Copier**

```php
$copier = Copytrade::copiers()->updateCopier($profileId, $copierId, [
    'name' => 'Updated Copier Name'
]);
// Returns: CopierDTO
```

**Remove Copier**

```php
$success = Copytrade::copiers()->removeCopier($profileId, $copierId);
// Returns: bool
```

**Get Copier Statistics**

```php
$stats = Copytrade::copiers()->getCopierStats($copierId);
// Returns: CopierStatsDTO
```

**Copy a Strategy**

```php
$result = Copytrade::copiers()->copyStrategy($profileId, $copierId, [
    'strategyId' => 'strategy-123',
    'copySettings' => [
        'multiplier' => 1.0,
        'maxRisk' => 0.02
    ]
]);
// Returns: CopySettingsDTO
```

**Update Copy Settings**

```php
$settings = Copytrade::copiers()->updateCopySettings($profileId, $copierId, [
    'multiplier' => 1.5,
    'maxRisk' => 0.03
]);
// Returns: CopySettingsDTO
```

**Stop Copying**

```php
$success = Copytrade::copiers()->stopCopy($profileId, $copierId);
// Returns: bool
```

**Get Copied Strategies**

```php
$strategies = Copytrade::copiers()->getCopiedStrategies($copierId);
// Returns: StrategyDTO[]
```

**Get Copier Signals**

```php
$signals = Copytrade::copiers()->getCopierSignals($copierId);
// Returns: SignalDTO[]
```

**Upload Copier Image**

```php
$result = Copytrade::copiers()->uploadCopierImage(
    $profileId,
    $copierId,
    $fileContent,
    'avatar.jpg'
);
// Returns: array
```

---

### Server Service

**Get All Servers**

```php
$servers = Copytrade::servers()->getServers();
// Returns: ServerDTO[]
```

---

### Section Service

**Get All Sections**

```php
$sections = Copytrade::sections()->getSections();
// Returns: SectionDTO[]
```

**Get Section by Code**

```php
$section = Copytrade::sections()->getSection('featured');
// Returns: SectionDTO
```

## 🎯 Data Transfer Objects (DTOs)

All API responses are wrapped in type-safe DTOs with full IDE autocomplete support.

### UserInfoDTO

```php
$userInfo->profileId    // string - Your profile ID
$userInfo->rawData      // array  - Raw API response
```

### ProfileDTO

```php
$profile->id            // string  - Profile ID
$profile->name          // ?string - Profile name
$profile->riskProfile   // ?string - Risk profile level
$profile->countryCode   // ?string - Country code (e.g., 'US')
$profile->rawData       // array   - Raw API response
```

### StrategyDTO

```php
$strategy->id           // string                    - Strategy ID
$strategy->name         // string                    - Strategy name
$strategy->riskProfile  // string                    - Risk profile
$strategy->fee          // float                     - Strategy fee
$strategy->connection   // ?StrategyConnectionDTO   - Connection details
$strategy->rawData      // array                     - Raw API response
```

### CopierDTO

```php
$copier->id             // string                - Copier ID
$copier->name           // ?string               - Copier name
$copier->connection     // ?CopierConnectionDTO - Connection details
$copier->drawdown       // ?CopierDrawdownDTO   - Drawdown info
$copier->rawData        // array                 - Raw API response
```

### StrategyStatsDTO

```php
$stats->totalTrades     // int   - Total number of trades
$stats->winRate         // float - Win rate percentage
$stats->profitFactor    // float - Profit factor
$stats->rawData         // array - Raw API response
```

### ServerDTO

```php
$server->id             // string - Server ID
$server->name           // string - Server name
$server->type           // string - Server type
$server->rawData        // array  - Raw API response
```

### SectionDTO

```php
$section->code          // string - Section code
$section->title         // string - Section title
$section->strategies    // array  - Strategies in section
$section->rawData       // array  - Raw API response
```

## ⚠️ Error Handling

The package provides specific exceptions for different error scenarios:

```php
use Asciisd\Copytrade\Exceptions\AuthenticationException;
use Asciisd\Copytrade\Exceptions\NotFoundException;
use Asciisd\Copytrade\Exceptions\ValidationException;
use Asciisd\Copytrade\Exceptions\RateLimitException;
use Asciisd\Copytrade\Exceptions\CopytradeException;

try {
    $profile = Copytrade::profiles()->getProfile($profileId);
} catch (AuthenticationException $e) {
    // Invalid or expired access token
    Log::error('Authentication failed: ' . $e->getMessage());
} catch (NotFoundException $e) {
    // Profile not found
    abort(404, 'Profile not found');
} catch (ValidationException $e) {
    // Invalid input data
    return back()->withErrors($e->getMessage());
} catch (RateLimitException $e) {
    // Rate limit exceeded
    return response('Too many requests', 429);
} catch (CopytradeException $e) {
    // Any other API error
    Log::error('API Error: ' . $e->getMessage());
}
```

## 🧪 Testing

The package is designed with testability in mind. All services implement interfaces, making them easy to mock:

```php
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;
use Asciisd\Copytrade\DTOs\Profile\ProfileDTO;

class ExampleTest extends TestCase
{
    public function test_can_get_profile()
    {
        // Mock the service
        $mock = Mockery::mock(ProfileServiceInterface::class);
        $mock->shouldReceive('getProfile')
            ->with('profile-123')
            ->andReturn(new ProfileDTO(
                id: 'profile-123',
                name: 'Test User',
                riskProfile: 'low',
                countryCode: 'US'
            ));

        $this->app->instance(ProfileServiceInterface::class, $mock);

        // Test your code
        $response = $this->get('/profile/profile-123');
        $response->assertSee('Test User');
    }
}
```

## 🔧 Advanced Usage

### Custom HTTP Client Configuration

You can modify HTTP client settings in the config file:

```php
// config/copytrade.php
return [
    'timeout' => 30,
    'retry_times' => 3,
    'retry_delay' => 100,
    // ... other settings
];
```

### Handling Raw Responses

All DTOs include a `rawData` property with the complete API response:

```php
$profile = Copytrade::profiles()->getProfile($profileId);

// Access structured data
echo $profile->name;

// Access raw response
dd($profile->rawData);
```

### Working with Multiple Accounts

Use runtime token override to work with multiple accounts:

```php
$accounts = [
    'account1' => 'token1',
    'account2' => 'token2',
];

foreach ($accounts as $name => $token) {
    $copytrade = Copytrade::withToken($token);
    $userInfo = $copytrade->profiles()->getUserInfo();
    $profile = $copytrade->profiles()->getProfile($userInfo->profileId);

    echo "{$name}: {$profile->name}\n";
}
```

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 🐛 Support

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/asciisd/caveofx-copytrade/issues) page
2. Create a new issue if your problem isn't already listed
3. Provide as much detail as possible

## 🔗 Links

- [GitHub Repository](https://github.com/asciisd/caveofx-copytrade)
- [CopyTrade API Documentation](https://copy-trade.io/docs)

---

Built with ❤️ using Laravel

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
use Asciisd\Copytrade\Exceptions\CopytradeException;

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
use Asciisd\Copytrade\Contracts\ProfileServiceInterface;

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
