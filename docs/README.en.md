<p align="center"><img src="https://res.cloudinary.com/fayne/image/upload/v1787643269/fayne_logo.png" width="300"></p>

# Laravel Filament Sso

[简体中文](../README.md) | English

For **PHP 8.4+, Laravel 13 and Filament 5**.

## Installation

Configure authorized Composer sources for this private package and its required `fayne/*` dependencies in the host first.

```bash
composer require fayne/laravel-filament-sso
php artisan vendor:publish --tag=filament-sso-config
```

Laravel discovers the service provider automatically. Field-only packages need no Panel plugin. See [composer.json](../composer.json) for exact dependencies; do not copy private credentials from a development host.

## Features, usage and boundaries

Single sign-on integrations for Laravel 13 and Filament 5, supporting Okta, Microsoft Azure, and LoginRadius drivers.

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5

## Installation

```bash
composer require fayne/laravel-filament-sso
php artisan vendor:publish --tag=filament-sso-config
```

Register the plugin in your panel:

```php
use Fayne\Sso\Filament\SsoPlugin;

$panel->plugins([SsoPlugin::make()]);
```

Configure provider credentials, callback URLs, and the identity model in `config/filament-sso.php` and the host environment. Only explicitly mapped active identities are authenticated.

Set `panel` to the target Filament panel ID. Missing or empty identity claims are rejected before account lookup. A `FilamentUser` must also be allowed into that panel. Accounts with local app MFA use password and MFA login; this callback does not replace the local challenge. Providers remain disabled by default.

## Tests and quality checks

Run from this package directory:

```bash
composer install
composer test
composer format
```

## Scope and roadmap

Current package scope is driver compatibility, callback handling, and Filament panel integration. The former manager tests remain active in the package test suite and target the current manager, facade, and configuration names. Verify each provider against a real identity provider separately; automated tests use mocks.


## Testing and deployment

Run `composer install`, `composer test`, and `composer format` from the package directory. Automated checks do not replace host UI and target-environment acceptance.

## Cooperation and contributions

Open an issue with reproduction steps, versions and expected behavior. Include relevant checks and outstanding environment acceptance in pull requests. Never submit credentials or sensitive business data. For commercial cooperation and licensing, contact [Fayne Guo](mailto:fayne.guo@gmail.com).

## License

[Proprietary license](../LICENSE.md). Copying, distribution and use require authorization. Third-party dependencies retain their own licenses.

## Development and security

[Development and CI](../CONTRIBUTING.md) · [Security reports](../SECURITY.md). Run `composer check` for syntax, formatting and available standalone tests. Do not commit `composer.lock`.
