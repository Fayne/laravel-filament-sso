<?php

namespace Fayne\Sso\Drivers;

use Laravel\Socialite\Facades\Socialite;

class AzureDriver
{
    protected $request;

    protected $config;

    public function __construct($request, $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    public function user()
    {
        $this->setConfig();

        return Socialite::driver('azure')->user();
    }

    public function redirect()
    {
        $this->setConfig();

        return Socialite::driver('azure')->redirect();
    }

    protected function setConfig()
    {
        config()->set('services.azure', $this->config->get('filament-sso.azure'));
    }
}
