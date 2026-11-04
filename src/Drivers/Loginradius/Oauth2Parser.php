<?php

namespace Fayne\Sso\Drivers\Loginradius;

use Fayne\Sso\Parsers\AbstractOauth2Parser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class Oauth2Parser extends AbstractOauth2Parser
{
    public static function redirect(): Response
    {
        config()->set('services.loginradius', config('filament-sso.loginradius.oauth2'));

        return Socialite::driver('loginradius')->redirect();
    }
}
