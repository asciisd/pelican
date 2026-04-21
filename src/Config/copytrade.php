<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CopyTrade API Base URI
    |--------------------------------------------------------------------------
    |
    | The base URI for the CopyTrade API endpoints.
    |
    */

    'base_uri' => env('COPYTRADE_BASE_URI', 'https://papi.copy-trade.io'),

    /*
    |--------------------------------------------------------------------------
    | CopyTrade Identity Base URI
    |--------------------------------------------------------------------------
    |
    | The base URI for the CopyTrade Identity/Authentication endpoints.
    |
    */

    'identity_uri' => env('COPYTRADE_IDENTITY_URI', 'https://identity.copy-trade.io'),

    /*
    |--------------------------------------------------------------------------
    | Access Token
    |--------------------------------------------------------------------------
    |
    | Your CopyTrade API access token for authentication.
    |
    */

    'access_token' => env('COPYTRADE_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | The client identifier for your application.
    |
    */

    'client_id' => env('COPYTRADE_CLIENT_ID', 'pelican'),

    /*
    |--------------------------------------------------------------------------
    | ACR Values
    |--------------------------------------------------------------------------
    |
    | Authentication Context Class Reference values.
    | Dynamically uses the client_id: tenant:{client_id}
    |
    */

    'acr_values' => env('COPYTRADE_ACR_VALUES', function () {
        return 'tenant:'.config('copytrade.client_id');
    }),

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    |
    | The callback URL for authentication redirects.
    | Dynamically uses the client_id: {client_id}://authenticated
    |
    */

    'callback_url' => env('COPYTRADE_CALLBACK_URL', function () {
        return config('copytrade.client_id').'://authenticated';
    }),

    /*
    |--------------------------------------------------------------------------
    | Additional Configuration
    |--------------------------------------------------------------------------
    |
    | Add any additional CopyTrade configuration options here.
    |
    */

    'enabled' => env('COPYTRADE_ENABLED', true),

    'timeout' => env('COPYTRADE_TIMEOUT', 30),

];
