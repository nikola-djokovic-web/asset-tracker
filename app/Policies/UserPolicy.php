<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authenticatedUser, User $targetUser): bool
    {
        return $authenticatedUser->tenant_id === $targetUser->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authenticatedUser, User $targetUser): bool
    {
        return $authenticatedUser->tenant_id === $targetUser->tenant_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authenticatedUser, User $targetUser): bool
    {
        // Korisnik ne može da obriše sam sebe
        if ($authenticatedUser->id === $targetUser->id) {
            return false;
        }

        return $authenticatedUser->tenant_id === $targetUser->tenant_id;
    }

}
