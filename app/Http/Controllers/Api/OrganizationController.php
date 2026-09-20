<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrganizationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Organization::class);

        return OrganizationResource::collection(Organization::latest()->paginate(15));
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        Gate::authorize('create', Organization::class);

        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $organization = Organization::create($validated);

        return response()->json([
            'message' => 'Organization created successfully.',
            'data' => new OrganizationResource($organization),
        ], 201);
    }

    public function show(Organization $organization): OrganizationResource
    {
        Gate::authorize('view', $organization);

        return response()->json(['organization' => new OrganizationResource($organization)]);
    }

    public function update(StoreOrganizationRequest $request, Organization $organization): JsonResponse
    {
        Gate::authorize('update', $organization);

        $validated = $request->validated();
        $organization->update($validated);

        return response()->json([
            'message' => 'Organization updated successfully.',
            'data' => new OrganizationResource($organization),
        ]);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        Gate::authorize('delete', $organization);

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully.']);
    }
}
