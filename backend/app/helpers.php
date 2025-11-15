<?php

use App\Support\Container;
use App\Support\DateFactory;
use App\Support\Exceptions\HttpException;

if (! function_exists('app')) {
    function app(?string $key = null)
    {
        $container = Container::getInstance();
        if ($key === null) {
            return $container;
        }

        return $container->get($key);
    }
}

if (! function_exists('now')) {
    function now(): DateTimeImmutable
    {
        return DateFactory::now();
    }
}

if (! function_exists('abort')) {
    function abort(int $status, string $message = 'Error'): void
    {
        throw new HttpException($status, $message);
    }
}

if (! function_exists('abort_if')) {
    function abort_if(bool $condition, int $status, string $message = 'Error'): void
    {
        if ($condition) {
            abort($status, $message);
        }
    }
}
