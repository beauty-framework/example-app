<?php
declare(strict_types=1);

namespace App\Requests\Auth;

use Beauty\Http\Request\AbstractValidatedRequest;

class LoginRequest extends AbstractValidatedRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
