<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TenantController extends Controller
{
    /**
     * Vraća podatke o trenutnom tenantu ulogovanog korisnika.
     */
    public function show(Request $request)
    {
        $tenant = Tenant::findOrFail($request->user()->tenant_id);

        Gate::authorize('view', $tenant);

        return new TenantResource($tenant);
    }

    /**
     * Ažurira podatke trenutnog tenanta ulogovanog korisnika.
     */
    public function update(UpdateTenantRequest $request): JsonResponse
    {
        $tenant = Tenant::findOrFail($request->user()->tenant_id);

        Gate::authorize('update', $tenant);

        $tenant->update($request->validated());

        return response()->json([
            'message' => 'Tenant settings updated successfully',
            'tenant'  => new TenantResource($tenant),
        ]);
    }
}