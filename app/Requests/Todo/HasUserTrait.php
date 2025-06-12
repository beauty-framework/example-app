<?php
declare(strict_types=1);

namespace App\Requests\Todo;

use App\Entities\User;

trait HasUserTrait
{
    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->getAttribute('user');
    }
}