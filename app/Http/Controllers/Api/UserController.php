<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', User::class);

        $users = User::where('tenant_id', $request->user()->tenant_id)->latest()->paginate(15);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validated();
        
        $user = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'] ?? 'member',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => new UserResource($user),
        ], 201);
    }

    public function show(Request $request, User $user): UserResource
    {
        Gate::authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => new UserResource($user),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
