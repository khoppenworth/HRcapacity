<?php

namespace App\Support\Exceptions;

use Exception;

class HttpException extends Exception
{
    public function __construct(private readonly int $status, string $message)
    {
        parent::__construct($message, $status);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }
}
