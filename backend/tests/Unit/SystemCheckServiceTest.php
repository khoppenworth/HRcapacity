<?php

namespace Tests\Unit;

use App\Services\SystemCheckService;
use PHPUnit\Framework\TestCase;

class SystemCheckServiceTest extends TestCase
{
    public function test_git_check_reports_dubious_ownership(): void
    {
        $service = $this->makeServiceWithGitResponse("fatal: detected dubious ownership in repository at '/srv/app'");
        $checks = $service->run();
        $gitCheck = $this->getGitCheck($checks);

        self::assertTrue($gitCheck['passed'] === false, 'Expected git check to fail.');
        self::assertTrue(str_contains($gitCheck['details'], 'safe.directory'), 'Expected safe.directory guidance.');
    }

    public function test_git_check_passes_for_safe_repository(): void
    {
        $service = $this->makeServiceWithGitResponse('true');
        $checks = $service->run();
        $gitCheck = $this->getGitCheck($checks);

        self::assertTrue($gitCheck['passed'] === true, 'Expected git check to pass.');
    }

    private function makeServiceWithGitResponse(string $gitResponse): SystemCheckService
    {
        return new SystemCheckService(function (string $command) use ($gitResponse): string {
            if (str_contains($command, 'node --version')) {
                return '20.0.0';
            }

            if (str_contains($command, 'npm --version')) {
                return '9.0.0';
            }

            if (str_contains($command, 'composer --version')) {
                return 'Composer version 2.5.0 2023-06-09';
            }

            if (str_contains($command, 'git rev-parse')) {
                return $gitResponse;
            }

            return 'true';
        });
    }

    private function getGitCheck(array $checks): array
    {
        foreach ($checks as $check) {
            if (($check['name'] ?? null) === 'git_repository') {
                return $check;
            }
        }

        self::assertTrue(false, 'Git repository check not found.');
    }
}
