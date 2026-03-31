# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Verter Client is a PHP library for querying exchange rates from the Verter API service. It uses Symfony HttpClient for HTTP and supports PSR-3 logging.

## Commands

```bash
# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit

# Run a single test
./vendor/bin/phpunit tests/Client/Object/RateItemTest.php

# Run tests with coverage
./vendor/bin/phpunit --coverage-text -dxdebug.mode=coverage

# Static analysis
./vendor/bin/phpstan analyse
```

## Architecture

- **`BaseClient`** — Abstract base class handling HTTP via Symfony HttpClient, authentication (API key), logging, and SSL config. All clients extend this.
- **`RateClient`** — Concrete client for the `/api/v1/rates` endpoint. Returns `RateItem` or null. Catches transport/format errors into custom exceptions.
- **`RateItem`** — Immutable value object (readonly promoted properties, private constructor). Created via `RateItem::createFromJson()`. Implements `JsonSerializable`. Supports recursive intermediate rates.
- **Exceptions** — `VerterTransportException` (HTTP/network errors) and `VerterFormatException` (JSON parsing errors), both extend `RuntimeException`.

## Key Conventions

- PHP 8.4+ with constructor property promotion and readonly properties
- PSR-4 autoloading: `VerterClient\` → `src/`, `VerterClient\Tests\` → `tests/`
- PHPStan at max level — all code must pass `phpstan analyse`
- 4-space indentation, LF line endings, UTF-8
