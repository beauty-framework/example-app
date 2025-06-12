<?php
declare(strict_types=1);

/**
 * @param string $path
 * @return string
 */
function base_path(string $path = ''): string
{
    return dirname(__DIR__) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/') : '');
}

/**
 * @param string $path
 * @return string
 */
function storage_path(string $path = ''): string
{
    return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/') : '');
}