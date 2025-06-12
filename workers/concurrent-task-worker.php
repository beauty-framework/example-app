<?php
declare(strict_types=1);

use function Opis\Closure\{serialize, unserialize};

require base_path('bootstrap/kernel.php');

if (!class_exists(\Beauty\Parallels\Concurrency\WorkerStrategy::class) || !class_exists(\Spiral\RoadRunner\Jobs\Jobs::class)) {
    exit(0);
}

$consumer = new \Spiral\RoadRunner\Jobs\Consumer();

while ($task = $consumer->waitTask()) {
    try {
        $callback = unserialize($task->getPayload());
        $headers = $task->getHeaders();

        $taskId = $headers['x-task-id'] ?? null;
        $tempFile = $headers['x-temp-file'] ?? null;

        fwrite(STDERR, "[Worker] taskId=$taskId\\n");
        fwrite(STDERR, "[Worker] tempFile=$tempFile\\n");

        if (is_callable($callback)) {
            $result = $callback();

            if ($tempFile) {
                file_put_contents($tempFile[0], serialize($result[0]));
            }
        }

        $task->ack();
    } catch (Throwable $e) {
        $task->requeue($e);
    }
}