<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Beauty\Cli\Console\AbstractCommand;
use Beauty\Cli\CliOutput;
use Beauty\Cli\CLI;
use Beauty\Database\Connection\ConnectionInterface;
use Beauty\Database\Connection\Exceptions\QueryException;

class MigrationsCommand extends AbstractCommand
{
    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(
        protected ConnectionInterface $connection,
    )
    {
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'migrate';
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return 'Run migrations (before we are create ORM library :) )';
    }

    /**
     * @param array $args
     * @return int
     */
    public function handle(array $args): int
    {
        CliOutput::info('Start migrations!');

        try {
            $this->connection->transaction(function (ConnectionInterface $tx) {
                $tx->raw(<<<SQL
                CREATE TABLE IF NOT EXISTS users  (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(150) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                SQL);

                $tx->raw(<<<SQL
                CREATE TABLE IF NOT EXISTS user_tokens (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL,
                    token TEXT NOT NULL,
                    user_agent TEXT,
                    ip_address INET,
                    expires_at TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
                SQL);

                $tx->raw(<<<SQL
                CREATE INDEX IF NOT EXISTS idx_user_tokens_user_id ON user_tokens(user_id);
                SQL);

                $tx->raw(<<<SQL
                CREATE INDEX IF NOT EXISTS idx_user_tokens_token ON user_tokens(token);
                SQL);

                $tx->raw(<<<SQL
                CREATE TABLE IF NOT EXISTS todos (
                    id SERIAL PRIMARY KEY,
                    user_id INTEGER NOT NULL,
                    title TEXT NOT NULL,
                    description TEXT,
                    is_completed BOOLEAN DEFAULT FALSE,
                    due_date TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
                SQL);

                $tx->raw(<<<SQL
                CREATE INDEX IF NOT EXISTS idx_todos_user_id ON todos(user_id);
                SQL);

                $tx->raw(<<<SQL
                CREATE INDEX IF NOT EXISTS idx_todos_is_completed ON todos(is_completed);
                SQL);
            });

            CliOutput::info('Migrated successfully!');
        } catch (QueryException $exception) {
            CliOutput::error('Failed to migrate: ' . $exception->getMessage());
            CliOutput::table([], $exception->getTrace());
        }

        try {
            $this->connection->transaction(function (ConnectionInterface $tx) {
                $tx->raw(<<<SQL
                CREATE TABLE IF NOT EXISTS task_logs (
                    id SERIAL PRIMARY KEY,
                    todo_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                    FOREIGN KEY (todo_id) REFERENCES todos(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
                SQL);
            });
        } catch (QueryException $exception) {
            CliOutput::error('Failed to migrate: ' . $exception->getMessage());
            CliOutput::table([], $exception->getTrace());
        }

        try {
            $this->connection->transaction(function (ConnectionInterface $tx) {
                $tx->raw(<<<SQL
                ALTER TABLE todos
                ADD COLUMN deleted_at TIMESTAMP NULL;
                SQL);
            });
        } catch (QueryException $exception) {
            CliOutput::error('Failed to migrate: ' . $exception->getMessage());
            CliOutput::table([], $exception->getTrace());
        }

        return CLI::SUCCESS;
    }
}
