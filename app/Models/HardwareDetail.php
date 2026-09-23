<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HardwareDetail extends Model
{
    use HasUlids, HasFactory;

    protected $guarded = [];

    protected $fillable = ['serial_number', 'specs'];

    protected $casts = [
        'specs' => 'array',
    ];

    public function asset(): MorphOne
    {
        return $this->morphOne(Asset::class, 'itemable');
    }
}
