<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArtisanCommandTest extends TestCase
{
    public function testMigrateCommandSucceedsWithoutComposerDependencies(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $command = sprintf(
            'cd %s && %s artisan migrate --force 2>&1',
            escapeshellarg($projectRoot),
            escapeshellarg(PHP_BINARY)
        );

        $output = [];
        $status = 0;
        exec($command, $output, $status);

        self::assertEquals(0, $status, 'Migrate command should exit successfully.');
        $message = trim(implode("\n", $output));
        self::assertTrue(
            str_contains($message, 'Offline database is already migrated'),
            'Expected migrate output to mention offline migrations.'
        );
    }
}
