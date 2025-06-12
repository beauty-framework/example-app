<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\User;
use App\Entities\UserToken;
use App\Repositories\Contracts\UserTokenRepositoryInterface;
use Beauty\Database\Connection\ConnectionInterface;
use PDO;

class UserTokenRepository implements UserTokenRepositoryInterface
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
     * @param string $token
     * @return UserToken|null
     * @throws \DateMalformedStringException
     */
    public function findByToken(string $token): UserToken|null
    {
        $stmt = $this->connection->query(
            <<<SQL
        SELECT 
            t.id,
            t.user_id,
            t.token,
            t.user_agent,
            t.ip_address,
            t.expires_at,
            t.created_at,
            u.id AS user_id,
            u.name AS user_name,
            u.email AS user_email,
            u.password AS user_password,
            u.created_at AS user_created_at
        FROM user_tokens t
        JOIN users u ON u.id = t.user_id
        WHERE t.token = ?
        LIMIT 1
        SQL,
            [$token]
        );

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrateUserToken($data);
    }

    /**
     * @param int $userId
     * @param string $token
     * @return UserToken
     * @throws \DateMalformedStringException
     */
    public function create(int $userId, string $token): UserToken
    {
        $stmt = $this->connection->query(
            <<<SQL
    WITH inserted AS (
        INSERT INTO user_tokens (user_id, token)
        VALUES (?, ?)
        RETURNING id, user_id, token, user_agent, ip_address, expires_at, created_at
    )
    SELECT 
        i.id, i.user_id, i.token, i.user_agent, i.ip_address, i.expires_at, i.created_at,
        u.id AS user_id, u.name AS user_name, u.email AS user_email, u.password AS user_password, u.created_at AS user_created_at
    FROM inserted i
    JOIN users u ON u.id = i.user_id;
    SQL,
            [$userId, $token]
        );

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->hydrateUserToken($data);
    }

    /**
     * @param array $data
     * @return UserToken
     * @throws \DateMalformedStringException
     */
    private function hydrateUserToken(array $data): UserToken
    {
        $user = new User(
            id: (int) $data['user_id'],
            name: $data['user_name'],
            email: $data['user_email'],
            password: $data['user_password'],
            createdAt: new \DateTimeImmutable($data['user_created_at']),
        );

        return new UserToken(
            id: (int) $data['id'],
            userId: (int) $data['user_id'],
            token: $data['token'],
            userAgent: $data['user_agent'] ?? null,
            ipAddress: $data['ip_address'] ?? null,
            expiresAt: isset($data['expires_at']) ? new \DateTimeImmutable($data['expires_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            user: $user,
        );
    }

}