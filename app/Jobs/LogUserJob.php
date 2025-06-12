<?php
declare(strict_types=1);

namespace App\Jobs;

use Beauty\Jobs\AbstractJob;
use Psr\Log\LoggerInterface;

class LogUserJob extends AbstractJob
{
    /**
     * @param int $userId
     * @param string $email
     */
    public function __construct(
        private int $userId,
        private string $email,
    )
    {
    }

    /**
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(): void
    {
        /** @var LoggerInterface $logger */
        $logger = $this->container->get(LoggerInterface::class);

        $logger->info('User registered', [
            'id' => $this->userId,
            'email' => $this->email,
            'timestamp' => time(),
        ]);
    }
}
