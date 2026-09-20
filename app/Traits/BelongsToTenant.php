<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // 1. Automatsko filtriranje upita po tenant_id prijavljenog korisnika
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', auth()->user()->tenant_id);
            }
        });

        // 2. Automatsko dodeljivanje tenant_id pri kreiranju novog zapisa
        static::creating(function (Model $model) {
            if (!$model->tenant_id && auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    // 3. Zajednička relacija za sve modele koji koriste ovaj trait
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}