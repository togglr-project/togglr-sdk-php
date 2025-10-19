# Togglr PHP SDK Makefile

.PHONY: help install dev-install generate build test lint format clean

# Default target
help:
	@echo "Available targets:"
	@echo "  install      - Install the package"
	@echo "  dev-install  - Install with development dependencies"
	@echo "  generate     - Generate client from OpenAPI spec"
	@echo "  build        - Build the package"
	@echo "  test         - Run tests"
	@echo "  lint         - Run linting"
	@echo "  format       - Format code"
	@echo "  clean        - Clean generated files"

# Installation
install:
	composer install --no-dev --optimize-autoloader

dev-install:
	composer install

# Generate client from OpenAPI spec
generate:
	@echo "Generating client from OpenAPI specification..."
	@mkdir -p generated
	openapi-generator-cli generate \
		-i specs/sdk.yml \
		-g php \
		-o generated \
		--package-name togglr-client \
		--additional-properties=packageName=togglr-client,projectName=togglr-sdk-php,packageVersion=1.0.0

# Build package
build:
	composer install --no-dev --optimize-autoloader

# Testing
test:
	vendor/bin/phpunit

# Linting
lint:
	vendor/bin/php-cs-fixer fix --dry-run --diff
	vendor/bin/phpstan analyse

# Format code
format:
	vendor/bin/php-cs-fixer fix

# Clean generated files
clean:
	rm -rf vendor/
	rm -rf coverage/
	find . -type d -name ".phpunit.result.cache" -exec rm -rf {} + 2>/dev/null || true
