<?php
declare(strict_types=1);

namespace App\Container;

use Beauty\Core\Container\ContainerManager;
use Beauty\Jobs\Dispatcher;
use Beauty\Jobs\Queue\RoadRunnerQueue;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Jobs\Jobs;

class Queue
{
    /**
     * @param ContainerManager $container
     * @return void
     */
    public static function configure(ContainerManager $container): void
    {
        $env = Environment::fromGlobals();
        $jobsClient = new Jobs(RPC::create($env->getRPCAddress()));

        $container->bind(Dispatcher::class, new Dispatcher(new RoadRunnerQueue($jobsClient)));
    }
}