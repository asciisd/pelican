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
    | When left null, defaults to "tenant:{client_id}".
    |
    */

    'acr_values' => env('COPYTRADE_ACR_VALUES', 'tenant:pelican'),

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    |
    | The callback URL for authentication redirects.
    | When left null, defaults to "{client_id}://authenticated".
    |
    */

    'callback_url' => env('COPYTRADE_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Asset URI
    |--------------------------------------------------------------------------
    |
    | The base URI for CopyTrade image assets (copier/strategy thumbnails).
    |
    */

    'asset_uri' => env('COPYTRADE_ASSET_URI', 'https://assets.copy-trade.io'),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Settings for the OAuth2 Authorization Code flow with PKCE.
    |
    | - scopes: space-separated OAuth scopes. "offline_access" is required to
    |   receive a refresh token for automatic token renewal.
    | - client_secret: leave null for the public "pelican" PKCE client.
    |
    */

    'auth' => [
        'scopes' => env('COPYTRADE_SCOPES', 'openid profile email copytrade'),
        'client_secret' => env('COPYTRADE_CLIENT_SECRET'),
    ],

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