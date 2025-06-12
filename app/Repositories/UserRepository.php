<?php
declare(strict_types=1);

namespace App\Repositories;

use App\DTO\Auth\RegisterDTO;
use App\Entities\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Beauty\Database\Connection\ConnectionInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
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
     * @param string $email
     * @return User|null
     * @throws \DateMalformedStringException
     */
    public function findByEmail(string $email): User|null
    {
        $stmt = $this->connection->query(
            'SELECT id, name, email, password, created_at FROM users WHERE email = ? LIMIT 1',
            [$email]
        );

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrateUser($data);
    }

    /**
     * @param RegisterDTO $dto
     * @return User
     * @throws \DateMalformedStringException
     */
    public function create(RegisterDTO $dto): User
    {
        $stmt = $this->connection->query('INSERT INTO users (name, email, password) VALUES (?, ?, ?) RETURNING id, name, email, password, created_at', [
            $dto->name,
            $dto->email,
            $dto->password
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->hydrateUser($data);
    }

    /**
     * @param array $data
     * @return User
     * @throws \DateMalformedStringException
     */
    private function hydrateUser(array $data): User
    {
        return new User(
            id: (int) $data['id'],
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            createdAt: new \DateTimeImmutable($data['created_at']),
        );
    }
}