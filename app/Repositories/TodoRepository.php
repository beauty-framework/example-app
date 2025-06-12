<?php
declare(strict_types=1);

namespace App\Repositories;

use App\DTO\Todo\TaskDTO;
use App\Entities\Task;
use App\Entities\User;
use App\Repositories\Contracts\TodoRepositoryInterface;
use Beauty\Database\Connection\ConnectionInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use PDO;

class TodoRepository implements TodoRepositoryInterface
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
     * @param TaskDTO $dto
     * @return Task
     * @throws DateMalformedStringException
     */
    public function create(TaskDTO $dto): Task
    {
        $stmt = $this->connection->query(
            <<<SQL
            WITH inserted AS (
                INSERT INTO todos (user_id, title, description, is_completed, due_date)
                VALUES (?, ?, ?, ?, ?)
                RETURNING *
            )
            SELECT *
            FROM (
                SELECT 
                    inserted.id,
                    inserted.user_id,
                    inserted.title,
                    inserted.description,
                    inserted.is_completed,
                    inserted.due_date,
                    inserted.created_at,
                    inserted.updated_at,
                    u.id AS u_user_id,
                    u.name AS user_name,
                    u.email AS user_email,
                    u.password AS user_password,
                    u.created_at AS user_created_at
                FROM inserted
                JOIN users u ON u.id = inserted.user_id
            ) AS t;
            SQL,
            [
                $dto->userId,
                $dto->title,
                $dto->description,
                $dto->isCompleted ? 'true' : 'false',
                $dto->dueDate?->format('Y-m-d'),
            ]
        );

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->hydrate($data);
    }

    /**
     * @param int $id
     * @param TaskDTO $todo
     * @return Task
     * @throws DateMalformedStringException
     */
    public function update(int $id, TaskDTO $todo): Task
    {
        $this->connection->update(
            <<<SQL
            UPDATE todos
            SET title = ?, description = ?, is_completed = ?, due_date = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ? AND deleted_at IS NULL
            SQL,
            [
                $todo->title,
                $todo->description,
                $todo->isCompleted ? 'true' : 'false',
                $todo->dueDate?->format('Y-m-d H:i:s'),
                $id,
                $todo->userId
            ]
        );

        return $this->findById($id, $todo->userId);
    }

    /**
     * @param int $id
     * @param int $userId
     * @return void
     */
    public function delete(int $id, int $userId): void
    {
        $this->connection->update('UPDATE todos SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    /**
     * @param int $id
     * @param int $userId
     * @return Task|null
     * @throws DateMalformedStringException
     */
    public function findById(int $id, int $userId): ?Task
    {
        $stmt = $this->connection->query(
            <<<SQL
            SELECT 
                t.*,
                u.id AS u_user_id,
                u.name AS user_name,
                u.email AS user_email,
                u.password AS user_password,
                u.created_at AS user_created_at
            FROM todos t
            JOIN users u ON u.id = t.user_id
            WHERE t.id = ? AND t.user_id = ? AND deleted_at IS NULL
            LIMIT 1
            SQL,
            [$id, $userId]
        );

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->hydrate($data) : null;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->connection->query(
            <<<SQL
            SELECT 
                t.*,
                u.id AS u_user_id,
                u.name AS user_name,
                u.email AS user_email,
                u.password AS user_password,
                u.created_at AS user_created_at
            FROM todos t
            JOIN users u ON u.id = t.user_id
            WHERE t.user_id = ? AND deleted_at IS NULL
            ORDER BY t.created_at DESC
            SQL,
            [$userId]
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param bool $isCompleted
     * @return void
     */
    public function updateStatus(int $id, int $userId, bool $isCompleted): void
    {
        $this->connection->update(
            <<<SQL
            UPDATE todos
            SET is_completed = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND user_id = ? AND deleted_at IS NULL
            SQL,
            [
                $isCompleted ? 'true' : 'false',
                $id,
                $userId
            ]
        );
    }

    /**
     * @param array $data
     * @return Task
     * @throws DateMalformedStringException
     */
    private function hydrate(array $data): Task
    {
        $user = new User(
            id: (int) $data['u_user_id'],
            name: $data['user_name'],
            email: $data['user_email'],
            password: $data['user_password'],
            createdAt: new DateTimeImmutable($data['user_created_at']),
        );

        return new Task(
            id: (int) $data['id'],
            userId: (int) $data['user_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            isCompleted: (bool) $data['is_completed'],
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: new DateTimeImmutable($data['updated_at']),
            user: $user,
        );
    }
}