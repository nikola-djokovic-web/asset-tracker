<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Category;
use App\Models\HardwareDetail;
use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        // Po defaultu pravimo polimorfni HardwareDetail
        $hardware = HardwareDetail::create([
            'serial_number' => $this->faker->uuid(),
            'specs'         => ['ram' => '16GB', 'cpu' => 'M2'],
        ]);

        return [
            'tenant_id'       => Tenant::factory(),
            'category_id'     => Category::factory(),
            'organization_id' => Organization::factory(),
            'name'            => $this->faker->word() . ' Laptop',
            'asset_tag'       => 'TAG-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status'          => 'active',
            'itemable_id'     => $hardware->id,
            'itemable_type'   => HardwareDetail::class,
        ];
    }
}