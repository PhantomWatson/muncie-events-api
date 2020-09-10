<?php

use App\Error\AppExceptionRenderer;
use Cake\Cache\Engine\FileEngine;

$config = [
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    'App' => [
        'fullBaseUrl' => env('FULL_BASE_URL'),
    ],

    'Asset' => [
        'timestamp' => 'force',
    ],

    'Cache' => [
        'daily' => [
            'className' => FileEngine::class,
            'path' => CACHE,
            'duration' => '+1 day',
            'url' => env('CACHE_DAILY_URL', null),
        ],
    ],

    'Cors' => [
        'AllowOrigin' => [
            'https://muncieevents.com',
            'https://api.muncieevents.com',
        ],
    ],

    'Datasources' => [
        'default' => [
            'username' => env('DB_USERNAME') ?: 'root',
            'password' => env('DB_PASSWORD') ?: null,
            'database' => env('DB_DATABASE') ?: 'okbvtfr_muncieevents',
            'url' => env('DATABASE_URL', null),
            'encoding' => 'utf8mb4',
        ],

        'test' => [
            'username' => env('DB_USERNAME') ?: 'root',
            'password' => env('DB_PASSWORD') ?: null,
            'database' => 'test_myapp',
            'encoding' => 'utf8mb4',
        ],
    ],

    'Error' => [
        'exceptionRenderer' => AppExceptionRenderer::class,
    ],

    'Log' => [
        'email' => [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => LOGS,
            'file' => 'email',
            'levels' => ['info'],
        ],
    ],

    'EmailTransport' => [
        'default' => [
            'host' => 'localhost',
            'port' => 25,
            'username' => 'automailer@muncieevents.com',
            'password' => env('AUTOMAILER_PASSWORD'),
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => ['automailer@MuncieEvents.com' => 'Muncie Events'],
            'sender' => ['automailer@MuncieEvents.com' => 'Muncie Events'],
            'returnPath' => 'automailer@MuncieEvents.com',
            'emailFormat' => 'both',
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
        'contact_form' => [
            'transport' => 'default',
            'from' => ['automailer@MuncieEvents.com' => 'Muncie Events'],
            'sender' => ['automailer@MuncieEvents.com' => 'Muncie Events'],
            'returnPath' => 'automailer@MuncieEvents.com',
            'emailFormat' => 'both',
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
    ],

    'cookie_key' => env('COOKIE_KEY', 'cookie key'),
    'mainSiteBaseUrl' => 'https://muncieevents.com',
    'adminEmail' => 'admin@muncieevents.com',
    'password_reset_salt' => env('PASSWORD_RESET_SALT'),
    'automailer_address' => 'automailer@muncieevents.com',
    'categoryIconBaseUrl' => env('CATEGORY_ICON_BASE_URL'),
    'eventImageBaseUrl' => env('EVENT_IMG_BASE_URL'),
    'eventImagePath' => env('EVENT_IMG_PATH'),
    'slackWebhook' => env('SLACK_WEBHOOK'),
    'googleAnalyticsId' => 'UA-10610808-13',
    'localTimezone' => 'America/Indiana/Indianapolis',
];

if ($config['debug']) {
    // In debug mode, use Debug transport class for mail delivery
    $config['EmailTransport']['default']['className'] = 'Debug';

    // Log all emails in debug mode
    foreach ($config['Email'] as $label => $emailConfig) {
        $config['Email'][$label]['log'] = [
            'level' => 'info',
            'scope' => 'email',
        ];
    }
}

return $config;
