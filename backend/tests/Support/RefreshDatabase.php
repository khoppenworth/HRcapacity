<?php

namespace Tests\Support;

use App\Auth\TokenRepository;
use App\Support\Container;
use App\Support\Database;

trait RefreshDatabase
{
    protected function refreshDatabase(): void
    {
        Database::reset();
        TokenRepository::clear();
        Container::getInstance()->forgetInstance(\App\Models\Tenant::class);
    }
}
