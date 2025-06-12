<?php
declare(strict_types=1);

namespace App\Events;

class SaveTaskInLogEvent
{
    /**
     * @param int $todoId
     * @param int $userId
     * @param string $message
     */
    public function __construct(
        public int $todoId,
        public int $userId,
        public string $message,
    )
    {
    }
}