<?php
declare(strict_types=1);

use App\Container\Base;
use App\Container\Cache;
use App\Container\Config;
use App\Container\Database;
use App\Container\DI;
use App\Container\Queue;
use Beauty\Core\Container\ContainerManager;
use Beauty\Core\Container\ContainerRegistry;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/helpers.php';

$bootstrap = new class {
    /**
     * @return object
     */
    public function boot(): object
    {
        $this->loadEnv();
        $this->maybeEnableDiCache();

        $containerManager = $this->initContainerManager();
        $di = $containerManager->getContainer();

        $this->registerEventListeners($di);

        ContainerRegistry::set($di);

        return new class ($containerManager, $this->loadRouter(), $this->loadMiddlewares()) {
            public function __construct(
                public ContainerManager $containerManager,
                public array $routerConfig,
                public array $middlewares,
            ) {}
        };
    }

    /**
     * @return void
     */
    private function loadEnv(): void
    {
        $dotenv = Dotenv::createImmutable(base_path());
        $dotenv->load();
    }

    /**
     * @return void
     */
    private function maybeEnableDiCache(): void
    {
        if (env('USE_DI_CACHE', true)) {
            ContainerManager::enableCache(base_path('/bootstrap/cache/di'));
        }
    }

    /**
     * @return ContainerManager
     */
    private function initContainerManager(): ContainerManager
    {
        $containers = [];

        if (class_exists(\Beauty\Database\Connection\ConnectionFactory::class)) {
            $containers[] = Database::class;
        }

        if (class_exists(\Beauty\Cache\CacheFactory::class)) {
            $containers[] = Cache::class;
        }

        if (class_exists(\Beauty\Jobs\Dispatcher::class)) {
            $containers[] = Queue::class;
        }

        return ContainerManager::bootFrom(array_merge(
            $containers,
            [Config::class, Base::class, DI::class]
        ));
    }

    /**
     * @param \Psr\Container\ContainerInterface $di
     * @return void
     */
    private function registerEventListeners(\Psr\Container\ContainerInterface $di): void
    {
        $registry = $di->get(\Beauty\Core\Events\ListenerRegistry::class);
        $registry->setContainer($di);

        $listeners = $this->loadEventListeners();
        foreach ($listeners as $event => $handlers) {
            foreach ((array)$handlers as $handler) {
                $registry->register($event, $handler);
            }
        }
    }

    /**
     * @return array
     */
    private function loadRouter(): array
    {
        return require base_path('config/router.php');
    }

    /**
     * @return array
     */
    private function loadMiddlewares(): array
    {
        return require base_path('config/middlewares.php');
    }

    /**
     * @return array
     */
    private function loadEventListeners(): array
    {
        return require base_path('config/event-listeners.php');
    }
};

return $bootstrap->boot();
