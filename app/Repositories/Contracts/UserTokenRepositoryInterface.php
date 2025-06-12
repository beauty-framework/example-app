<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Entities\UserToken;

interface UserTokenRepositoryInterface
{
    public function findByToken(string $token): UserToken|null;
    public function create(int $userId, string $token): UserToken;
}