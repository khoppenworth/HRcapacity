<?php

namespace App\Models;

use App\Support\Database;
use App\Support\Collection;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected array $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    abstract protected static function table(): string;

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public static function create(array $attributes): static
    {
        $record = Database::insert(static::table(), $attributes);

        return new static($record);
    }

    public static function count(): int
    {
        return count(Database::all(static::table()));
    }

    public static function exists(): bool
    {
        return static::count() > 0;
    }

    public static function all(): Collection
    {
        return new Collection(array_map(fn ($row) => new static($row), Database::all(static::table())));
    }

    public static function where(string $field, mixed $value): Collection
    {
        $rows = Database::where(static::table(), fn ($row) => ($row[$field] ?? null) === $value);

        return new Collection(array_map(fn ($row) => new static($row), $rows));
    }

    public static function firstWhere(string $field, mixed $value): ?static
    {
        $rows = Database::where(static::table(), fn ($row) => ($row[$field] ?? null) === $value);
        $first = $rows[0] ?? null;

        return $first ? new static($first) : null;
    }

    public static function find(int $id): ?static
    {
        $record = Database::find(static::table(), $id);

        return $record ? new static($record) : null;
    }

    public static function updateOrCreate(array $criteria, array $values): static
    {
        $existing = Database::where(static::table(), function ($row) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if (($row[$key] ?? null) !== $value) {
                    return false;
                }
            }
            return true;
        });

        if ($existing) {
            $record = $existing[0];
            $record = Database::update(static::table(), $record['id'], array_merge($record, $values));

            return new static($record);
        }

        return static::create(array_merge($criteria, $values));
    }

    public function save(): void
    {
        $this->attributes = Database::update(static::table(), $this->id, $this->attributes);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
