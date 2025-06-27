<?php
declare(strict_types=1);

namespace App\Exceptions;

use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'AuthError',
    properties: [
        new OAT\Property(property: 'message', type: 'string'),
        new OAT\Property(property: 'fails', type: 'object'),
    ]
)]
class UnauthorizedException extends \Exception
{

}