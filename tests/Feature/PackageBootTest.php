<?php

use Fayne\Sso\SsoServiceProvider;

it('boots SSO routes independently', function () {
    expect(app()->getLoadedProviders())->toHaveKey(SsoServiceProvider::class);
    $this->get('/sso/login')->assertOk();
});
