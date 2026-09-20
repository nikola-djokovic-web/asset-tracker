<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Models\HardwareDetail;
use App\Models\LicenseDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\StoreAssetRequest;
use Illuminate\Support\Facades\Gate;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
       $assets = Asset::with(['category', 'organization'])->paginate(10);

        return AssetResource::collection($assets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $asset = DB::transaction(function () use ($validated, $request) {
            $itemable = null;

            if ($validated['type'] === 'hardware') {
                $itemable = HardwareDetail::create([
                    'serial_number' => $request->input('details.serial_number'),
                    'specs' => $request->input('details.specs')
                ]);
            } elseif ($validated['type'] === 'license') {
                $itemable = LicenseDetail::create([
                    'license_key' => $request->input('details.license_key'),
                    'seats' => $request->input('details.seats'),
                    'expires_at' => $request->input('details.expires_at')
                ]);
            }

            return $itemable->asset()->create([
                'tenant_id' => $request->user()->tenant_id,
                'name' => $validated['name'],
                'asset_tag' => $validated['asset_tag'],
                'status' => $validated['status'],
                'category_id' => $validated['category_id'],
                'organization_id' => $validated['organization_id'],
            ]);
        });

        return response()->json([
            'message' => 'Asset created successfully',
            'asset'   => new AssetResource($asset->load(['category', 'organization', 'itemable'])),
        ], 201);

      
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset): AssetResource
    {
       Gate::authorize('view', $asset);

       return response()->json([
            'asset' => new AssetResource($asset->load(['category', 'organization', 'itemable'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        Gate::authorize('update', $asset);

        $asset->update($request->validated());

        return response()->json([
            'message' => 'Asset updated successfully',
            'asset'   => new AssetResource($asset->fresh()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('delete', $asset);
        
        $asset->delete();

        return response()->json(['message' => 'Asset deleted successfully'], 200);
    }
}
