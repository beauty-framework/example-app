<?php
declare(strict_types=1);

namespace App\Listeners;

use App\DTO\Todo\TaskLogDTO;
use App\Events\SaveTaskInLogEvent;
use App\Repositories\DatabaseTaskLogRepository;
use App\Services\Todo\TaskLogsService;
use Beauty\Database\Connection\Exceptions\QueryException;
use DateMalformedStringException;
use Psr\Log\LoggerInterface;

class TaskLogInDBListener
{
    /**
     * @param TaskLogsService $taskLogsService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private TaskLogsService $taskLogsService,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * @param SaveTaskInLogEvent $event
     * @return void
     */
    public function handle(SaveTaskInLogEvent $event): void
    {
        try {
            $taskLog = $this->taskLogsService->save(
                $event->todoId,
                $event->userId,
                $event->message
            );

            $this->logger->info('Task log created', [
                'id' => $taskLog->getId(),
                'todo_id' => $taskLog->getTodoId(),
            ]);
        } catch (QueryException|DateMalformedStringException $exception) {
            $this->logger->error('Create task log error: ' . $exception->getMessage());
        }
    }
}