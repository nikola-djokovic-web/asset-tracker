<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasUlids, BelongsToTenant, HasFactory;

   protected $fillable = [
        'tenant_id',
        'name',
        'code'
    ];
    public function users()
    {
        return $this->hasMany(User::class)->withPivot('organization_user')->withTimestamps();
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class);
    }
}
