<?php

namespace Fayne\Sso\Drivers;

use Fayne\Sso\Contracts\ParserInterface;
use Illuminate\Support\Str;

abstract class AbstractDriver
{
    protected $request;

    protected $config;

    protected string $providerName;

    public function __construct(string $providerName, $request, $config)
    {
        $this->providerName = $providerName;
        $this->request = $request;
        $this->config = $config;
    }

    public function user()
    {
        return $this->getParser()->getUser();
    }

    public function redirect()
    {
        $class = $this->getParserClass();

        return $class::redirect();
    }

    public function getParser(): ParserInterface
    {
        return $this->createParser();
    }

    protected function getParserClass(): string
    {
        $mode = $this->config->get(sprintf('filament-sso.%s.mode', $this->providerName), 'saml');
        $providerName = Str::studly($this->providerName);
        $mode = Str::studly($mode);

        return sprintf('Fayne\\Sso\\Drivers\\%s\\%sParser', $providerName, $mode);
    }

    protected function createParser(): ParserInterface
    {
        $class = $this->getParserClass();

        if (class_exists($class)) {
            // SAML parsers need the request object, OAuth2 parsers might not.
            // This could be improved with a factory or checking constructor parameters.
            if (str_contains($class, 'SamlParser') || str_contains($class, 'StandardParser')) {
                return new $class($this->request, $this->providerName);
            }

            return new $class($this->providerName);
        }

        throw new \Exception(sprintf('Parser class [%s] not exists', $class));
    }
}
