<?php

namespace App\Policies;

use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShoppingItemPolicy
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
    public function view(User $user, ShoppingItem $shoppingItem): bool
    {
        return $user->household_id === $shoppingItem->household_id;
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
    public function update(User $user, ShoppingItem $shoppingItem): bool
    {
        return $user->household_id === $shoppingItem->household_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ShoppingItem $shoppingItem): bool
    {
        return $user->household_id === $shoppingItem->household_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ShoppingItem $shoppingItem): bool
    {
        return $user->household_id === $shoppingItem->household_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ShoppingItem $shoppingItem): bool
    {
        return $user->household_id === $shoppingItem->household_id;
    }
}
