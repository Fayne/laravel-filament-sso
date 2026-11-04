<?php

use Fayne\Sso\SsoManager;
use Laravel\Socialite\Two\User;
use Tests\Fixtures\Admin;
use Tests\Support\PackageDatabase;

beforeEach(function () {
    config(['logging.default' => 'single']);
    PackageDatabase::prepare();
    config([
        'filament-sso.providers.okta.enabled' => true,
        'filament-sso.identity_mapping.okta' => ['claim' => 'email', 'column' => 'email'],
    ]);
});

it('logs in only the explicitly mapped active admin', function () {
    $admin = Admin::query()->create([
        'name' => 'SSO Admin',
        'email' => 'sso-admin@example.test',
        'password' => 'password',
    ]);
    $identity = Mockery::mock(User::class);
    $identity->shouldReceive('getEmail')->once()->andReturn($admin->email);
    $provider = Mockery::mock();
    $provider->shouldReceive('user')->once()->andReturn($identity);
    $manager = Mockery::mock(SsoManager::class);
    $manager->shouldReceive('getProvider')->once()->with('okta')->andReturn($provider);
    app()->instance(SsoManager::class, $manager);

    $this->get('/sso/okta/callback')->assertRedirect('/admin');
    $this->assertAuthenticatedAs($admin, 'admin');
});

it('rejects unknown providers, unmapped identities, and inactive admins', function () {
    $inactive = Admin::query()->create([
        'name' => 'Inactive SSO Admin',
        'email' => 'sso-inactive@example.test',
        'password' => 'password',
        'is_active' => false,
    ]);
    $identity = Mockery::mock(User::class);
    $identity->shouldReceive('getEmail')->andReturn($inactive->email);
    $emptyProvider = Mockery::mock();
    $emptyProvider->shouldReceive('user')->once()->andReturn(null);
    $inactiveProvider = Mockery::mock();
    $inactiveProvider->shouldReceive('user')->once()->andReturn($identity);
    $manager = Mockery::mock(SsoManager::class);
    $manager->shouldReceive('getProvider')->andReturn($emptyProvider, $inactiveProvider);
    app()->instance(SsoManager::class, $manager);

    $this->get('/sso/unknown/callback')->assertNotFound();
    config(['filament-sso.identity_mapping.okta' => []]);
    $this->get('/sso/okta/callback')->assertRedirect(route('filament-sso.login'));

    config(['filament-sso.identity_mapping.okta' => ['claim' => 'email', 'column' => 'email']]);

    $this->get('/sso/okta/callback')->assertRedirect(route('filament-sso.login'));
    $this->assertGuest('admin');
});
