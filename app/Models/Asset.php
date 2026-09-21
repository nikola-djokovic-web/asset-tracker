<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\AssetAssignment;

class Asset extends Model
{
    use HasUlids, SoftDeletes, BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'organization_id', 'category_id', 'name', 'asset_tag', 'status', 'itemable_type', 'itemable_id'];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_at');
    }
}
