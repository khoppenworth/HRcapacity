<?php

namespace App\Models;

use App\Auth\TokenRepository;
use App\Support\Hash;

class User extends Model
{
    protected static function table(): string
    {
        return 'users';
    }

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function createToken(string $name): object
    {
        $plainTextToken = TokenRepository::issue($this->id);

        return (object) ['plainTextToken' => $plainTextToken];
    }
}
