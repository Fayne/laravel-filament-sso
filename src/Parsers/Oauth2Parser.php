<?php

namespace Fayne\Sso\Drivers\Parsers;

use Fayne\Sso\Contracts\ParserInterface;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class Oauth2Parser implements ParserInterface
{
    public function getUser(): ?User
    {
        config()->set('services.okta', config('filament-sso.okta.oauth2'));

        return Socialite::driver('okta')->user();
    }

    public static function redirect(): Response
    {
        config()->set('services.okta', config('filament-sso.okta.oauth2'));

        return Socialite::driver('okta')->redirect();
    }
}
