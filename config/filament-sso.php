<?php

return [
    'default' => env('FILAMENT_SSO_DEFAULT', 'okta'),
    'routes' => ['prefix' => env('FILAMENT_SSO_ROUTE_PREFIX', 'sso')],
    'redirect' => '/admin',
    'panel' => 'admin',
    'identity' => [
        'guard' => 'admin',
        'model' => null,
    ],
    'identity_mapping' => [],
    'providers' => [
        'okta' => ['enabled' => env('FILAMENT_SSO_OKTA_ENABLED', false)],
        'azure' => ['enabled' => env('FILAMENT_SSO_AZURE_ENABLED', false)],
        'loginradius' => ['enabled' => env('FILAMENT_SSO_LOGINRADIUS_ENABLED', false)],
    ],

    'okta' => [
        'mode' => env('FILAMENT_SSO_OKTA_MODE', 'saml'),

        'oauth2' => [
            'base_url' => env('FILAMENT_SSO_OKTA_BASE_URL'),
            'client_id' => env('FILAMENT_SSO_OKTA_CLIENT_ID'),
            'client_secret' => env('FILAMENT_SSO_OKTA_CLIENT_SECRET'),
            'redirect' => env('FILAMENT_SSO_OKTA_OAUTH_REDIRECT_URI'),
        ],

        'saml' => [
            'redirect' => env('FILAMENT_SSO_OKTA_REDIRECT_URI'),
            'issuer' => env('FILAMENT_SSO_OKTA_ISSUER'),
            'x509cert' => env('FILAMENT_SSO_OKTA_CERTIFICATE'),
        ],
    ],

    'azure' => [
        'client_id' => env('FILAMENT_SSO_AZURE_CLIENT_ID'),
        'client_secret' => env('FILAMENT_SSO_AZURE_CLIENT_SECRET'),
        'redirect' => env('FILAMENT_SSO_AZURE_REDIRECT_URI'),
        'tenant' => env('FILAMENT_SSO_AZURE_TENANT_ID'),
    ],

    'loginradius' => [
        'mode' => env('FILAMENT_SSO_LOGINRADIUS_MODE', 'saml'),

        'standard' => [
            'api_key' => env('FILAMENT_SSO_LOGINRADIUS_STANDARD_API_KEY'), // LoginRadius Standard SSO URL
        ],

        'oauth2' => [
            'base_url' => env('FILAMENT_SSO_LOGINRADIUS_BASE_URL'),
            'client_id' => env('FILAMENT_SSO_LOGINRADIUS_CLIENT_ID'),
            'redirect' => env('FILAMENT_SSO_LOGINRADIUS_OAUTH_REDIRECT_URI'),
            'client_secret' => env('FILAMENT_SSO_LOGINRADIUS_CLIENT_SECRET'),
        ],

        'saml' => [
            'redirect' => env('FILAMENT_SSO_LOGINRADIUS_REDIRECT_URI'), // LoginRadius IdP SSO URL
            'issuer' => env('FILAMENT_SSO_LOGINRADIUS_ISSUER'),
            'x509cert' => env('FILAMENT_SSO_LOGINRADIUS_CERTIFICATE'),
            'logout_redirect' => env('FILAMENT_SSO_LOGINRADIUS_LOGOUT_REDIRECT_URI'),
        ],

    ],
];
