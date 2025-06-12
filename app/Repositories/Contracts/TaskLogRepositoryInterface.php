<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTO\Todo\TaskLogDTO;
use App\Entities\TaskLog;

interface TaskLogRepositoryInterface
{
    /**
     * @param TaskLogDTO $log
     * @return TaskLog
     */
    public function create(TaskLogDTO $log): TaskLog;
}