<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckinAssetRequest;
use App\Http\Requests\CheckoutAssetRequest;
use App\Models\Asset;
use App\Models\User;
use App\Models\AssetAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\AssetAssignmentResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssetAssignmentController extends Controller
{
    /**
     * Zaduži opremu korisniku (Checkout).
     */
    public function checkout(CheckoutAssetRequest $request, Asset $asset): JsonResponse
    {
        Gate::authorize('update', $asset);

        // Provera da li je aset već zadužen
        if ($asset->currentAssignment()->exists()) {
            return response()->json([
                'message' => 'Ovaj aset je već zadužen i mora se prvo razdužiti.'
            ], 422);
        }

        $assignment = DB::transaction(function () use ($request, $asset) {
            // 1. Kreiramo istorijski zapis o zaduženju
            $assignment = $asset->assignments()->create([
                'tenant_id'             => $request->user()->tenant_id,
                'assigned_to_user_id'   => $request->validated('assigned_to_user_id'),
                'assigned_by_user_id'   => $request->user()->id,
                'assigned_at'           => now(),
                'notes'                 => $request->validated('notes'),
                'condition_on_checkout' => $request->validated('condition_on_checkout', 'good'),
            ]);

            // 2. Menjamo status samog Aseta u 'assigned'
            $asset->update(['status' => 'assigned']);

            return $assignment;
        });

        return response()->json([
            'message'    => 'Asset uspesno zaduzen.',
            'assignment' => $assignment->load(['assignedTo', 'assignedBy']),
        ], 201);
    }

    /**
     * Razduži opremu (Check-in).
     */
    public function checkin(CheckinAssetRequest $request, Asset $asset): JsonResponse
    {
        Gate::authorize('update', $asset);

        $currentAssignment = $asset->currentAssignment;

        if (! $currentAssignment) {
            return response()->json([
                'message' => 'Ovaj aset trenutno nije zadužen.'
            ], 422);
        }

        DB::transaction(function () use ($request, $asset, $currentAssignment) {
            // 1. Zatvaramo trenutno zaduženje
            $currentAssignment->update([
                'returned_at'          => now(),
                'condition_on_checkin' => $request->validated('condition_on_checkin'),
                'notes'                => $request->filled('notes') 
                                            ? $currentAssignment->notes . "\n[Check-in Note]: " . $request->validated('notes')
                                            : $currentAssignment->notes,
            ]);

            // 2. Vraćamo status Aseta na 'active'
            $asset->update(['status' => 'active']);
        });

        return response()->json([
            'message' => 'Asset uspesno razduzen.'
        ], 200);
    }

    /**
     * Istorija svih zaduženja za konkretan aset.
     */
    public function historyForAsset(Asset $asset): AnonymousResourceCollection
    {
        Gate::authorize('view', $asset);

        $assignments = $asset->assignments()
            ->with(['assignedTo', 'assignedBy'])
            ->latest('assigned_at')
            ->paginate(15);

        return AssetAssignmentResource::collection($assignments);
    }

    /**
     * Sva zaduženja (aktivna i prošla) za određenog korisnika unutar tenanta.
     */
    public function assignmentsForUser(User $user): AnonymousResourceCollection
    {
        Gate::authorize('view', $user);

        $assignments = AssetAssignment::where('assigned_to_user_id', $user->id)
            ->with(['asset', 'assignedBy'])
            ->latest('assigned_at')
            ->paginate(15);

        return AssetAssignmentResource::collection($assignments);
    }

}
