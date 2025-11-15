<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SystemCheckService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(): array
    {
        return [
            $this->checkPhpVersion(),
            $this->checkExtension('openssl'),
            $this->checkExtension('pdo_mysql'),
            $this->checkExtension('mbstring'),
            $this->checkStorageDirectoryIsWritable('app'),
            $this->checkStorageDirectoryIsWritable('logs'),
            $this->checkDatabaseConnection(),
            $this->checkAppKey(),
        ];
    }

    private function checkPhpVersion(): array
    {
        $minimum = '8.2.0';
        $current = PHP_VERSION;

        return [
            'name' => 'PHP Version',
            'passed' => version_compare($current, $minimum, '>='),
            'details' => sprintf('Current: %s (minimum %s)', $current, $minimum),
        ];
    }

    private function checkExtension(string $extension): array
    {
        return [
            'name' => sprintf('Extension: %s', $extension),
            'passed' => extension_loaded($extension),
            'details' => extension_loaded($extension)
                ? 'Loaded'
                : 'Missing - enable the extension in php.ini',
        ];
    }

    private function checkStorageDirectoryIsWritable(string $directory): array
    {
        $path = storage_path($directory);
        $writable = is_writable($path);

        return [
            'name' => sprintf('Writable: storage/%s', $directory),
            'passed' => $writable,
            'details' => $writable
                ? 'Directory is writable'
                : sprintf('Set write permissions for %s', $path),
        ];
    }

    private function checkDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'name' => 'Database Connection',
                'passed' => true,
                'details' => 'Connection established successfully.',
            ];
        } catch (\Throwable $exception) {
            return [
                'name' => 'Database Connection',
                'passed' => false,
                'details' => $exception->getMessage(),
            ];
        }
    }

    private function checkAppKey(): array
    {
        $hasKey = config('app.key') !== null;

        return [
            'name' => 'APP_KEY configured',
            'passed' => $hasKey,
            'details' => $hasKey
                ? 'Application key is set.'
                : 'Run `php artisan key:generate` to configure the APP_KEY.',
        ];
    }
}

