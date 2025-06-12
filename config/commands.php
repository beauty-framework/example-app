<?php
declare(strict_types=1);

return array_merge(
    \Beauty\Core\Console\RegisterCommands::commands(),
    class_exists(\Beauty\Jobs\Console\RegisterCommands::class) ? \Beauty\Jobs\Console\RegisterCommands::commands() : [],
    [
        \App\Console\Commands\MigrationsCommand::class,
    ]
);