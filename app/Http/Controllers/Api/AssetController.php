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
use App\Http\Requests\UpdateAssetRequest;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        $query = Asset::query()
            ->with(['category', 'itemable']);

        // 1. Pretraga (name, asset_tag, serial_number)
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(asset_tag) LIKE ?', ["%{$search}%"])
                ->orWhereHasMorph(
                    'itemable',
                    [\App\Models\HardwareDetail::class],
                    function ($subQuery) use ($search) {
                        $subQuery->whereRaw('LOWER(serial_number) LIKE ?', ["%{$search}%"]);
                    }
                );
            });
        }

        // 2. Filter po statusu
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Filter po kategoriji
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 4. Filter po tipu (Hardware vs License)
        if ($request->filled('type')) {
            $type = $request->input('type');
            if ($type === 'hardware') {
                $query->where('itemable_type', 'like', '%HardwareDetail%');
            } elseif ($type === 'license') {
                $query->where('itemable_type', 'like', '%LicenseDetail%');
            }
        }

        // 5. Sortiranje
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSortFields = ['name', 'asset_tag', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 6. Paginacija
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        return AssetResource::collection($query->paginate($perPage));
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

       $asset->load(['category', 'itemable']);

        return new AssetResource($asset);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset): JsonResponse
    {
        Gate::authorize('update', $asset);

        $validated = $request->validated();

        // 1. Ažuriranje samog Asset-a
        $assetFields = array_intersect_key($validated, array_flip([
            'name', 'asset_tag', 'status', 'category_id', 'organization_id'
        ]));

        if (!empty($assetFields)) {
            $asset->update($assetFields);
        }

        // 2. Ažuriranje ugnježdenih detalja na HardwareDetail/LicenseDetail
        if (isset($validated['details']) && $asset->itemable) {
            $asset->itemable->update($validated['details']);
        }

        // 3. Osvežavanje iz baze radi vršenja uvid u nove podatke
        $asset->refresh();
        $asset->load(['category', 'itemable']);

        return response()->json([
            'message' => 'Asset updated successfully',
            'data'    => new AssetResource($asset),
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
