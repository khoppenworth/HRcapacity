<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\SystemCheckService;
use App\Support\Hash;
use App\Support\Http\JsonResponse;
use App\Support\Http\Request;
use App\Support\Http\Router;
use App\Support\Database;

class InstallationController
{
    public function __construct(private readonly SystemCheckService $checks)
    {
    }

    public function status(): JsonResponse
    {
        return new JsonResponse([
            'isConfigured' => Tenant::count() > 0 && User::where('role', 'tenant_admin')->count() > 0,
            'tenantCount' => Tenant::count(),
            'adminCount' => User::where('role', 'tenant_admin')->count(),
            'checks' => $this->checks->run(),
        ]);
    }

    public function install(Request $request): JsonResponse
    {
        if (Tenant::exists() || User::exists()) {
            return new JsonResponse(['message' => 'Installation has already been completed.'], 409);
        }

        $data = $request->all();
        $required = ['company_name', 'company_slug', 'contact_email', 'admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                abort(422, sprintf('%s is required', $field));
            }
        }

        if ($data['admin_password'] !== $data['admin_password_confirmation']) {
            abort(422, 'Password confirmation does not match.');
        }

        [$tenant, $admin] = Database::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'slug' => $data['company_slug'],
                'primary_contact_email' => $data['contact_email'],
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

        return new JsonResponse([
            'message' => 'Installation completed successfully.',
            'tenant' => ['id' => $tenant->id, 'name' => $tenant->name, 'slug' => $tenant->slug],
            'admin' => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email],
        ], 201);
    }
}
