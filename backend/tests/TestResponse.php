<?php

namespace Tests;

use App\Support\Http\JsonResponse;

class TestResponse
{
    public function __construct(private JsonResponse $response)
    {
    }

    public function assertOk(): self
    {
        \PHPUnit\Framework\TestCase::assertEquals(200, $this->response->getStatusCode());

        return $this;
    }

    public function assertCreated(): self
    {
        \PHPUnit\Framework\TestCase::assertEquals(201, $this->response->getStatusCode());

        return $this;
    }

    public function assertStatus(int $status): self
    {
        \PHPUnit\Framework\TestCase::assertEquals($status, $this->response->getStatusCode());

        return $this;
    }

    public function assertJson(array $subset): self
    {
        foreach ($subset as $key => $value) {
            \PHPUnit\Framework\TestCase::assertEquals($value, $this->json($key));
        }

        return $this;
    }

    public function json(?string $key = null): mixed
    {
        $data = $this->response->jsonSerialize();
        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? null;
    }

    public function assertJsonPath(string $path, mixed $expected): self
    {
        $segments = explode('.', $path);
        $value = $this->json();
        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                $value = null;
                break;
            }
            $value = $value[$segment];
        }

        \PHPUnit\Framework\TestCase::assertEquals($expected, $value);

        return $this;
    }

    public function assertJsonStructure(array $structure): self
    {
        $data = $this->json();
        foreach ($structure as $key => $value) {
            if (is_int($key)) {
                \PHPUnit\Framework\TestCase::assertTrue(array_key_exists($value, $data));
            } else {
                \PHPUnit\Framework\TestCase::assertTrue(array_key_exists($key, $data));
                foreach ($data[$key] as $item) {
                    foreach ($value as $childKey) {
                        \PHPUnit\Framework\TestCase::assertTrue(array_key_exists($childKey, $item));
                    }
                }
            }
        }

        return $this;
    }
}
