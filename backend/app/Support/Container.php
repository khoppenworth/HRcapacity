<?php

namespace App\Support;

class Container
{
    private static ?self $instance = null;

    private array $bindings = [];

    public static function getInstance(): self
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    public function instance(string $abstract, mixed $object): void
    {
        $this->bindings[$abstract] = static fn () => $object;
    }

    public function get(string $abstract): mixed
    {
        if (! isset($this->bindings[$abstract])) {
            throw new \RuntimeException("Nothing bound for {$abstract}");
        }

        $factory = $this->bindings[$abstract];

        return $factory();
    }

    public function forgetInstance(string $abstract): void
    {
        unset($this->bindings[$abstract]);
    }
}
