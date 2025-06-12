<?php
declare(strict_types=1);

/**
 * @var array<class-string, class-string[]>
 */
return [
    \App\Events\SaveTaskInLogEvent::class => [
        \App\Listeners\TaskLogInDBListener::class,
    ],
];