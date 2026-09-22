<?php

use App\Models\Asset;
use App\Models\HardwareDetail;
use App\Models\LicenseDetail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can fetch dashboard metrics scoped to their tenant', function () {
    $tenantA = Tenant::factory()->create();
    $userA   = User::factory()->create(['tenant_id' => $tenantA->id]);

    $hw1 = HardwareDetail::create(['model_number' => 'M1']);
    $hw2 = HardwareDetail::create(['model_number' => 'M2']);

    Asset::factory()->create([
        'tenant_id'     => $tenantA->id,
        'status'        => 'active',
        'itemable_type' => HardwareDetail::class,
        'itemable_id'   => $hw1->id,
    ]);

    Asset::factory()->create([
        'tenant_id'     => $tenantA->id,
        'status'        => 'assigned',
        'itemable_type' => HardwareDetail::class,
        'itemable_id'   => $hw2->id,
    ]);

    $licenseDetail = LicenseDetail::create([
        'license_key' => 'TEST-KEY-123',
        'seats'       => 5,
        'expires_at'  => now()->addDays(10)->format('Y-m-d'),
    ]);

    Asset::factory()->create([
        'tenant_id'     => $tenantA->id,
        'status'        => 'active',
        'itemable_type' => LicenseDetail::class,
        'itemable_id'   => $licenseDetail->id,
    ]);

    $tenantB = Tenant::factory()->create();
    Asset::factory()->count(5)->create(['tenant_id' => $tenantB->id]);

    Sanctum::actingAs($userA);

    $response = $this->getJson('/api/dashboard/stats');

    $response->assertStatus(200)
             ->assertJson([
                 'assets' => [
                     'total'       => 3,
                     'assigned'    => 1,
                     'available'   => 2,
                     'maintenance' => 0,
                     'retired'     => 0,
                 ],
                 'breakdown' => [
                     'hardware' => 2,
                     'licenses' => 1,
                 ],
                 'licenses' => [
                     'expiring_soon' => 1,
                 ],
             ]);
});