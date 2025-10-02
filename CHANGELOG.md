# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-01-02

### Added
- **Error Reporting**: New methods for reporting feature execution errors
  - `reportError($featureKey, $errorType, $errorMessage, $context)` - Report a single error with automatic retries, returns `[$health, $isPending]`
  - Support for different error types (timeout, validation, service_unavailable, etc.)
  - Context data support for error reports

- **Feature Health Monitoring**: New methods for monitoring feature health
  - `getFeatureHealth($featureKey)` - Get detailed health status with automatic retries
  - `isFeatureHealthy($featureKey)` - Simple boolean health check

- **New Models**:
  - `Togglr\Sdk\Models\ErrorReport` - Structure for error reporting
  - `Togglr\Sdk\Models\FeatureHealth` - Structure for health monitoring with detailed information
  - Support for 202 responses with `$isPending` boolean return value

- **Enhanced Examples**:
  - Updated simple example with error reporting and health monitoring
  - New advanced example demonstrating comprehensive usage
  - Error reporting examples with different error types
  - Health monitoring examples

### Changed
- **Retry Logic**: All methods now automatically apply retries based on client configuration
- **202 Response Handling**: 202 responses now return `[$health, $isPending]` with `$isPending = true` instead of error
- Updated README with comprehensive documentation for new features
- Enhanced error handling and response processing
- Improved example structure and organization

### Technical Details
- Manual implementation of API endpoints (no OpenAPI client generation)
- Automatic retry logic based on client configuration
- Proper handling of 202 responses with pending change indication
- Backward compatible - no breaking changes to existing API
- Full PHP-style method naming and conventions
- Comprehensive PHPDoc type annotations

## [1.0.0] - 2024-01-XX

### Added
- Initial release of togglr-sdk-php
- Support for feature flag evaluation
- Request context with predefined attributes
- Caching support with TTL
- Retry logic with exponential backoff
- Health check functionality
- Comprehensive error handling
- Examples and documentation
- PHPUnit test suite
- PHP CS Fixer code style checking
- PHPStan static analysis
- Coverage reporting

### Features
- **Client**: Main client for feature flag evaluation
- **RequestContext**: Builder pattern for request context
- **Configuration**: Flexible configuration with method chaining
- **Caching**: ArrayAdapter cache with TTL for evaluation results
- **Retries**: Exponential backoff retry logic
- **Logging**: PSR-3 compatible logging interface
- **Error Handling**: Comprehensive error types and handling