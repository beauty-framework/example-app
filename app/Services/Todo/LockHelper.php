<?php
declare(strict_types=1);

namespace App\Services\Todo;

use RoadRunner\Lock\LockInterface;

final class LockHelper
{
    /**
     * @param int $id
     * @param int $userId
     * @return string
     */
    public static function getLockKey(int $id, int $userId): string
    {
        return "task:$id:$userId";
    }

    /**
     * @param LockInterface $lock
     * @param int $id
     * @param int $userId
     * @return string|false
     */
    public static function tryLock(LockInterface $lock, int $id, int $userId): string|false
    {
        $lockKey = self::getLockKey($id, $userId);
        return $lock->lock($lockKey, ttl: 60);
    }

    /**
     * @param LockInterface $lock
     * @param int $id
     * @param int $userId
     * @param string $lockId
     * @return void
     */
    public static function releaseLock(LockInterface $lock, int $id, int $userId, string $lockId): void
    {
        $lockKey = self::getLockKey($id, $userId);
        $lock->release($lockKey, $lockId);
    }
}