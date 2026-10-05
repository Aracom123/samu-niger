<?php

namespace App\Policies;

use App\Models\TypeStructure;
use App\Models\User;

class TypeStructurePolicy
{
    /**
     * Gestion des types de structures réservée à l'administrateur national (section 4).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('types-structures.gerer');
    }

    public function view(User $user, TypeStructure $typeStructure): bool
    {
        return $user->can('types-structures.gerer');
    }

    public function create(User $user): bool
    {
        return $user->can('types-structures.gerer');
    }

    public function update(User $user, TypeStructure $typeStructure): bool
    {
        return $user->can('types-structures.gerer');
    }

    public function delete(User $user, TypeStructure $typeStructure): bool
    {
        return $user->can('types-structures.gerer');
    }
}
