<?php
declare(strict_types=1);

namespace App\Exceptions;

use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'ServerError',
    properties: [
        new OAT\Property(property: 'message', type: 'string', example: 'Internal Server Error'),
        new OAT\Property(property: 'errors', type: 'array', example: null),
    ]
)]
class ServerErrorException extends \Exception
{
}