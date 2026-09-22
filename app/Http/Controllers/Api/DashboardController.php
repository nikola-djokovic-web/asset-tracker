<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\LicenseDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $baseQuery = Asset::where('tenant_id', $tenantId);

        $totalAssets       = (clone $baseQuery)->count();
        $assignedAssets    = (clone $baseQuery)->where('status', 'assigned')->count();
        $activeAssets      = (clone $baseQuery)->where('status', 'active')->count();
        $maintenanceAssets = (clone $baseQuery)->where('status', 'maintenance')->count();
        $retiredAssets     = (clone $baseQuery)->where('status', 'retired')->count();

        $hardwareCount = (clone $baseQuery)->where('itemable_type', 'like', '%HardwareDetail%')->count();
        $licenseCount  = (clone $baseQuery)->where('itemable_type', 'like', '%LicenseDetail%')->count();

        $tenantLicenseIds = (clone $baseQuery)
            ->where('itemable_type', 'like', '%LicenseDetail%')
            ->pluck('itemable_id');

        $expiringLicensesCount = LicenseDetail::whereIn('id', $tenantLicenseIds)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->count();

        return response()->json([
            'assets' => [
                'total'       => $totalAssets,
                'assigned'    => $assignedAssets,
                'available'   => $activeAssets,
                'maintenance' => $maintenanceAssets,
                'retired'     => $retiredAssets,
            ],
            'breakdown' => [
                'hardware' => $hardwareCount,
                'licenses' => $licenseCount,
            ],
            'licenses' => [
                'expiring_soon' => $expiringLicensesCount,
            ],
        ]);
    }
}