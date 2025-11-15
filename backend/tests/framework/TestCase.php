<?php

namespace PHPUnit\Framework;

abstract class TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }

    public function run(): array
    {
        $results = [];
        foreach (get_class_methods($this) as $method) {
            if (! str_starts_with($method, 'test')) {
                continue;
            }

            $this->setUp();
            try {
                $this->$method();
                $results[] = ['method' => $method, 'status' => 'passed'];
            } catch (\Throwable $throwable) {
                $results[] = [
                    'method' => $method,
                    'status' => 'failed',
                    'message' => $throwable->getMessage(),
                ];
            }
            $this->tearDown();
        }

        return $results;
    }

    public static function assertTrue(bool $condition, string $message = ''): void
    {
        if (! $condition) {
            throw new \RuntimeException($message ?: 'Failed asserting that condition is true.');
        }
    }

    public static function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected != $actual) {
            throw new \RuntimeException($message ?: sprintf('Failed asserting that %s matches expected %s.', var_export($actual, true), var_export($expected, true)));
        }
    }
}
