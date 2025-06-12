<?php
declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
            'prefix' => env('REDIS_CACHE_PREFIX', 'beauty:cache:'),
            'auth' => [
                'username' => env('REDIS_USERNAME'),
                'password' => env('REDIS_PASSWORD'),
            ]
        ],

        /**
         * First, you need to configure the RoadRunner .rr.yaml file
         *
         * @see https://docs.roadrunner.dev/docs/key-value/overview-kv
         */
        'roadrunner-kv' => [
            'driver' => 'roadrunner-kv',
            'rpc' => env('RR_KV_RPC', \Spiral\RoadRunner\Environment::fromGlobals()->getRPCAddress()),
            'store' => env('RR_KV_STORE', 'redis'),
        ],

        'array' => [
            'driver' => 'array',
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache'),
        ],

        'memory' => [
            'driver' => 'lru',
            'size' => env('LRU_CACHE_CAPACITY', 1000),
        ]
    ],
];