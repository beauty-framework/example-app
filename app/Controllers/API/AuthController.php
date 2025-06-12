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

class AuthController
{
    public function __construct(
        protected AuthService $authService,
    )
    {
    }

    #[Route(method: HttpMethodsEnum::POST, path: '/api/auth/login')]
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

    #[Route(method: HttpMethodsEnum::POST, path: '/api/auth/register')]
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