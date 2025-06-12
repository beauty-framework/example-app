<?php
declare(strict_types=1);

namespace App\DTO\Todo;

use DateTimeInterface;

final readonly class TaskDTO
{
    /**
     * @param int $userId
     * @param string $title
     * @param string|null $description
     * @param DateTimeInterface|null $dueDate
     * @param bool $isCompleted
     */
    public function __construct(
        public int $userId,
        public string $title,
        public string|null $description,
        public DateTimeInterface|null $dueDate,
        public bool $isCompleted = false,
    )
    {
    }
}