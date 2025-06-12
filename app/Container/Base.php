<?php
declare(strict_types=1);

namespace App\Container;

use Beauty\Core\Container\ContainerManager;
use Beauty\Core\Events\EventDispatcher;
use Beauty\Core\Events\ListenerProvider;
use Beauty\Core\Events\ListenerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use RoadRunner\Lock\Lock;
use RoadRunner\Lock\LockInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;

class Base
{
    /**
     * @param ContainerManager $container
     * @return void
     */
    public static function configure(ContainerManager $container): void
    {
        $eventListenerProvider = new ListenerProvider();
        $registry = new ListenerRegistry($eventListenerProvider);
        $dispatcher = new EventDispatcher($eventListenerProvider);

        $container->bind(ListenerProviderInterface::class, $eventListenerProvider);
        $container->bind(ListenerRegistry::class, $registry);
        $container->bind(EventDispatcherInterface::class, $dispatcher);

        $container->bind(LockInterface::class, new Lock(RPC::create(Environment::fromGlobals()->getRPCAddress())));

        if (class_exists(\Redis::class)) {
            $container->bind(\Redis::class, function () {
                $redis = new \Redis();
                $redis->connect(env('REDIS_HOST', '127.0.0.1'), env('REDIS_PORT', 6379));
                $redis->auth([
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                ]);
                return $redis;
            });
        }

        if (class_exists(\Spiral\RoadRunner\KeyValue\Factory::class)) {
            $container->bind(\Spiral\RoadRunner\KeyValue\Factory::class, function () {
                $kvConfig = require base_path('config/kv-storage.php');

                return (new \Spiral\RoadRunner\KeyValue\Factory(RPC::create(Environment::fromGlobals()->getRPCAddress())))
                    ->select($kvConfig['default']);
            });
        }
    }
}