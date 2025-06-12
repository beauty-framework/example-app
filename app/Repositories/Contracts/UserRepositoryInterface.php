<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTO\Auth\RegisterDTO;
use App\Entities\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): User|null;
    public function create(RegisterDTO $dto): User;
}