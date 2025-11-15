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
