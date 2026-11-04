<?php

use Fayne\Sso\SsoManager;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\PanelRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Two\User;
use Tests\Fixtures\Admin;
use Tests\Support\PackageDatabase;

class PanelRestrictedAdmin extends Admin implements FilamentUser, HasAppAuthentication
{
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->panel_access;
    }

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->two_factor_secret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->update(['two_factor_secret' => $secret]);
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }
}

beforeEach(function () {
    PackageDatabase::prepare();
    Schema::table('sso_admins', function (Blueprint $table) {
        $table->boolean('panel_access')->default(true);
        $table->text('two_factor_secret')->nullable();
        $table->string('external_subject')->nullable();
    });
    app(PanelRegistry::class)->register(Panel::make()->default()->id('admin')->path('admin')->login());
    expect(Filament::getPanel('admin'))->toBeInstanceOf(Panel::class);
    expect(config('filament-sso.panel', 'admin'))->toBe('admin');
    config([
        'filament-sso.providers.okta.enabled' => true,
        'filament-sso.identity.model' => PanelRestrictedAdmin::class,
        'filament-sso.identity_mapping.okta' => ['claim' => 'email', 'column' => 'email'],
    ]);
    $identity = new User;
    $identity->map(['email' => 'restricted@example.test']);
    $provider = Mockery::mock();
    $provider->shouldReceive('user')->once()->andReturn($identity);
    $manager = Mockery::mock(SsoManager::class);
    $manager->shouldReceive('getProvider')->with('okta')->once()->andReturn($provider);
    app()->instance(SsoManager::class, $manager);
});

it('rejects an active identity that cannot access the configured panel', function () {
    PanelRestrictedAdmin::create(['name' => 'Restricted', 'email' => 'restricted@example.test', 'password' => 'password', 'is_active' => true, 'panel_access' => false]);
    $this->get('/sso/okta/callback')->assertRedirect(route('filament-sso.login'));
    $this->assertGuest('admin');
});

it('does not bypass local app MFA through the SSO callback', function () {
    PanelRestrictedAdmin::create(['name' => 'MFA', 'email' => 'restricted@example.test', 'password' => 'password', 'is_active' => true, 'two_factor_secret' => 'JBSWY3DPEHPK3PXP']);
    $this->get('/sso/okta/callback')->assertRedirect(route('filament-sso.login'));
    $this->assertGuest('admin');
});

it('regenerates the browser CSRF token after a successful SSO login', function () {
    $admin = PanelRestrictedAdmin::create(['name' => 'Active', 'email' => 'restricted@example.test', 'password' => 'password', 'is_active' => true]);
    $this->withSession(['_token' => 'before-login'])->get('/sso/okta/callback')->assertRedirect('/admin');
    $this->assertAuthenticatedAs($admin, 'admin');
    expect(session()->token())->not->toBe('before-login');
});

it('rejects a missing identity claim instead of matching an unbound administrator', function () {
    PanelRestrictedAdmin::create(['name' => 'Unbound', 'email' => 'unbound@example.test', 'password' => 'password', 'is_active' => true]);
    config(['filament-sso.identity_mapping.okta' => ['claim' => 'id', 'column' => 'external_subject']]);
    $this->get('/sso/okta/callback')->assertRedirect(route('filament-sso.login'));
    $this->assertGuest('admin');
});
