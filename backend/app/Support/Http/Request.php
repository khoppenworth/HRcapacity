<?php

namespace App\Support\Http;

use App\Models\Tenant;
use App\Models\User;

class Request
{
    private ?Tenant $tenant = null;
    private ?User $user = null;

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $payload = [],
        private readonly array $headers = []
    ) {
    }

    public function method(): string
    {
        return strtoupper($this->method);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function all(): array
    {
        return $this->payload;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = strtolower($key);
        foreach ($this->headers as $header => $value) {
            if (strtolower($header) === $normalized) {
                return $value;
            }
        }

        return $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function user(): ?User
    {
        return $this->user;
    }
}
