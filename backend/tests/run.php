<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$directory = new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory);

foreach ($iterator as $file) {
    if (str_ends_with($file->getFilename(), 'Test.php')) {
        require_once $file->getPathname();
    }
}

$testClasses = array_filter(get_declared_classes(), function (string $class): bool {
    if (! str_starts_with($class, 'Tests\\')) {
        return false;
    }

    if (! is_subclass_of($class, \PHPUnit\Framework\TestCase::class)) {
        return false;
    }

    $reflection = new ReflectionClass($class);

    return ! $reflection->isAbstract();
});

$total = 0;
$failures = 0;

foreach ($testClasses as $class) {
    $test = new $class();
    $results = $test->run();
    foreach ($results as $result) {
        $total++;
        $status = $result['status'];
        $message = $result['message'] ?? '';
        printf("[%s] %s::%s %s\n", strtoupper($status), $class, $result['method'], $message);
        if ($status === 'failed') {
            $failures++;
        }
    }
}

printf("\nExecuted %d test%s\n", $total, $total === 1 ? '' : 's');

if ($failures > 0) {
    exit(1);
}
