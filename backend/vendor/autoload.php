<?php

declare(strict_types=1);

$baseDir = __DIR__ . '/../';

require_once $baseDir . 'app/helpers.php';

spl_autoload_register(function (string $class) use ($baseDir): void {
    $prefixes = [
        'App\\' => $baseDir . 'app/',
        'Tests\\' => $baseDir . 'tests/',
        'PHPUnit\\Framework\\' => $baseDir . 'tests/framework/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $path = $dir . str_replace('\\', '/', $relative) . '.php';

            if (is_file($path)) {
                require_once $path;
            }

            return;
        }
    }
});
