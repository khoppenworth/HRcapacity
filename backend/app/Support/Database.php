<?php

namespace App\Support;

class Database
{
    private static array $tables = [];
    private static array $increments = [];

    public static function insert(string $table, array $attributes): array
    {
        $id = self::$increments[$table] = (self::$increments[$table] ?? 0) + 1;
        $record = $attributes;
        $record['id'] = $id;
        $record['created_at'] = $record['created_at'] ?? now();
        $record['updated_at'] = $record['updated_at'] ?? now();
        self::$tables[$table][$id] = $record;

        return $record;
    }

    public static function update(string $table, int $id, array $attributes): array
    {
        if (! isset(self::$tables[$table][$id])) {
            throw new \RuntimeException("Record {$id} missing in {$table}");
        }

        self::$tables[$table][$id] = array_merge(self::$tables[$table][$id], $attributes, [
            'updated_at' => now(),
        ]);

        return self::$tables[$table][$id];
    }

    public static function all(string $table): array
    {
        return array_values(self::$tables[$table] ?? []);
    }

    public static function find(string $table, int $id): ?array
    {
        return self::$tables[$table][$id] ?? null;
    }

    public static function deleteWhere(string $table, callable $filter): void
    {
        foreach (self::$tables[$table] ?? [] as $id => $record) {
            if ($filter($record)) {
                unset(self::$tables[$table][$id]);
            }
        }
    }

    public static function where(string $table, callable $filter): array
    {
        return array_values(array_filter(self::$tables[$table] ?? [], $filter));
    }

    public static function reset(): void
    {
        self::$tables = [];
        self::$increments = [];
    }

    public static function transaction(callable $callback): mixed
    {
        return $callback();
    }
}
