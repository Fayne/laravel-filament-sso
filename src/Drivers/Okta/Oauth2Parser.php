<?php

namespace Fayne\Sso\Drivers\Okta;

use Fayne\Sso\Parsers\AbstractOauth2Parser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class Oauth2Parser extends AbstractOauth2Parser
{
    public static function redirect(): Response
    {
        config()->set('services.okta', config('filament-sso.okta.oauth2'));

        return Socialite::driver('okta')->redirect();
    }
}
