<?php
declare(strict_types=1);

namespace App\Controllers;

use Beauty\OpenApi\Http\Controllers\BaseOpenApiController;
use OpenApi\Attributes as OAT;

#[OAT\OpenApi(openapi: OAT\OpenApi::VERSION_3_1_0, security: [['bearerAuth' => []]])]
#[OAT\Info(
    version: '1.0.0',
    description: 'Base ToDo List API, created in Beauty Framework',
    title: 'ToDo List API',
    contact: new OAT\Contact(name: 'Beauty Team', url: 'https://github.com/beauty-framework'),
    license: new OAT\License(name: 'MIT License', identifier: 'MIT'),
    attachables: [new OAT\Attachable()]
)]
#[OAT\Server(url: 'http://localhost:8080/api', description: 'API server')]
#[OAT\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', description: 'Basic Auth', scheme: 'bearer')]
#[OAT\Tag(name: 'Auth', description: 'Auth API')]
#[OAT\Tag(name: 'Tasks', description: 'Tasks API')]
class ApiController extends BaseOpenApiController
{
}