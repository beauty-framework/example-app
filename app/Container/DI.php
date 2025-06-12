<?php
declare(strict_types=1);

namespace App\Container;


use App\Repositories\Contracts\TaskLogRepositoryInterface;
use App\Repositories\Contracts\TodoRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTokenRepositoryInterface;
use App\Repositories\DatabaseTaskLogRepository;
use App\Repositories\TodoRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserTokenRepository;
use Beauty\Core\Container\ContainerManager;
use Psr\Log\LoggerInterface;
use Spiral\RoadRunner\Logger;

class DI
{
    /**
     * @param ContainerManager $container
     * @return void
     */
    public static function configure(ContainerManager $container): void
    {
        $container->singleton(LoggerInterface::class, fn() => new Logger('stdout'));

        $container->bind(UserRepositoryInterface::class, UserRepository::class);
        $container->bind(UserTokenRepositoryInterface::class, UserTokenRepository::class);
        $container->bind(TodoRepositoryInterface::class, TodoRepository::class);
        $container->bind(TaskLogRepositoryInterface::class, DatabaseTaskLogRepository::class);
    }
}