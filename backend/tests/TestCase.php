<?php

namespace Tests;

use App\Application;
use App\Support\Http\JsonResponse;
use App\Support\Http\Request;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Support\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    private array $pendingHeaders = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    protected function withHeaders(array $headers): static
    {
        $this->pendingHeaders = array_merge($this->pendingHeaders, $headers);

        return $this;
    }

    protected function getJson(string $uri): TestResponse
    {
        return $this->request('GET', $uri);
    }

    protected function postJson(string $uri, array $payload = []): TestResponse
    {
        return $this->request('POST', $uri, $payload);
    }

    protected function putJson(string $uri, array $payload = []): TestResponse
    {
        return $this->request('PUT', $uri, $payload);
    }

    private function request(string $method, string $uri, array $payload = []): TestResponse
    {
        $app = new Application();
        $headers = $this->pendingHeaders;
        $this->pendingHeaders = [];
        $response = $app->handle(new Request($method, $uri, $payload, $headers));

        return new TestResponse($response);
    }

    protected function assertDatabaseHas(string $table, array $criteria): void
    {
        $records = \App\Support\Database::where($table, function ($row) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if (($row[$key] ?? null) !== $value) {
                    return false;
                }
            }
            return true;
        });

        self::assertTrue(! empty($records), 'Failed asserting that database has matching record.');
    }
}
