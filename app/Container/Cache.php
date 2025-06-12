<?php
declare(strict_types=1);

namespace App\Container;

use Beauty\Cache\CacheFactory;
use Beauty\Cache\CacheRegistry;
use Beauty\Cache\Drivers\ArrayCacheDriver;
use Beauty\Cache\Drivers\FileCacheDriver;
use Beauty\Cache\Drivers\KVCacheDriver;
use Beauty\Cache\Drivers\LruCacheDriver;
use Beauty\Cache\Drivers\RedisCacheDriver;
use Beauty\Core\Container\ContainerManager;
use Psr\SimpleCache\CacheInterface;

class Cache
{
    /**
     * @param ContainerManager $container
     * @return void
     */
    public static function configure(ContainerManager $container): void
    {
        $configs = require base_path('config/cache.php');

        $factory = new CacheFactory([
            new RedisCacheDriver(),
            new KVCacheDriver(),
            new ArrayCacheDriver(),
            new FileCacheDriver(),
            new LruCacheDriver(),
        ]);

        $registry = new CacheRegistry(
            $configs['stores'],
            $factory,
            $configs['default'] ?? 'default',
        );

        $container->instance(CacheFactory::class, $factory);
        $container->instance(CacheRegistry::class, $registry);

        $connection = $registry->get();
        $container->instance(CacheInterface::class, $connection);
    }
}