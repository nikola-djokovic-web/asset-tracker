<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
                    'password' => Hash::make($validated['password']),
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

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Neispravna email adresa ili lozinka.'
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Uspesna prijava',
            'user'    => Auth::user(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Uspesna odjava'
        ]);
    }

}