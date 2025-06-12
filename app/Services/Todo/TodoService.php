<?php
declare(strict_types=1);

namespace App\Services\Todo;

use App\DTO\Todo\TaskDTO;
use App\Entities\Task;
use App\Events\SaveTaskInLogEvent;
use App\Exceptions\ConflictHttpException;
use App\Exceptions\ServerErrorException;
use App\Repositories\Contracts\TodoRepositoryInterface;
use Beauty\Core\Router\Exceptions\NotFoundException;
use Beauty\Database\Connection\ConnectionInterface;
use Beauty\Database\Connection\Exceptions\QueryException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RoadRunner\Lock\LockInterface;

class TodoService
{
    const int CACHE_TTL = 60*30; // 30 minutes

    /**
     * @param ConnectionInterface $connection
     * @param TodoRepositoryInterface $todoRepository
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param LockInterface $lock
     * @param CacheInterface $cache
     */
    public function __construct(
        protected ConnectionInterface      $connection,
        protected TodoRepositoryInterface  $todoRepository,
        protected LoggerInterface          $logger,
        protected EventDispatcherInterface $eventDispatcher,
        protected LockInterface            $lock,
        protected CacheInterface           $cache,
    )
    {
    }

    /**
     * @param int $userId
     * @return array
     * @throws InvalidArgumentException
     */
    public function allByUserId(int $userId): array
    {
        $cacheKey = "todos:$userId";

        $todos = $this->cache->get($cacheKey);

        if (!$todos) {
            $todos = $this->todoRepository->findByUserId($userId);
            $this->cache->set($cacheKey, $todos, self::CACHE_TTL);
        }

        return $todos;
    }

    /**
     * @param int $id
     * @param int $userId
     * @return Task
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function get(int $id, int $userId): Task
    {
        $cacheKey = "todos:$id:$userId";

        $task = $this->cache->get($cacheKey);

        if (!$task) {
            $task = $this->todoRepository->findById($id, $userId);

            if (!$task) {
                throw new NotFoundException('Task not found');
            }

            $this->cache->set($cacheKey, $task, self::CACHE_TTL);
        }

        return $task;
    }

    /**
     * @param TaskDTO $todo
     * @return Task
     * @throws ServerErrorException
     */
    public function create(TaskDTO $todo): Task
    {
        try {
            return $this->connection->transaction(function (ConnectionInterface $tx) use ($todo) {
                $task = $this->todoRepository->create($todo);

                $this->changeCache($task->getId(), $todo->userId, $task);

                $this->eventDispatcher->dispatch(new SaveTaskInLogEvent($task->getId(), $task->getUserId(), 'Task created'));

                return $task;
            });
        } catch (QueryException $exception) {
            $this->logger->error('Create todo error: ' . $exception->getMessage());
            throw new ServerErrorException('Server error');
        }
    }

    /**
     * @param int $id
     * @param TaskDTO $todo
     * @return Task
     * @throws ConflictHttpException
     * @throws ServerErrorException
     */
    public function update(int $id, TaskDTO $todo): Task
    {
        $lockKey = LockHelper::tryLock($this->lock, $id, $todo->userId);
        if ($lockKey === false) {
            throw new ConflictHttpException('Task is updating');
        }

        try {
            return $this->connection->transaction(function (ConnectionInterface $tx) use ($id, $todo) {
                $task = $this->todoRepository->update($id, $todo);

                $this->changeCache($id, $todo->userId, $task);

                $this->eventDispatcher->dispatch(new SaveTaskInLogEvent($task->getId(), $task->getUserId(), 'Task updated'));

                return $task;
            });
        } catch (QueryException $exception) {
            $this->logger->error('Update todo error: ' . $exception->getMessage());
            throw new ServerErrorException('Server error');
        } finally {
            LockHelper::releaseLock($this->lock, $id, $todo->userId, $lockKey);
        }
    }

    /**
     * @param int $id
     * @param int $userId
     * @return void
     * @throws ConflictHttpException
     * @throws ServerErrorException
     */
    public function delete(int $id, int $userId): void
    {
        $lockId = LockHelper::tryLock($this->lock, $id, $userId);
        if ($lockId === false) {
            throw new ConflictHttpException('Task is updating');
        }

        try {
            $this->connection->transaction(function (ConnectionInterface $tx) use ($id, $userId) {
                $this->todoRepository->delete($id, $userId);

                $this->deleteCache($userId);
                $this->cache->delete("todos:$id:$userId");

                $this->eventDispatcher->dispatch(new SaveTaskInLogEvent($id, $userId, 'Task deleted'));
            });
        } catch (QueryException $exception) {
            $this->logger->error('Delete todo error: ' . $exception->getMessage());
            throw new ServerErrorException('Server error');
        } finally {
            LockHelper::releaseLock($this->lock, $id, $userId, $lockId);
        }
    }

    /**
     * @param int $id
     * @param int $userId
     * @param bool $isCompleted
     * @return bool
     * @throws ConflictHttpException
     * @throws ServerErrorException
     */
    public function updateStatus(int $id, int $userId, bool $isCompleted): bool
    {
        $lockId = LockHelper::tryLock($this->lock, $id, $userId);
        if ($lockId === false) {
            throw new ConflictHttpException('Task is updating');
        }

        try {
            return $this->connection->transaction(function (ConnectionInterface $tx) use ($id, $userId, $isCompleted) {
                $this->todoRepository->updateStatus($id, $userId, $isCompleted);

                $this->deleteCache($userId);
                $this->deleteTaskCache($id, $userId);

                $this->eventDispatcher->dispatch(new SaveTaskInLogEvent($id, $userId, 'Task status updated to ' . $isCompleted));

                return true;
            });
        } catch (QueryException $exception) {
            $this->logger->error('Update todo status error: ' . $exception->getMessage());
            throw new ServerErrorException('Server error');
        } finally {
            LockHelper::releaseLock($this->lock, $id, $userId, $lockId);
        }
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Task $task
     * @param int $ttl
     * @return void
     * @throws InvalidArgumentException
     */
    protected function changeCache(int $id, int $userId, Task $task, int $ttl = self::CACHE_TTL): void
    {
        $this->deleteCache($userId);
        $this->cache->set("todos:$id:$userId", $task, $ttl);
    }

    /**
     * @param int $userId
     * @return void
     * @throws InvalidArgumentException
     */
    protected function deleteCache(int $userId): void
    {
        $this->cache->delete("todos:$userId");
    }

    /**
     * @param int $id
     * @param int $userId
     * @return void
     * @throws InvalidArgumentException
     */
    protected function deleteTaskCache(int $id, int $userId): void
    {
        $this->cache->delete("todos:$id:$userId");
    }
}