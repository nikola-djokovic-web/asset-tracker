<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasUlids, HasFactory;
    
    protected $fillable = ['name', 'slug'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

}
