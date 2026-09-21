<?php

use App\Models\Asset;
use App\Models\User;
use App\Models\Tenant;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can checkout an active asset', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $assignee = User::factory()->create(['tenant_id' => $tenant->id]);
    $asset = Asset::factory()->create([
        'tenant_id' => $tenant->id,
        'status'    => 'active',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/assets/{$asset->id}/checkout", [
        'assigned_to_user_id'   => $assignee->id,
        'notes'                 => 'Zadužen za rad.',
        'condition_on_checkout' => 'good',
    ]);

    $response->assertStatus(201)
             ->assertJsonPath('message', 'Asset uspesno zaduzen.');

    $this->assertDatabaseHas('assets', [
        'id'     => $asset->id,
        'status' => 'assigned',
    ]);

    $this->assertDatabaseHas('asset_assignments', [
        'asset_id'            => $asset->id,
        'assigned_to_user_id' => $assignee->id,
        'assigned_by_user_id' => $admin->id,
        'returned_at'         => null,
    ]);
});

test('cannot checkout an already assigned asset', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $assignee = User::factory()->create(['tenant_id' => $tenant->id]);
    $asset = Asset::factory()->create([
        'tenant_id' => $tenant->id,
        'status'    => 'active',
    ]);

    Sanctum::actingAs($admin);

    // Prvo zaduživanje
    $this->postJson("/api/assets/{$asset->id}/checkout", [
        'assigned_to_user_id' => $assignee->id,
    ]);

    // Drugo zaduživanje (mora da padne)
    $response = $this->postJson("/api/assets/{$asset->id}/checkout", [
        'assigned_to_user_id' => $assignee->id,
    ]);

    $response->assertStatus(422)
             ->assertJsonPath('message', 'Ovaj aset je već zadužen i mora se prvo razdužiti.');
});

test('user can checkin an assigned asset', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $assignee = User::factory()->create(['tenant_id' => $tenant->id]);
    $asset = Asset::factory()->create([
        'tenant_id' => $tenant->id,
        'status'    => 'active',
    ]);

    Sanctum::actingAs($admin);

    // Zaduživanje
    $this->postJson("/api/assets/{$asset->id}/checkout", [
        'assigned_to_user_id' => $assignee->id,
    ]);

    // Razduživanje
    $response = $this->postJson("/api/assets/{$asset->id}/checkin", [
        'condition_on_checkin' => 'good',
        'notes'                => 'Vraćeno u ispravnom stanju.',
    ]);

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Asset uspesno razduzen.');

    $this->assertDatabaseHas('assets', [
        'id'     => $asset->id,
        'status' => 'active',
    ]);

    $this->assertDatabaseMissing('asset_assignments', [
        'asset_id'    => $asset->id,
        'returned_at' => null,
    ]);
});

test('user cannot checkout asset belonging to another tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = User::factory()->create(['tenant_id' => $tenantA->id]);
    $userB = User::factory()->create(['tenant_id' => $tenantB->id]);

    $assetB = Asset::factory()->create([
        'tenant_id' => $tenantB->id,
        'status'    => 'active',
    ]);

    Sanctum::actingAs($userA);

    $response = $this->postJson("/api/assets/{$assetB->id}/checkout", [
        'assigned_to_user_id' => $userB->id,
    ]);

    // Route model binding ili policy vraća 404/403 za nepostojeći resurs u sklopu tenanta
    $response->assertStatus(404);
});

test('can fetch assignment history for an asset', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $assignee = User::factory()->create(['tenant_id' => $tenant->id]);
    $asset = Asset::factory()->create([
        'tenant_id' => $tenant->id,
        'status'    => 'active',
    ]);

    Sanctum::actingAs($admin);

    // Kreiramo jedno zaduženje
    $this->postJson("/api/assets/{$asset->id}/checkout", [
        'assigned_to_user_id' => $assignee->id,
        'notes'               => 'Test zaduženje.',
    ]);

    // Pozivamo endpoint za istoriju
    $response = $this->getJson("/api/assets/{$asset->id}/assignments");

    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.asset_id', $asset->id)
             ->assertJsonPath('data.0.assigned_to.id', $assignee->id)
             ->assertJsonPath('data.0.is_active', true);
});

test('can fetch assignment history for a user', function () {
    $tenant = Tenant::factory()->create();
    $admin = User::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $asset = Asset::factory()->create([
        'tenant_id' => $tenant->id,
        'status'    => 'active',
    ]);

    Sanctum::actingAs($admin);

    // Zadužujemo aset korisniku
    $this->postJson("/api/assets/{$asset->id}/checkout", [
        'assigned_to_user_id' => $user->id,
    ]);

    // Pozivamo endpoint za zaduženja korisnika
    $response = $this->getJson("/api/users/{$user->id}/assignments");

    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.assigned_to.id', $user->id);
});