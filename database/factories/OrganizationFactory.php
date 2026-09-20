<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name'      => $this->faker->company(),
            'code'      => strtoupper($this->faker->unique()->lexify('ORG-???')),
        ];
    }
}