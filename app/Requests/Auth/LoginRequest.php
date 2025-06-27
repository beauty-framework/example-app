<?php
declare(strict_types=1);

namespace App\Requests\Auth;

use Beauty\Http\Request\AbstractValidatedRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OAT\Property(property: 'email', type: 'string', format: 'email'),
        new OAT\Property(property: 'password', type: 'string'),
    ]
)]
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
