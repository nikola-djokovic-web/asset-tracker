<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasUlids;

    // Isključujemo auto-incrementing jer je ID tipa ULID (string)
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Polja koja se mogu masovno popunjavati.
     */
    protected $fillable = [
        'tenant_id', // Neophodno za Multi-tenancy!
        'name',
        'email',
        'password',
    ];

    /**
     * Polja koja se sakrivaju u JSON odgovoru.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}