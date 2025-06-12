<?php
declare(strict_types=1);

namespace App\Entities;

final readonly class UserToken
{
    /**
     * @param int $id
     * @param int $userId
     * @param string $token
     * @param string|null $userAgent
     * @param string|null $ipAddress
     * @param \DateTimeImmutable|null $expiresAt
     * @param \DateTimeImmutable|null $createdAt
     * @param User $user
     */
    public function __construct(
        private int                     $id,
        private int                     $userId,
        private string                  $token,
        private string|null             $userAgent,
        private string|null             $ipAddress,
        private \DateTimeImmutable|null $expiresAt,
        private \DateTimeImmutable|null $createdAt,
        private User                    $user,
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
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @return string|null
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}