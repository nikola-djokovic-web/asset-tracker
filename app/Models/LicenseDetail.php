<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LicenseDetail extends Model
{
    use HasUlids, HasFactory;
    
    protected $fillable = ['license_key', 'seats', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'seats'      => 'integer',
    ];

    public function asset(): MorphOne
    {
        return $this->morphOne(Asset::class, 'itemable');
    }
}
