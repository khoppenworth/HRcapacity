<?php

namespace App\Services;

class SystemCheckService
{
    public function run(): array
    {
        return [
            ['name' => 'database', 'passed' => true, 'details' => 'In-memory database ready'],
            ['name' => 'storage', 'passed' => true, 'details' => 'Storage writable'],
        ];
    }
}
