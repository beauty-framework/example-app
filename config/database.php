<?php
declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'pgsql'),
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'app'),
            'username' => env('DB_USERNAME', 'user'),
            'password' => env('DB_PASSWORD', 'secret'),
            'charset' => 'utf8',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'app'),
            'username' => env('DB_USERNAME', 'user'),
            'password' => env('DB_PASSWORD', 'secret'),
            'charset' => 'utf8mb4',
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', 'dev.sqlite'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 1433),
            'database' => env('DB_DATABASE', 'app'),
            'username' => env('DB_USERNAME', 'user'),
            'password' => env('DB_PASSWORD', 'secret'),
            'encrypt' => env('DB_ENCRYPT', 'no'),
            'trust_cert' => env('DB_TRUST_CERT', 'yes'),
        ],
    ],

];