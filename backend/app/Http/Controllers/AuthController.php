<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Hash;
use App\Support\Http\JsonResponse;
use App\Support\Http\Request;
use App\Support\Exceptions\HttpException;
use App\Support\Container;

class AuthController
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->all();
        foreach (['email', 'password'] as $field) {
            if (empty($data[$field])) {
                abort(422, sprintf('%s is required', $field));
            }
        }

        $tenant = Container::getInstance()->get(Tenant::class);
        $user = User::where('tenant_id', $tenant->id)->firstWhere('email', $data['email']);

        if (! $user || ! $user->is_active || ! $user->verifyPassword($data['password'])) {
            throw new HttpException(422, 'The provided credentials are incorrect.');
        }

        $token = $user->createToken('api')->plainTextToken;

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }
}
