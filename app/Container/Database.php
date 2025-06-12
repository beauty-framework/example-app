<?php
declare(strict_types=1);

namespace App\Container;

use Beauty\Core\Container\ContainerManager;
use Beauty\Database\Connection\ConnectionFactory;
use Beauty\Database\Connection\ConnectionInterface;
use Beauty\Database\Connection\ConnectionRegistry;
use Beauty\Database\Connection\Drivers\PdoMysqlDriver;
use Beauty\Database\Connection\Drivers\PdoPgsqlDriver;
use Beauty\Database\Connection\Drivers\PdoSqliteDriver;
use Beauty\Database\Connection\Drivers\PdoSqlsrvDriver;

class Database
{
    /**
     * @param ContainerManager $container
     * @return void
     */
    public static function configure(ContainerManager $container): void
    {
        $configs = require base_path('config/database.php');

        $factory = new ConnectionFactory([
            new PdoPgsqlDriver(),
            new PdoMysqlDriver(),
            new PdoSqliteDriver(),
            new PdoSqlsrvDriver(),
        ]);

        $registry = new ConnectionRegistry(
            $configs['connections'] ?? [],
            $factory,
            $configs['default'] ?? 'default',
        );

        $container->instance(ConnectionFactory::class, $factory);
        $container->instance(ConnectionRegistry::class, $registry);

        $connection = $registry->get();
        $container->instance(ConnectionInterface::class, $connection);
    }
}