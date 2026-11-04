<?php

namespace Fayne\Sso\Drivers;

class OktaDriver extends AbstractDriver
{
    public function __construct($request, $config)
    {
        parent::__construct('okta', $request, $config);
    }
}
