<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\WorkFunction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkFunctionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        $workFunctions = WorkFunction::where('tenant_id', $tenant->id)->get();

        return new JsonResponse($workFunctions);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('work_functions')->where('tenant_id', $tenant->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $workFunction = WorkFunction::create([
            'tenant_id' => $tenant->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return new JsonResponse($workFunction, 201);
    }

    public function show(Request $request, WorkFunction $workFunction): JsonResponse
    {
        $this->authorizeTenant($workFunction);

        return new JsonResponse($workFunction);
    }

    public function update(Request $request, WorkFunction $workFunction): JsonResponse
    {
        $this->authorizeTenant($workFunction);

        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:20', Rule::unique('work_functions')->where('tenant_id', $tenant->id)->ignore($workFunction->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $workFunction->fill($data);
        $workFunction->save();

        return new JsonResponse($workFunction);
    }

    public function destroy(Request $request, WorkFunction $workFunction): JsonResponse
    {
        $this->authorizeTenant($workFunction);

        $workFunction->delete();

        return new JsonResponse(['message' => 'Deleted']);
    }

    private function authorizeTenant(WorkFunction $workFunction): void
    {
        /** @var Tenant $tenant */
        $tenant = app(Tenant::class);

        abort_if($workFunction->tenant_id !== $tenant->id, 404);
    }
}
