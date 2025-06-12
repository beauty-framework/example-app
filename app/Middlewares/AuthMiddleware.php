<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Exceptions\UnauthorizedException;
use App\Repositories\Contracts\UserTokenRepositoryInterface;
use Beauty\Http\Middleware\AbstractMiddleware;
use Beauty\Http\Request\HttpRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends AbstractMiddleware
{
    /**
     * @param UserTokenRepositoryInterface $tokenRepository
     */
    public function __construct(
        private UserTokenRepositoryInterface $tokenRepository,
    )
    {
    }

    /**
     * @param HttpRequest $request
     * @param RequestHandlerInterface $next
     * @return ResponseInterface
     * @throws UnauthorizedException
     */
    public function handle(HttpRequest $request, RequestHandlerInterface $next): ResponseInterface
    {
        $bearer = $request->getHeaderLine('Authorization');

        if (!str_starts_with($bearer, 'Bearer ')) {
            throw new UnauthorizedException('Missing or invalid Authorization header');
        }

        $clearToken = trim(substr($bearer, 7));
        $token = $this->tokenRepository->findByToken($clearToken);

        if ($token === null) {
            throw new UnauthorizedException('Unauthorized');
        }

        $request = $request->withAttribute('user', $token->getUser());

        return $next->handle($request);
    }
}
