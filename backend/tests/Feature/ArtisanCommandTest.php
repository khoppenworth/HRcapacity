<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArtisanCommandTest extends TestCase
{
    public function testMigrateCommandSucceedsWithoutComposerDependencies(): void
    {
        $result = $this->runArtisanCommand('migrate --force');

        self::assertEquals(0, $result['status'], 'Migrate command should exit successfully.');
        self::assertTrue(
            str_contains($result['output'], 'Offline database is already migrated'),
            'Expected migrate output to mention offline migrations.'
        );
    }

    public function testDbSeedCommandSucceedsWithoutComposerDependencies(): void
    {
        $result = $this->runArtisanCommand('db:seed --force');

        self::assertEquals(0, $result['status'], 'db:seed command should exit successfully.');
        self::assertTrue(
            str_contains($result['output'], 'Offline database seeding is not required'),
            'Expected db:seed output to mention offline seeding.'
        );
    }

    public function testConfigCacheCommandSucceedsWithoutComposerDependencies(): void
    {
        $result = $this->runArtisanCommand('config:cache');

        self::assertEquals(0, $result['status'], 'config:cache command should exit successfully.');
        self::assertTrue(
            str_contains($result['output'], 'Configuration cache is already optimized'),
            'Expected config:cache output to mention offline cache.'
        );
    }

    public function testRouteCacheCommandSucceedsWithoutComposerDependencies(): void
    {
        $result = $this->runArtisanCommand('route:cache');

        self::assertEquals(0, $result['status'], 'route:cache command should exit successfully.');
        self::assertTrue(
            str_contains($result['output'], 'Route definitions cache is already optimized'),
            'Expected route:cache output to mention offline cache.'
        );
    }

    public function testStorageDirectoriesAllowChownCommand(): void
    {
        $result = $this->runShellCommand(
            'sudo chown -R www-data:www-data storage bootstrap/cache'
        );

        self::assertEquals(0, $result['status'], 'chown command should exit successfully.');
    }

    public function testStorageDirectoriesAllowChmodCommand(): void
    {
        $result = $this->runShellCommand(
            'sudo chmod -R ug+rwx storage bootstrap/cache'
        );

        self::assertEquals(0, $result['status'], 'chmod command should exit successfully.');
    }

    /**
     * @return array{status:int, output:string}
     */
    private function runArtisanCommand(string $arguments): array
    {
        $command = sprintf(
            'cd %s && %s artisan %s 2>&1',
            escapeshellarg($this->getProjectRoot()),
            escapeshellarg(PHP_BINARY),
            $arguments
        );

        return $this->runRawCommand($command);
    }

    /**
     * @return array{status:int, output:string}
     */
    private function runShellCommand(string $command): array
    {
        $fullCommand = sprintf('cd %s && %s 2>&1', escapeshellarg($this->getProjectRoot()), $command);

        return $this->runRawCommand($fullCommand);
    }

    /**
     * @return array{status:int, output:string}
     */
    private function runRawCommand(string $command): array
    {
        $output = [];
        $status = 0;
        exec($command, $output, $status);

        return [
            'status' => $status,
            'output' => trim(implode("\n", $output)),
        ];
    }

    private function getProjectRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
