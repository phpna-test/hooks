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
            'database' => base_path('packages/hooks/database.sqlite')
        ],
        'timeout' => 300,
        'tries' => 3,
        'driver' => 'sync',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 310,
    ],
    'checks' => [
        'token' => Gkr\Hooks\Code\Checks\TokenCheck::class
    ],
    'types' => [
        'gogs' => Gkr\Hooks\Code\Types\Gogs::class
    ],
    'scripts' => [
        'normal' => [
            'shell' => 'php',
            'file' => base_path('packages/hooks/src/code/scripts/normal.php')
        ],
        'composer' => [
            'shell' => 'php',
            'file' => base_path('packages/hooks/src/code/scripts/composer.php')
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