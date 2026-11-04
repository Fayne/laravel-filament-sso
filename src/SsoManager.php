<?php

namespace Fayne\Sso;

use Fayne\Sso\Drivers\AzureDriver;
use Fayne\Sso\Drivers\LoginradiusDriver;
use Fayne\Sso\Drivers\OktaDriver;
use Illuminate\Support\Manager;

class SsoManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('filament-sso.default', 'okta');
    }

    public function user()
    {
        return $this->driver()->user();
    }

    public function getProvider($driver = null)
    {
        return $this->driver($driver);
    }

    protected function createOktaDriver(): OktaDriver
    {
        return new OktaDriver($this->container['request'], $this->config);
    }

    protected function createAzureDriver(): AzureDriver
    {
        return new AzureDriver($this->container['request'], $this->config);
    }

    protected function createLoginradiusDriver(): LoginradiusDriver
    {
        return new LoginradiusDriver($this->container['request'], $this->config);
    }
}
