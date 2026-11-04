<?php

use Fayne\Sso\Drivers\AzureDriver;
use Fayne\Sso\Drivers\Loginradius\StandardParser;
use Fayne\Sso\Drivers\Okta\Oauth2Parser;
use Fayne\Sso\Drivers\Okta\SamlParser;
use Fayne\Sso\SsoManager;
use Tests\Support\PackageDatabase;

beforeEach(function () {
    config(['logging.default' => 'single']);
    PackageDatabase::prepare();
});

it('resolves the supported Okta and LoginRadius parser modes', function (string $provider, string $mode, string $parser) {
    config(["filament-sso.{$provider}.mode" => $mode]);

    expect((new SsoManager(app()))->getProvider($provider)->getParser())->toBeInstanceOf($parser);
})->with([
    'Okta SAML' => ['okta', 'saml', SamlParser::class],
    'Okta OAuth2' => ['okta', 'oauth2', Oauth2Parser::class],
    'LoginRadius SAML' => ['loginradius', 'saml', Fayne\Sso\Drivers\Loginradius\SamlParser::class],
    'LoginRadius OAuth2' => ['loginradius', 'oauth2', Fayne\Sso\Drivers\Loginradius\Oauth2Parser::class],
    'LoginRadius Standard' => ['loginradius', 'standard', StandardParser::class],
]);

it('keeps Azure on the Socialite driver implementation', function () {
    expect((new SsoManager(app()))->getProvider('azure'))->toBeInstanceOf(AzureDriver::class);
});
