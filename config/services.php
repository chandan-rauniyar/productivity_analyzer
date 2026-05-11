<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'azure' => [
        'client_id'     => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect'      => env('AZURE_REDIRECT_URI'),

        /*
        |----------------------------------------------------------------
        | Use 'consumers' for personal Microsoft/Outlook accounts.
        | Use 'common' only if supporting BOTH personal + work accounts.
        | Use your tenant GUID only for single-org work accounts.
        |----------------------------------------------------------------
        */
        'tenant' => env('AZURE_TENANT_ID', 'consumers'),

        /*
        |----------------------------------------------------------------
        | SHORT scope names — required for personal Microsoft accounts.
        | Full URIs (https://graph.microsoft.com/Mail.Read) only work
        | for work/school Azure AD accounts, NOT personal accounts.
        |----------------------------------------------------------------
        */
        'scopes' => [
            'User.Read',
            'Mail.Read',
            'Mail.ReadBasic',
            'Calendars.Read',
            'offline_access',
        ],

        'guzzle' => [
            'verify' => env('AZURE_CA_BUNDLE') ?: env('AZURE_SSL_VERIFY', true),
        ],
    ],

];