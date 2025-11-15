<?php

namespace App\Support;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Traversable;

class Collection implements IteratorAggregate
{
    public function __construct(private array $items = [])
    {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->items, $callback)));
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function sum(callable $callback): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $callback($item);
        }
        return $total;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    public function firstWhere(string $key, mixed $value): mixed
    {
        foreach ($this->items as $item) {
            if (is_array($item) && ($item[$key] ?? null) === $value) {
                return $item;
            }
            if (is_object($item) && ($item->$key ?? null) === $value) {
                return $item;
            }
        }

        return null;
    }

    public function keyBy(string $key): self
    {
        $results = [];
        foreach ($this->items as $item) {
            if (is_array($item) && isset($item[$key])) {
                $results[$item[$key]] = $item;
            }
            if (is_object($item) && isset($item->$key)) {
                $results[$item->$key] = $item;
            }
        }

        return new self($results);
    }

    public function get(mixed $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function has(mixed $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
