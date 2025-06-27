<?php
declare(strict_types=1);

namespace App\Responses\Auth;

use Beauty\Http\Response\AbstractJsonResource;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'AuthResponse',
    required: ['token', 'name', 'email'],
    properties: [
        new OAT\Property(property: 'token', type: 'string'),
        new OAT\Property(property: 'name', type: 'string'),
        new OAT\Property(property: 'email', type: 'string'),
    ],
    type: 'object',
)]
class AuthResponse extends AbstractJsonResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = ['token', 'name', 'email'];

    /**
     * @param string $token
     * @param string $name
     * @param string $email
     */
    public function __construct(
        public string $token,
        public string $name,
        public string $email,
    )
    {
    }
}