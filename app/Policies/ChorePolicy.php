<?php

namespace App\Policies;

use App\Models\Chore;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChorePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->household_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chore $chore): bool
    {
        return $user->household_id === $chore->household_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->household_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chore $chore): bool
    {
        return $user->household_id === $chore->household_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chore $chore): bool
    {
        return $user->household_id === $chore->household_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chore $chore): bool
    {
        return $user->household_id === $chore->household_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chore $chore): bool
    {
        return $user->household_id === $chore->household_id;
    }
}
