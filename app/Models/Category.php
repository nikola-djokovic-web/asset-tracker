<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasUlids, BelongsToTenant, HasFactory;

    protected $fillable = ['tenant_id', 'name', 'slug'];

    public function tenants()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
