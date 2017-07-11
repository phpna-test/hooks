<?php
return [
    'single' => true,
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
            'database' => __DIR__.'/../data/database.sqlite'
        ],
        'driver' => 'sync',
        'table' => 'jobs',
        'queue' => 'hooks',
        'retry_after' => 60,
    ],
    'checks' => [
        'token' => PHPNa\Hooks\Checks\TokenCheck::class
    ],
    'types' => [
        'gogs' => PHPNa\Hooks\Types\Gogs::class
    ],
    'scripts' => [
        'normal' => [
            'shell' => 'php',
            'file' => base_path('packages/hooks/src/Scripts/normal.php')
        ],
        'composer' => [
            'shell' => 'php',
            'file' => base_path('packages/hooks/src/Scripts/composer.php')
        ]
    ],
    'sites' => [
        'default' => [
            'script' => 'composer',
            'type' => 'gogs'
        ],
        'blog' => [
            'repository' => 'git@git.phpna.com:phpna/blog.git',
            'checks' => ['token'],
//            'prefix' => 'sudo -Hu www'
        ]
    ],
    'ips' => [],
];