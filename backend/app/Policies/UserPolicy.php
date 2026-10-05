<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('utilisateurs.gerer') || $user->can('structure.utilisateurs.gerer');
    }

    /**
     * Un responsable de structure ne gère que les comptes de sa propre structure (section 4).
     */
    public function view(User $user, User $model): bool
    {
        if ($user->can('utilisateurs.gerer')) {
            return true;
        }

        if ($user->can('structure.utilisateurs.gerer')) {
            return $user->structure_sanitaire_id !== null
                && $user->structure_sanitaire_id === $model->structure_sanitaire_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('utilisateurs.gerer') || $user->can('structure.utilisateurs.gerer');
    }

    public function update(User $user, User $model): bool
    {
        return $this->view($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('utilisateurs.gerer');
    }
}
