<?php
declare(strict_types=1);

namespace App\DTO\Auth;

final class LoginDTO
{
    /**
     * @param string $email
     * @param string $password
     */
    public function __construct(
        public string $email,
        public string $password,
    )
    {
    }
}