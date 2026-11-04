<?php

use Fayne\Sso\Drivers\AzureDriver;
use Fayne\Sso\Drivers\Okta\SamlParser;
use Fayne\Sso\Drivers\OktaDriver;
use Fayne\Sso\Facades\Sso;

it('can resolve okta driver by default', function () {
    config()->set('filament-sso.default', 'okta');

    $driver = Sso::driver();

    expect($driver)->toBeInstanceOf(OktaDriver::class);
});

it('can resolve azure driver', function () {
    config()->set('filament-sso.default', 'azure');

    $driver = Sso::driver();

    expect($driver)->toBeInstanceOf(AzureDriver::class);
});

it('can resolve driver explicitly', function () {
    $okta = Sso::driver('okta');
    $azure = Sso::driver('azure');

    expect($okta)->toBeInstanceOf(OktaDriver::class)
        ->and($azure)->toBeInstanceOf(AzureDriver::class);
});

it('can get parser from okta driver', function () {
    $parser = Sso::driver('okta')->getParser();

    // Default is saml
    expect($parser)->toBeInstanceOf(SamlParser::class);
});
