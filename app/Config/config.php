<?php

return [
    'app' => [
        'name' => 'ServiceDesk',
        'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost:8000',
        'locale' => getenv('APP_LOCALE') ?: 'ru',
        'locales' => ['ru', 'en'],
    ],
    'db' => [
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=servicedesk;charset=utf8mb4',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
    'uploads' => [
        'path' => __DIR__ . '/../../public/uploads',
        'url' => '/uploads',
        'max_size' => 10 * 1024 * 1024,
        'allowed' => ['pdf', 'png', 'jpg', 'jpeg', 'gif'],
    ],
    'security' => [
        'session_name' => 'sd_session',
        'password_algo' => PASSWORD_BCRYPT,
    ],
    'branding' => [
        'default_primary' => '#2563eb',
        'default_secondary' => '#14b8a6',
    ],
    'oidc' => [
        'enabled' => getenv('OIDC_ENABLED') === 'true',
        'discovery_url' => getenv('OIDC_DISCOVERY_URL') ?: '',
        'client_id' => getenv('OIDC_CLIENT_ID') ?: '',
        'client_secret' => getenv('OIDC_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('OIDC_REDIRECT_URI') ?: '',
        'scopes' => getenv('OIDC_SCOPES') ?: 'openid profile email',
        'issuer' => getenv('OIDC_ISSUER') ?: '',
    ],
    'ai' => [
        'enabled' => getenv('AI_ENABLED') === 'true',
        'provider' => getenv('AI_PROVIDER') ?: 'openai',
        'api_key' => getenv('AI_API_KEY') ?: '',
        'endpoint' => getenv('AI_ENDPOINT') ?: 'https://api.openai.com/v1/chat/completions',
    ],
    'one_c' => [
        'enabled' => getenv('ONEC_ENABLED') === 'true',
        'providers' => [
            'itilium' => [
                'base_url' => getenv('ONEC_ITILIUM_URL') ?: '',
                'token' => getenv('ONEC_ITILIUM_TOKEN') ?: '',
            ],
            'enterprise' => [
                'base_url' => getenv('ONEC_ENTERPRISE_URL') ?: '',
                'token' => getenv('ONEC_ENTERPRISE_TOKEN') ?: '',
            ],
        ],
        'sync' => [
            'tickets' => getenv('ONEC_SYNC_TICKETS') === 'true',
            'directories' => getenv('ONEC_SYNC_DIRECTORIES') === 'true',
            'knowledge' => getenv('ONEC_SYNC_KNOWLEDGE') === 'true',
        ],
    ],
];
