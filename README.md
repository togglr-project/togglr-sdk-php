# Togglr PHP SDK

PHP SDK for working with Togglr - feature flag management system.

## Installation

```bash
composer require togglr/sdk-php
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Togglr\Sdk\TogglrSdk;
use Togglr\Sdk\RequestContext;

// Create client with default configuration
$client = TogglrSdk::newClient(
    'your-api-key-here',
    [
        'base_url' => 'http://localhost:8090',
        'timeout' => 1.0,
        'cache' => [
            'enabled' => true,
            'max_size' => 1000,
            'ttl_seconds' => 10,
        ],
    ]
);

// Create request context
$context = RequestContext::new()
    ->withUserId('user123')
    ->withCountry('US')
    ->withDeviceType('mobile')
    ->withOs('iOS')
    ->withOsVersion('15.0')
    ->withBrowser('Safari')
    ->withLanguage('en-US');

// Evaluate feature flag
try {
    $result = $client->evaluate('new_ui', $context);
    if ($result['found']) {
        echo "Feature enabled: {$result['enabled']}, value: {$result['value']}\n";
    } else {
        echo "Feature not found\n";
    }
} catch (Togglr\Sdk\Exception\TogglrException $e) {
    echo "Error evaluating feature: {$e->getMessage()}\n";
}

// Simple enabled check
try {
    $isEnabled = $client->isEnabled('new_ui', $context);
    echo "Feature is enabled: " . ($isEnabled ? 'true' : 'false') . "\n";
} catch (Togglr\Sdk\Exception\FeatureNotFoundException $e) {
    echo "Feature not found\n";
} catch (Togglr\Sdk\Exception\TogglrException $e) {
    echo "Error checking feature: {$e->getMessage()}\n";
}

// With default value
$isEnabled = $client->isEnabledOrDefault('new_ui', $context, false);
echo "Feature enabled (with default): " . ($isEnabled ? 'true' : 'false') . "\n";

// Clean up
$client->close();
```

## Configuration

### Creating a client

```php
// With default settings
$client = TogglrSdk::newClient('api-key');

// With custom configuration
$client = TogglrSdk::newClient('api-key', [
    'base_url' => 'https://api.togglr.com',
    'timeout' => 2.0,
    'retries' => 3,
    'cache' => [
        'enabled' => true,
        'max_size' => 1000,
        'ttl_seconds' => 10,
    ],
]);
```

### Advanced configuration

```php
use Togglr\Sdk\ClientConfig;
use Togglr\Sdk\BackoffConfig;
use Togglr\Sdk\Client;

// Create custom configuration
$config = ClientConfig::default('api-key')
    ->withBaseUrl('https://api.togglr.com')
    ->withTimeout(2.0)
    ->withRetries(3)
    ->withCache(true, 1000, 10)
    ->withBackoff(new BackoffConfig(0.2, 5.0, 1.5));

$client = new Client($config);
```

## Usage

### Creating request context

```php
$context = RequestContext::new()
    ->withUserId('user123')
    ->withUserEmail('user@example.com')
    ->withCountry('US')
    ->withDeviceType('mobile')
    ->withOs('iOS')
    ->withOsVersion('15.0')
    ->withBrowser('Safari')
    ->withLanguage('en-US')
    ->withAge(25)
    ->withGender('female')
    ->set('custom_attribute', 'custom_value');
```

### Evaluating feature flags

```php
// Full evaluation
$result = $client->evaluate('feature_key', $context);
// Returns: ['value' => string, 'enabled' => bool, 'found' => bool]

// Simple enabled check
$isEnabled = $client->isEnabled('feature_key', $context);

// With default value
$isEnabled = $client->isEnabledOrDefault('feature_key', $context, false);
```

### Health check

```php
if ($client->healthCheck()) {
    echo "API is healthy\n";
} else {
    echo "API is not healthy\n";
}
```

## Caching

The SDK supports optional caching of evaluation results using Symfony Cache:

```php
$client = TogglrSdk::newClient('api-key', [
    'cache' => [
        'enabled' => true,
        'max_size' => 1000,
        'ttl_seconds' => 10,
    ],
]);
```

## Retries

The SDK automatically retries requests on temporary errors:

```php
$config = ClientConfig::default('api-key')
    ->withRetries(3)
    ->withBackoff(new BackoffConfig(0.1, 2.0, 2.0));

$client = new Client($config);
```

## Logging and Metrics

```php
use Psr\Log\LoggerInterface;

class CustomLogger implements LoggerInterface
{
    public function log($level, $message, array $context = []): void
    {
        echo "[{$level}] {$message} " . json_encode($context) . "\n";
    }
    
    // ... implement other required methods
}

$config = ClientConfig::default('api-key')
    ->withLogger(new CustomLogger());

$client = new Client($config);
```

## Error Handling

```php
use Togglr\Sdk\Exception\TogglrException;
use Togglr\Sdk\Exception\UnauthorizedException;
use Togglr\Sdk\Exception\BadRequestException;
use Togglr\Sdk\Exception\NotFoundException;
use Togglr\Sdk\Exception\InternalServerException;
use Togglr\Sdk\Exception\TooManyRequestsException;
use Togglr\Sdk\Exception\FeatureNotFoundException;

try {
    $result = $client->evaluate('feature_key', $context);
} catch (UnauthorizedException $e) {
    // Authorization error
} catch (BadRequestException $e) {
    // Bad request
} catch (NotFoundException $e) {
    // Resource not found
} catch (InternalServerException $e) {
    // Internal server error
} catch (TooManyRequestsException $e) {
    // Rate limit exceeded
} catch (FeatureNotFoundException $e) {
    // Feature flag not found
} catch (TogglrException $e) {
    // Other errors
    echo "Error: {$e->getMessage()}\n";
}
```

## Client Generation

To update the generated client from OpenAPI specification:

```bash
make generate
```

## Building and Testing

```bash
# Install development dependencies
make dev-install

# Build
make build

# Testing
make test

# Linting
make lint

# Format code
make format

# Clean
make clean
```

## Examples

Complete usage examples are located in the `examples/` directory:

- `simple_example.php` - Simple usage example
- `advanced_example.php` - Advanced example with custom configuration

## Requirements

- PHP 8.1+
- Guzzle HTTP 7.0+
- Symfony Cache 6.0+
- PSR-3 Logger Interface
- PSR-6 Cache Interface

## Dependencies

- `guzzlehttp/guzzle` - HTTP client
- `symfony/cache` - Caching implementation
- `psr/cache` - Cache interface
- `psr/log` - Logger interface
