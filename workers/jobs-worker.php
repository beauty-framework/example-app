<?php
declare(strict_types=1);

use Beauty\Jobs\JobRunner;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Task\Factory\ReceivedTaskFactory;
use Spiral\RoadRunner\Worker;

$application = require __DIR__ . '/../bootstrap/kernel.php';

if (!class_exists(\Beauty\Jobs\Dispatcher::class)) {
    exit(0);
}

$worker = Worker::create();
$factory = new ReceivedTaskFactory($worker);

$consumer = new Consumer($worker, $factory);

$runner = new JobRunner($consumer, $application->containerManager->getContainer());
$runner->run();