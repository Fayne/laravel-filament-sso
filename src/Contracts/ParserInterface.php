<?php

namespace Fayne\Sso\Contracts;

use Laravel\Socialite\Contracts\User;
use Symfony\Component\HttpFoundation\Response;

interface ParserInterface
{
    public function getUser(): ?User;

    public static function redirect(): Response;
}
