<?php

namespace Fayne\Sso\Parsers;

use Fayne\Sso\Contracts\ParserInterface;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractOauth2Parser implements ParserInterface
{
    protected string $providerName;

    public function __construct(string $providerName)
    {
        $this->providerName = $providerName;
    }

    public function getUser(): ?User
    {
        $this->setConfig();

        return Socialite::driver($this->providerName)->user();
    }

    public static function redirect(): Response
    {
        // This static method can't access providerName, so we can't set the config here.
        // The config must be set before calling this method, maybe in the driver.
        // Or, we make this non-static and change the driver logic.
        // For now, let's assume the child class will handle it.
        throw new \Exception('Redirect method must be implemented in the child parser.');
    }

    protected function setConfig(): void
    {
        $configKey = sprintf('filament-sso.%s.oauth2', $this->providerName);
        config()->set(sprintf('services.%s', $this->providerName), config($configKey));
    }
}
