<?php

use App\Models\Category;
use App\Models\HardwareDetail;
use App\Models\LicenseDetail;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can create a hardware asset with polymorphic details', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $organization = Organization::factory()->create(['tenant_id' => $tenant->id]);

    Sanctum::actingAs($user);

    $payload = [
        'name'            => 'MacBook Pro M2',
        'asset_tag'       => 'HW-998877',
        'type'            => 'hardware',
        'status'          => 'active',
        'category_id'     => $category->id,
        'organization_id' => $organization->id,
        'details'         => [
            'serial_number' => 'SN-MAC-2026-X',
            'specs'         => ['ram' => '32GB', 'storage' => '1TB'],
        ],
    ];

    $response = $this->postJson('/api/assets', $payload);

    // 1. Provera uspešnog kreiranja
    $response->assertStatus(201)
         ->assertJsonPath('message', 'Asset created successfully')
         ->assertJsonPath('asset.name', 'MacBook Pro M2')
         ->assertJsonPath('asset.details.serial_number', 'SN-MAC-2026-X');

    // 2. Provera u bazi
    $this->assertDatabaseHas('hardware_details', [
        'serial_number' => 'SN-MAC-2026-X',
    ]);

    $this->assertDatabaseHas('assets', [
        'tenant_id'     => $tenant->id,
        'name'          => 'MacBook Pro M2',
        'asset_tag'     => 'HW-998877',
        'itemable_type' => HardwareDetail::class,
    ]);
});

test('user can create a license asset with polymorphic details', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $category = Category::factory()->create(['tenant_id' => $tenant->id]);
    $organization = Organization::factory()->create(['tenant_id' => $tenant->id]);

    Sanctum::actingAs($user);

    $payload = [
        'name'            => 'JetBrains All Products Pack',
        'asset_tag'       => 'LIC-100200',
        'type'            => 'license',
        'status'          => 'active',
        'category_id'     => $category->id,
        'organization_id' => $organization->id,
        'details'         => [
            'license_key' => 'XXXX-YYYY-ZZZZ-2026',
            'seats'       => 10,
            'expires_at'  => '2027-12-31',
        ],
    ];

    $response = $this->postJson('/api/assets', $payload);

    $response->assertStatus(201)
         ->assertJsonPath('asset.details.license_key', 'XXXX-YYYY-ZZZZ-2026')
         ->assertJsonPath('asset.details.seats', 10);

    $this->assertDatabaseHas('license_details', [
        'license_key' => 'XXXX-YYYY-ZZZZ-2026',
        'seats'       => 10,
    ]);

    $this->assertDatabaseHas('assets', [
        'tenant_id'     => $tenant->id,
        'name'          => 'JetBrains All Products Pack',
        'itemable_type' => LicenseDetail::class,
    ]);
});