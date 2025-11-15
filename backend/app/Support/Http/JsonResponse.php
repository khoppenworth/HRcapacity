<?php

namespace App\Support\Http;

class JsonResponse implements \JsonSerializable
{
    public function __construct(private array $data, private int $status = 200)
    {
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }
}
