<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Entities\User;
use App\Entities\UserToken;
use App\Exceptions\ServerErrorException;
use App\Jobs\LogUserJob;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTokenRepositoryInterface;
use Beauty\Core\Router\Exceptions\NotFoundException;
use Beauty\Database\Connection\ConnectionInterface;
use Beauty\Database\Connection\Exceptions\QueryException;
use Beauty\Http\Request\Exceptions\ValidationException;
use Beauty\Jobs\Dispatcher;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

class AuthService
{
    /**
     * @param NativePasswordHasher $hasher
     * @param ConnectionInterface $connection
     * @param UserRepositoryInterface $userRepository
     * @param UserTokenRepositoryInterface $tokenRepository
     * @param LoggerInterface $logger
     * @param Dispatcher $dispatcher
     */
    public function __construct(
        protected NativePasswordHasher $hasher,
        protected ConnectionInterface $connection,
        protected UserRepositoryInterface $userRepository,
        protected UserTokenRepositoryInterface $tokenRepository,
        protected LoggerInterface $logger,
        protected Dispatcher $dispatcher,
    )
    {
    }

    /**
     * @param LoginDTO $dto
     * @return UserToken
     * @throws NotFoundException
     * @throws RandomException
     * @throws ServerErrorException
     * @throws ValidationException
     */
    public function login(LoginDTO $dto): UserToken
    {
        try {
            $user = $this->userRepository->findByEmail($dto->email);

            if (!$user) {
                throw new NotFoundException('User not found');
            }

            if (!$this->hasher->verify($user->getPassword(), $dto->password)) {
                throw new ValidationException('Invalid credentials');
            }

            return $this->tokenRepository->create($user->getId(), $this->generateToken());
        } catch (QueryException $exception) {
            $this->logger->error($exception->getMessage());
            throw new ServerErrorException('Server error');
        }
    }

    /**
     * @param RegisterDTO $dto
     * @return UserToken
     * @throws ServerErrorException
     */
    public function register(RegisterDTO $dto): UserToken
    {
        try {
            return $this->connection->transaction(function (ConnectionInterface $tx) use ($dto) {
                $dto->password = $this->hasher->hash($dto->password);

                $user = $this->userRepository->create($dto);

                $token = $this->generateToken();

                $userToken = $this->tokenRepository->create($user->getId(), $token);

                $this->dispatcher->dispatch(new LogUserJob($user->getId(), $user->getEmail()));

                return $userToken;
            });
        } catch (QueryException $exception) {
            $this->logger->error($exception->getMessage());
            throw new ServerErrorException('Server error');
        }
    }

    /**
     * @return string
     * @throws RandomException
     */
    protected function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}