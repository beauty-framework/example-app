<?php
declare(strict_types=1);

namespace App\Repositories;

use App\DTO\Todo\TaskLogDTO;
use App\Entities\TaskLog;
use App\Repositories\Contracts\TaskLogRepositoryInterface;
use Beauty\Database\Connection\ConnectionInterface;

class DatabaseTaskLogRepository implements TaskLogRepositoryInterface
{
    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(
        private ConnectionInterface $connection,
    )
    {
    }

    /**
     * @param TaskLogDTO $log
     * @return TaskLog
     * @throws \DateMalformedStringException
     */
    public function create(TaskLogDTO $log): TaskLog
    {
        $statement = $this->connection->query(<<<SQL
            INSERT INTO task_logs (todo_id, user_id, message)
            VALUES (?, ?, ?)
            RETURNING id, todo_id, user_id, message, created_at
            SQL,
            [
                $log->todoId,
                $log->userId,
                $log->message
            ]
        );

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        return new TaskLog(
            $data['id'],
            $data['todo_id'],
            $data['user_id'],
            $data['message'],
            new \DateTimeImmutable($data['created_at']),
        );
    }
}