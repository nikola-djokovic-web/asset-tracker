<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user cannot view asset belonging to another tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);

    $categoryB = Category::factory()->create(['tenant_id' => $tenantB->id]);
    $organizationB = Organization::factory()->create(['tenant_id' => $tenantB->id]);
    
    $assetB = Asset::factory()->create([
        'tenant_id' => $tenantB->id,
        'category_id' => $categoryB->id,
        'organization_id' => $organizationB->id
    ]);

    Sanctum::actingAs($userA);

    $response = $this->getJson("/api/assets/{$assetB->id}");

    $response->assertStatus(404)->assertJson([
        'message' => 'Traženi resurs nije pronađen ili nemate pristup.'
    ]);
    
});

test('user only sees assets from their own tenant in index list', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

    $categoryA = Category::factory()->create(['tenant_id' => $tenantA->id]);
    $organizationA = Organization::factory()->create(['tenant_id' => $tenantA->id]);
    Asset::factory()->count(2)->create([
        'tenant_id' => $tenantA->id,
        'category_id' => $categoryA->id,
        'organization_id' => $organizationA->id
    ]);
    
    $categoryB = Category::factory()->create(['tenant_id' => $tenantB->id]);
    $organizationB = Organization::factory()->create(['tenant_id' => $tenantB->id]);


    Asset::factory()->count(3)->create([
        'tenant_id' => $tenantB->id,
        'category_id' => $categoryB->id,
        'organization_id' => $organizationB->id
    ]);

    Sanctum::actingAs($userA);

    $response = $this->getJson('/api/assets');

    $response->assertStatus(200)
         ->assertJsonCount(2, 'data');
});
