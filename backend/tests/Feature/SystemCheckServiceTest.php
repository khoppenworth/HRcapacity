<?php

namespace Tests\Feature;

use App\Services\SystemCheckService;
use PHPUnit\Framework\TestCase;

class SystemCheckServiceTest extends TestCase
{
    public function test_reports_version_information_for_runtime_tools(): void
    {
        $service = new SystemCheckService(function (string $command): ?string {
            return match (true) {
                str_contains($command, 'node') => 'v20.5.0',
                str_contains($command, 'npm') => "10.2.0\n",
                str_contains($command, 'composer') => 'Composer version 2.6.7 2024-05-01 00:00:00',
                default => null,
            };
        });

        $checks = $this->indexChecks($service->run());

        self::assertTrue($checks['php_version']['passed']);
        self::assertTrue(str_contains($checks['php_version']['details'], PHP_VERSION));

        self::assertTrue($checks['php_extensions']['passed']);

        self::assertTrue($checks['node']['passed']);
        self::assertTrue(str_contains($checks['node']['details'], '20.5.0'));

        self::assertTrue($checks['npm']['passed']);
        self::assertTrue(str_contains($checks['npm']['details'], '10.2.0'));

        self::assertTrue($checks['composer']['passed']);
        self::assertTrue(str_contains($checks['composer']['details'], '2.6.7'));
    }

    public function test_flags_missing_binaries(): void
    {
        $service = new SystemCheckService(fn () => null);

        $checks = $this->indexChecks($service->run());

        self::assertTrue($checks['node']['passed'] === false);
        self::assertTrue(str_contains($checks['node']['details'], 'Unable to detect node'));
        self::assertTrue($checks['npm']['passed'] === false);
        self::assertTrue($checks['composer']['passed'] === false);
    }

    private function indexChecks(array $checks): array
    {
        $indexed = [];
        foreach ($checks as $check) {
            $indexed[$check['name']] = $check;
        }

        return $indexed;
    }
}
