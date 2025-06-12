<?php
declare(strict_types=1);

namespace App\Entities;

final readonly class User
{
    /**
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string $password
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        private int    $id,
        private string $name,
        private string $email,
        private string $password,
        private \DateTimeImmutable $createdAt,
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}