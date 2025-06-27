<?php
declare(strict_types=1);

namespace App\Controllers\API;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Requests\Auth\LoginRequest;
use App\Requests\Auth\RegisterRequest;
use App\Responses\Auth\AuthResponse;
use App\Services\Auth\AuthService;
use Beauty\Core\Router\Route;
use Beauty\Http\Enums\HttpMethodsEnum;
use Beauty\Http\Response\Contracts\ResponsibleInterface;
use Beauty\Http\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use OpenApi\Attributes as OAT;

class AuthController
{
    /**
     * @param AuthService $authService
     */
    public function __construct(
        protected AuthService $authService,
    )
    {
    }

    /**
     * @param LoginRequest $request
     * @return ResponsibleInterface
     * @throws \App\Exceptions\ServerErrorException
     * @throws \Beauty\Core\Router\Exceptions\NotFoundException
     * @throws \Beauty\Http\Request\Exceptions\ValidationException
     * @throws \Random\RandomException
     */
    #[Route(method: HttpMethodsEnum::POST, path: '/api/auth/login')]
    #[OAT\Post(
        path: '/auth/login',
        operationId: 'Login',
        security: [],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'application/json',
                schema: new OAT\Schema(ref: '#/components/schemas/LoginRequest')
            )
        ),
        tags: ['Auth'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful response',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/AuthResponse')
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Bad request',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/AuthError')
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Not found',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
    public function login(LoginRequest $request): ResponsibleInterface
    {
        $dto = new LoginDTO(
            email: $request->json('email'),
            password: $request->json('password'),
        );

        $user = $this->authService->login($dto);

        return new AuthResponse(
            $user->getToken(),
            $user->getUser()->getName(),
            $user->getUser()->getEmail(),
        );
    }

    /**
     * @param RegisterRequest $request
     * @return ResponsibleInterface
     * @throws \App\Exceptions\ServerErrorException
     */
    #[Route(method: HttpMethodsEnum::POST, path: '/api/auth/register')]
    #[OAT\Post(
        path: '/auth/register',
        operationId: 'Register',
        security: [],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'application/json',
                schema: new OAT\Schema(ref: '#/components/schemas/RegisterRequest')
            )
        ),
        tags: ['Auth'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful response',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/AuthResponse')
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Bad request',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/AuthError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
    public function register(RegisterRequest $request): ResponsibleInterface
    {
        $dto = new RegisterDTO(
            name: $request->json('name'),
            email: $request->json('email'),
            password: $request->json('password'),
        );

        $user = $this->authService->register($dto);

        return new AuthResponse(
            $user->getToken(),
            $user->getUser()->getName(),
            $user->getUser()->getEmail(),
        );
    }
}