<?php

namespace Fayne\Sso\Drivers;

class LoginradiusDriver extends AbstractDriver
{
    public function __construct($request, $config)
    {
        parent::__construct('loginradius', $request, $config);
    }
}
