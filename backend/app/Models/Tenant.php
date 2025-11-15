<?php

namespace App\Models;

class Tenant extends Model
{
    protected static function table(): string
    {
        return 'tenants';
    }
}
