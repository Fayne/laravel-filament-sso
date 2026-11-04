<?php

namespace Tests;

use Fayne\Sso\SsoServiceProvider;
use Filament\FilamentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Fixtures\Admin;

class TestCase extends Orchestra
{
    protected $enablesPackageDiscoveries = true;

    protected function getPackageProviders($app): array
    {
        return [FilamentServiceProvider::class, SsoServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:5qJq8O4L7PJkq4+9eX6xqz5VxA8w3iH+0mZkR4j7r7U=');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]);
        $app['config']->set('auth.defaults.guard', 'admin');
        $app['config']->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'sso-admins']);
        $app['config']->set('auth.providers.sso-admins', ['driver' => 'eloquent', 'model' => Admin::class]);
        $app['config']->set('filament-sso.identity.model', Admin::class);
    }
}
