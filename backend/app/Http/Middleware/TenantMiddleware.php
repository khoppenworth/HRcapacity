<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $slug = $request->header('X-Tenant');

        if (empty($slug)) {
            return new JsonResponse(['message' => 'Tenant header missing.'], 400);
        }

        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->first();

        if (! $tenant) {
            return new JsonResponse(['message' => 'Tenant not found or inactive.'], 404);
        }

        app()->instance(Tenant::class, $tenant);
        $request->attributes->set('tenant_id', $tenant->id);

        return $next($request);
    }
}
