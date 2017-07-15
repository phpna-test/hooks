<?php
return [
    'single' => env('HOOKS_SINGLE',true),
    'single_site' => [],
    'bins' => [
        'php' => null,
    ],
    'paths' => [
        'root' => base_path(),
        'web' => dirname(base_path())
    ],
    'defaults' => [
        'type' => 'gogs',
        'script' => 'normal',
        'site' => 'default'
    ],
    'route' => [
        'prefix' => 'hooks',
        'name' => 'hooks'
    ],
    'queue' => [
        'connection' => [
            'driver'  => 'sqlite',
            'database' => base_path('vendor/gkr/hooks/database.sqlite')
        ],
        'timeout' => 300,
        'tries' => 3,
        'driver' => 'sync',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 310,
    ],
    'checks' => [
        'token' => \Gkr\Hooks\Code\Checks\TokenCheck::class
    ],
    'types' => [
        'gogs' => \Gkr\Hooks\Code\Types\Gogs::class
    ],
    'scripts' => [
        'normal' => [
            'shell' => 'php',
            'file' => base_path('vendor/gkr/hooks/src/Code/scripts/normal.php')
        ],
        'composer' => [
            'shell' => 'php',
            'file' => base_path('vendor/gkr/hooks/src/Code/scripts/composer.php')
        ]
    ],
    'sites' => [
        'default' => [
            'script' => 'composer',
            'type' => 'gogs'
        ]
    ],
    'ips' => [],
];