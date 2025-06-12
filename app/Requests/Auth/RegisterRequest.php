<?php
declare(strict_types=1);

namespace App\Requests\Auth;

use Beauty\Http\Request\AbstractValidatedRequest;

class RegisterRequest extends AbstractValidatedRequest
{
    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
