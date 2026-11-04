<?php

namespace Fayne\Sso\Parsers;

use Fayne\Sso\Contracts\ParserInterface;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractStandardParser implements ParserInterface
{
    protected Request $request;

    protected string $providerName;

    public function __construct(Request $request, string $providerName)
    {
        $this->request = $request;
        $this->providerName = $providerName;
    }

    abstract public function getUser(): ?User;

    abstract public static function redirect(): Response;
}
