# Togglr PHP SDK

PHP SDK for working with Togglr - feature flag management system.

## Installation

```bash
composer require togglr/sdk-php
```

## Package Structure

The SDK is self-contained and includes all necessary generated code:

- `src/` - Main SDK source code
- `src/Generated/` - Generated API client code (included in package)
- `examples/` - Usage examples
- `specs/` - OpenAPI specification

No external generation step is required for end users.

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Togglr\Sdk\TogglrSdk;
use Togglr\Sdk\RequestContext;
use Togglr\Sdk\Models\TrackEvent;

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
    'insecure' => true, // Skip SSL verification for self-signed certificates
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

## Event Tracking

The SDK supports tracking events for analytics and A/B testing. This is essential for measuring feature impact and training machine learning algorithms.

### Basic Event Tracking

```php
// Track impression event (recommended for each evaluation)
$impressionEvent = TrackEvent::new('A', TrackEvent::EVENT_TYPE_SUCCESS)
    ->withContext('user.id', 'user123')
    ->withContext('country', 'US')
    ->withContext('device_type', 'mobile')
    ->withDedupKey('impression-user123-feature_key');

$client->trackEvent('feature_key', $impressionEvent);
```

### Conversion Tracking

```php
// Track conversion with reward
$conversionEvent = TrackEvent::new('A', TrackEvent::EVENT_TYPE_SUCCESS)
    ->withReward(1.0)
    ->withContext('user.id', 'user123')
    ->withContext('conversion_type', 'purchase')
    ->withContext('order_value', 99.99)
    ->withDedupKey('conversion-user123-feature_key');

$client->trackEvent('feature_key', $conversionEvent);
```

### Error and Failure Tracking

```php
// Track failure event
$failureEvent = TrackEvent::new('B', TrackEvent::EVENT_TYPE_FAILURE)
    ->withContext('user.id', 'user123')
    ->withContext('failure_reason', 'validation_error')
    ->withDedupKey('failure-user123-feature_key');

$client->trackEvent('feature_key', $failureEvent);

// Track error event
$errorEvent = TrackEvent::new('B', TrackEvent::EVENT_TYPE_ERROR)
    ->withContext('user.id', 'user123')
    ->withContext('error_type', 'timeout')
    ->withContext('error_message', 'Service did not respond in 5s')
    ->withDedupKey('error-user123-feature_key');

$client->trackEvent('feature_key', $errorEvent);
```

### Event Types

- `TrackEvent::EVENT_TYPE_SUCCESS` - Successful events (conversions, impressions)
- `TrackEvent::EVENT_TYPE_FAILURE` - Failed events (validation errors, business logic failures)
- `TrackEvent::EVENT_TYPE_ERROR` - Technical errors (timeouts, exceptions)

### Event Context

Events support arbitrary context data for detailed analytics:

```php
$event = TrackEvent::new('A', TrackEvent::EVENT_TYPE_SUCCESS)
    ->withContext('user.id', 'user123')
    ->withContext('session.id', 'sess_456')
    ->withContext('experiment', 'new_algorithm')
    ->withContext('custom_metric', 42.5)
    ->withReward(2.0)
    ->withCreatedAt(new DateTime())
    ->withDedupKey('unique-event-id');
```

## Error Reporting and Auto-Disable

The SDK supports reporting errors for features, which can trigger automatic disabling based on error rates:

```php
// Report an error for a feature
$client->reportError(
    'feature_key',
    'timeout',
    'Service did not respond in 5s',
    ['service' => 'payment-gateway', 'timeout_ms' => 5000]
);

echo "Error reported successfully - queued for processing\n";
```

### Error Types

Supported error types:
- `timeout` - Service timeout
- `validation` - Data validation error
- `service_unavailable` - External service unavailable
- `rate_limit` - Rate limit exceeded
- `network` - Network connectivity issue
- `internal` - Internal application error

### Context Data

You can provide additional context with error reports:

```php
$context = [
    'service' => 'payment-gateway',
    'timeout_ms' => 5000,
    'user_id' => 'user123',
    'region' => 'us-east-1'
];

$client->reportError(
    'feature_key',
    'timeout',
    'Service timeout',
    $context
);
```

## Feature Health Monitoring

Monitor the health status of features:

```php
// Get detailed health information
$health = $client->getFeatureHealth('feature_key');

echo "Feature: {$health->getFeatureKey()}\n";
echo "Enabled: " . ($health->isEnabled() ? 'true' : 'false') . "\n";
echo "Auto Disabled: " . ($health->isAutoDisabled() ? 'true' : 'false') . "\n";
echo "Error Rate: {$health->getErrorRate()}\n";
echo "Threshold: {$health->getThreshold()}\n";
echo "Last Error At: {$health->getLastErrorAt()}\n";

// Simple health check
$isHealthy = $client->isFeatureHealthy('feature_key');
echo "Feature is healthy: " . ($isHealthy ? 'true' : 'false') . "\n";
```

### FeatureHealth Model

The `Togglr\Sdk\Models\FeatureHealth` class provides:

- `getFeatureKey()` - The feature identifier
- `getEnvironmentKey()` - The environment identifier
- `isEnabled()` - Whether the feature is enabled
- `isAutoDisabled()` - Whether the feature was auto-disabled due to errors
- `getErrorRate()` - Current error rate (0.0 to 1.0)
- `getThreshold()` - Error rate threshold for auto-disable
- `getLastErrorAt()` - Timestamp of the last error
- `isHealthy()` - Boolean method to check if feature is healthy

### ErrorReport Model

The `Togglr\Sdk\Models\ErrorReport` class provides:

- `getErrorType()` - Type of error
- `getErrorMessage()` - Human-readable error message
- `getContext()` - Additional context data
- `toArray()` - Convert to array for API requests

```php
// Create an error report
$errorReport = ErrorReport::new(
    'timeout',
    'Service timeout',
    ['service' => 'api', 'timeout_ms' => 5000]
);

// Convert to array for API requests
$errorData = $errorReport->toArray();
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
