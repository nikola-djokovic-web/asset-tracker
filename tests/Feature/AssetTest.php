<?php

use App\Models\Asset;
use App\Models\HardwareDetail;
use App\Models\Tenant;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('can filter assets by search query, status, and type', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $hw1 = HardwareDetail::create(['serial_number' => 'SN-UNIQUE-999']);
    $hw2 = HardwareDetail::create(['serial_number' => 'SN-COMMON-111']);

    $asset1 = Asset::factory()->create([
        'tenant_id'     => $tenant->id,
        'name'          => 'MacBook Pro M2',
        'status'        => 'active',
        'itemable_type' => HardwareDetail::class,
        'itemable_id'   => $hw1->id,
    ]);

    $asset2 = Asset::factory()->create([
        'tenant_id'     => $tenant->id,
        'name'          => 'Dell Monitor',
        'status'        => 'assigned',
        'itemable_type' => HardwareDetail::class,
        'itemable_id'   => $hw2->id,
    ]);

    Sanctum::actingAs($user);

    // 1. Pretraga po nazivu
    $response = $this->getJson('/api/assets?search=MacBook');
    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.id', $asset1->id);

    // 2. Pretraga po serijskom broju u polimorfnom modelu
    $response = $this->getJson('/api/assets?search=SN-UNIQUE-999');
    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.id', $asset1->id);

    // 3. Filtriranje po statusu
    $response = $this->getJson('/api/assets?status=assigned');
    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.id', $asset2->id);
});

test('can sort and paginate assets', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    Asset::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Alpha Asset']);
    Asset::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Zulu Asset']);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/assets?sort_by=name&sort_order=asc&per_page=1');

    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.name', 'Alpha Asset')
             ->assertJsonPath('meta.per_page', 1);
});

test('updating an asset automatically creates an audit log entry', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);

    $asset = Asset::factory()->create([
        'tenant_id' => $tenant->id,
        'name'      => 'MacBook Pro 16 M3',
        'status'    => 'active',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/assets/{$asset->id}", [
        'name'   => 'MacBook Pro 16 M4',
        'status' => 'active',
    ]);

    $response->assertStatus(200);

    // Provera da li je kreiran audit log zapis
    $this->assertDatabaseHas('audit_logs', [
        'tenant_id'      => $tenant->id,
        'user_id'        => $user->id,
        'event'          => 'updated',
        'auditable_type' => Asset::class,
        'auditable_id'   => $asset->id,
    ]);

    $log = AuditLog::where('auditable_id', $asset->id)->latest()->first();

    expect($log)->not->toBeNull();
    expect($log->old_values)->toHaveKey('name', 'MacBook Pro 16 M3');
    expect($log->new_values)->toHaveKey('name', 'MacBook Pro 16 M4');
});