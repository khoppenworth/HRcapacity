<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstallRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SystemCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallationController extends Controller
{
    public function __construct(private readonly SystemCheckService $systemCheck)
    {
    }

    public function status(): JsonResponse
    {
        $tenants = 0;
        $admins = 0;

        try {
            $tenants = Tenant::count();
            $admins = User::where('role', 'tenant_admin')->count();
        } catch (\Throwable $exception) {
            // Leave counts at zero when the database is unreachable so the readiness
            // checklist can still surface the failure via the system checks.
        }

        return new JsonResponse([
            'isConfigured' => $tenants > 0 && $admins > 0,
            'tenantCount' => $tenants,
            'adminCount' => $admins,
            'checks' => $this->systemCheck->run(),
        ]);
    }

    public function install(InstallRequest $request): JsonResponse
    {
        if (Tenant::exists() || User::exists()) {
            return new JsonResponse([
                'message' => 'Installation has already been completed.',
            ], 409);
        }

        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'slug' => $data['company_slug'],
                'primary_contact_email' => $data['contact_email'] ?? null,
                'is_active' => true,
            ]);

            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role' => 'tenant_admin',
                'is_active' => true,
            ]);

            return [$tenant, $admin];
        });

        [$tenant, $admin] = $result;

        return new JsonResponse([
            'message' => 'Installation completed successfully.',
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
        ], 201);
    }
}

