<?php
declare(strict_types=1);

namespace App\Services\Todo;

use App\DTO\Todo\TaskLogDTO;
use App\Entities\TaskLog;
use App\Repositories\DatabaseTaskLogRepository;

class TaskLogsService
{
    /**
     * @param DatabaseTaskLogRepository $taskLogRepository
     */
    public function __construct(
        private DatabaseTaskLogRepository $taskLogRepository,
    )
    {
    }

    /**
     * @param int $todoId
     * @param int $userId
     * @param string $message
     * @return TaskLog
     * @throws \DateMalformedStringException
     */
    public function save(int $todoId, int $userId, string $message): TaskLog
    {
        $dto = new TaskLogDTO(
            $todoId,
            $userId,
            $message
        );

        return $this->taskLogRepository->create($dto);
    }
}