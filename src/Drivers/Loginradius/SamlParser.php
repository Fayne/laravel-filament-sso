<?php

namespace Fayne\Sso\Drivers\Loginradius;

use Fayne\Sso\Parsers\AbstractSamlParser;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SamlParser extends AbstractSamlParser
{
    public static function redirect(): SymfonyResponse
    {
        return new RedirectResponse(config('filament-sso.loginradius.saml.redirect'));
    }
}
