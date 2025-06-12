<?php
declare(strict_types=1);

namespace App\Container;

use Beauty\Core\Config\ConfigLoader;
use Beauty\Core\Config\ConfigRepository;
use Beauty\Core\Container\ContainerManager;

class Config
{
    /**
     * @param ContainerManager $container
     * @return void
     */
    public static function configure(ContainerManager $container): void
    {
        $configPath = base_path('config');
        $cachePath = base_path('bootstrap/cache/config.php');

        $loader = new ConfigLoader($configPath);
        $configData = file_exists($cachePath)
            ? $loader->loadFromCache($cachePath)
            : $loader->load();

        $config = new ConfigRepository($configData);

        $container->bind(ConfigRepository::class, $config);

        set_config_repository($config);
    }
}