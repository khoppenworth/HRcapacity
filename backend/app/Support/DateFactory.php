<?php

namespace App\Support;

use DateTimeImmutable;

class DateFactory
{
    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
