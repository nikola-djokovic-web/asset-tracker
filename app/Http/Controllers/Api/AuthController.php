<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registracija usera
     * 
     */
    public function register(Request $request){
        // 1. Validacija ulaznih podataka
        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'tenant_slug' => ['required', 'string', 'max:255', 'unique:tenants,slug'],
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8'],
        ]);

        return DB::transaction(
            function () use ($validated) {
                $tenant = Tenant::create([
                    'name' => $validated['tenant_name'],
                    'slug' => $validated['tenant_slug']
                ]);

                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                ]);

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message'      => 'Registration successful',
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                    'user'         => [
                        'id'        => $user->id,
                        'name'      => $user->name,
                        'email'     => $user->email,
                        'tenant_id' => $user->tenant_id,
                    ],
                ], 201);
            }
        );
    }

    /**
     * Autentifikacija postojeceg korisnika
     */

    public function login(Request $request){
        // 1. Validacija ulaznih podataka
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'     => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'tenant_id' => $user->tenant_id,
            ],
        ]);
    }

    /**
     * Odjava korisnika i brisanje trenutnog tokena.
     */
    public function logout(Request $request)
    {
        // Briše samo token koji je iskorišćen za ovaj zahtev
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

}