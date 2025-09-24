# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-XX

### Added
- Initial release of togglr-sdk-php
- Support for feature flag evaluation
- Request context with predefined attributes
- Caching support with TTL using Symfony Cache
- Retry logic with exponential backoff
- Health check functionality
- Comprehensive error handling
- Type hints throughout
- Unit tests
- Examples and documentation
- OpenAPI client generation from specs/sdk.yml
- Makefile for common tasks

### Features
- **Client**: Main client for feature flag evaluation
- **RequestContext**: Builder pattern for request context
- **Configuration**: Flexible configuration with method chaining
- **Caching**: Optional LRU cache with TTL using Symfony Cache
- **Error Handling**: Specific error types for different scenarios
- **Logging**: Optional PSR-3 logger support
- **Retries**: Configurable retry logic with backoff
- **Health Checks**: API health monitoring

### API
- `Client::evaluate(featureKey, context)` - Full feature evaluation
- `Client::isEnabled(featureKey, context)` - Simple enabled check
- `Client::isEnabledOrDefault(featureKey, context, default)` - Check with fallback
- `Client::healthCheck()` - API health check
- `RequestContext::new()` - Create new context
- `RequestContext::with*()` - Chainable context builders
- `ClientConfig::default(apiKey)` - Create default config
- `ClientConfig::with*()` - Chainable config builders

### Dependencies
- guzzlehttp/guzzle >= 7.0
- symfony/cache >= 6.0
- psr/cache >= 3.0
- psr/log >= 3.0
