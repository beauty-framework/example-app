<?php
declare(strict_types=1);

namespace App\Entities;

use DateTimeImmutable;

final readonly class Task
{
    /**
     * @param int $id
     * @param int $userId
     * @param string $title
     * @param string|null $description
     * @param bool $isCompleted
     * @param DateTimeImmutable|null $dueDate
     * @param DateTimeImmutable $createdAt
     * @param DateTimeImmutable $updatedAt
     * @param User $user
     */
    public function __construct(
        private int $id,
        private int $userId,
        private string $title,
        private string|null $description,
        private bool $isCompleted,
        private DateTimeImmutable|null $dueDate,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private User $user,
    )
    {
    }

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
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDueDate(): ?DateTimeImmutable
    {
        return $this->dueDate;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
