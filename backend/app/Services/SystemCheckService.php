<?php

namespace App\Services;

class SystemCheckService
{
    private const REQUIRED_PHP_VERSION = '8.2.0';

    private const REQUIRED_EXTENSIONS = [
        'bcmath',
        'ctype',
        'fileinfo',
        'json',
        'mbstring',
        'pdo_mysql',
        'openssl',
        'xml',
        'zip',
    ];

    private const REQUIRED_BINARIES = [
        'node' => '20.0.0',
        'npm' => '9.0.0',
        'composer' => '2.5.0',
    ];

    /**
     * @param (callable(string):(?string))|null $commandRunner
     */
    public function __construct(private $commandRunner = null)
    {
    }

    public function run(): array
    {
        $checks = [
            $this->checkPhpVersion(),
            $this->checkExtensions(),
        ];

        foreach (self::REQUIRED_BINARIES as $binary => $requiredVersion) {
            $checks[] = $this->checkBinary($binary, $requiredVersion);
        }

        $checks[] = $this->checkGitSafeDirectory();

        return $checks;
    }

    private function checkPhpVersion(): array
    {
        $current = PHP_VERSION;
        $passed = version_compare($current, self::REQUIRED_PHP_VERSION, '>=');

        return [
            'name' => 'php_version',
            'passed' => $passed,
            'details' => sprintf(
                'PHP %s detected (requires >= %s).',
                $current,
                self::REQUIRED_PHP_VERSION
            ),
        ];
    }

    private function checkExtensions(): array
    {
        $missing = array_values(array_filter(self::REQUIRED_EXTENSIONS, function (string $extension): bool {
            return ! extension_loaded($extension);
        }));

        return [
            'name' => 'php_extensions',
            'passed' => empty($missing),
            'details' => empty($missing)
                ? sprintf('Required extensions loaded: %s.', implode(', ', self::REQUIRED_EXTENSIONS))
                : sprintf('Missing extensions: %s.', implode(', ', $missing)),
        ];
    }

    private function checkBinary(string $binary, string $required): array
    {
        $output = $this->runCommand(sprintf('%s --version', $binary));
        $version = $this->extractVersion($output);
        $passed = $version !== null && version_compare($version, $required, '>=');

        return [
            'name' => $binary,
            'passed' => $passed,
            'details' => $version
                ? sprintf('%s %s detected (requires >= %s).', ucfirst($binary), $version, $required)
                : sprintf('Unable to detect %s. Ensure it is installed and available in $PATH.', $binary),
        ];
    }

    private function checkGitSafeDirectory(): array
    {
        $gitRoot = $this->findGitRoot();

        if (! $gitRoot) {
            return [
                'name' => 'git_repository',
                'passed' => true,
                'details' => 'Git repository not detected. Skipping ownership checks.',
            ];
        }

        $output = $this->runCommand(sprintf('cd %s && git rev-parse --is-inside-work-tree', escapeshellarg($gitRoot)));

        if ($output === null) {
            return [
                'name' => 'git_repository',
                'passed' => false,
                'details' => sprintf('Unable to execute git commands inside %s. Ensure Git is installed and accessible.', $gitRoot),
            ];
        }

        $normalized = strtolower($output);

        if (str_contains($normalized, 'dubious ownership') || str_contains($normalized, 'safe.directory')) {
            return [
                'name' => 'git_repository',
                'passed' => false,
                'details' => sprintf(
                    'Git reported dubious ownership for %s. Run "sudo chown -R <user>:<user> %1$s" or "git config --global --add safe.directory %1$s" before continuing.',
                    $gitRoot
                ),
            ];
        }

        if (trim($output) !== 'true') {
            return [
                'name' => 'git_repository',
                'passed' => false,
                'details' => sprintf('Unable to verify the Git repository at %s. Output: %s', $gitRoot, $output),
            ];
        }

        return [
            'name' => 'git_repository',
            'passed' => true,
            'details' => sprintf('Git repository detected at %s.', $gitRoot),
        ];
    }

    private function findGitRoot(): ?string
    {
        $directory = dirname(__DIR__, 2);

        while ($directory && $directory !== DIRECTORY_SEPARATOR) {
            if (is_dir($directory . '/.git')) {
                return $directory;
            }

            $parent = dirname($directory);

            if ($parent === $directory) {
                break;
            }

            $directory = $parent;
        }

        return null;
    }

    private function runCommand(string $command): ?string
    {
        $runner = $this->commandRunner;
        $output = $runner
            ? $runner($command)
            : shell_exec($command . ' 2>&1');

        if ($output === null) {
            return null;
        }

        $output = trim((string) $output);

        return $output === '' ? null : $output;
    }

    private function extractVersion(?string $output): ?string
    {
        if (! $output) {
            return null;
        }

        if (preg_match('/\d+(?:\.\d+)+/', $output, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
