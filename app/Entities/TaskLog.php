<?php
declare(strict_types=1);

namespace App\Entities;

use DateTimeImmutable;

final readonly class TaskLog
{
    /**
     * @param int $id
     * @param int $todoId
     * @param int $userId
     * @param string $message
     * @param DateTimeImmutable $createdAt
     */
    public function __construct(
        private int $id,
        private int $todoId,
        private int $userId,
        private string $message,
        private DateTimeImmutable $createdAt
    ) {}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTodoId(): int
    {
        return $this->todoId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
