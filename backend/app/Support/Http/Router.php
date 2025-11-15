<?php

namespace App\Support\Http;

use App\Auth\TokenRepository;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Exceptions\HttpException;
use App\Support\Container;

class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, callable $action, array $options = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'action' => $action,
            'options' => array_merge([
                'tenant' => false,
                'auth' => false,
            ], $options),
        ];
    }

    public function dispatch(Request $request): JsonResponse
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $params = $this->matchPattern($route['pattern'], $request->path());
            if ($params === null) {
                continue;
            }

            $this->applyGuards($request, $route['options']);
            $action = $route['action'];

            return $action($request, ...$params);
        }

        throw new HttpException(404, 'Route not found');
    }

    private function matchPattern(string $pattern, string $path): ?array
    {
        $patternSegments = explode('/', trim($pattern, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        if (count($patternSegments) !== count($pathSegments)) {
            return null;
        }

        $params = [];
        foreach ($patternSegments as $index => $segment) {
            if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
                $params[] = $pathSegments[$index];
                continue;
            }

            if ($segment !== $pathSegments[$index]) {
                return null;
            }
        }

        return $params;
    }

    private function applyGuards(Request $request, array $options): void
    {
        if ($options['tenant']) {
            $slug = $request->header('X-Tenant');
            if (! $slug) {
                throw new HttpException(400, 'Tenant header missing.');
            }

            $tenant = Tenant::firstWhere('slug', $slug);
            if (! $tenant || ! $tenant->is_active) {
                throw new HttpException(404, 'Tenant not found or inactive.');
            }

            Container::getInstance()->instance(Tenant::class, $tenant);
            $request->setTenant($tenant);
        }

        if ($options['auth']) {
            $header = $request->header('Authorization');
            if (! $header || ! str_starts_with($header, 'Bearer ')) {
                throw new HttpException(401, 'Missing token');
            }

            $token = substr($header, 7);
            $userId = TokenRepository::resolve($token);
            if (! $userId) {
                throw new HttpException(401, 'Invalid token');
            }

            $user = User::find($userId);
            if (! $user) {
                throw new HttpException(401, 'User missing');
            }

            if ($options['tenant'] && $request->tenant() && $user->tenant_id !== $request->tenant()->id) {
                throw new HttpException(403, 'Tenant mismatch');
            }

            $request->setUser($user);
        }
    }
}
