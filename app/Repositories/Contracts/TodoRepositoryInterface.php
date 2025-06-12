<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTO\Todo\TaskDTO;
use App\Entities\Task;

interface TodoRepositoryInterface
{
    /**
     * @param TaskDTO $todo
     * @return Task
     */
    public function create(TaskDTO $todo): Task;

    /**
     * @param int $id
     * @param TaskDTO $todo
     * @return Task
     */
    public function update(int $id, TaskDTO $todo): Task;

    /**
     * @param int $id
     * @param int $userId
     * @return void
     */
    public function delete(int $id, int $userId): void;

    /**
     * @param int $id
     * @param int $userId
     * @return Task|null
     */
    public function findById(int $id, int $userId): Task|null;

    /**
     * @param int $userId
     * @return array
     */
    public function findByUserId(int $userId): array;

    /**
     * @param int $id
     * @param int $userId
     * @param bool $isCompleted
     * @return void
     */
    public function updateStatus(int $id, int $userId, bool $isCompleted): void;
}