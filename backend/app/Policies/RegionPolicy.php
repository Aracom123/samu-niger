<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;

class RegionPolicy
{
    /**
     * Gestion des régions réservée à l'administrateur national (section 4).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('regions.gerer');
    }

    public function view(User $user, Region $region): bool
    {
        return $user->can('regions.gerer');
    }

    public function create(User $user): bool
    {
        return $user->can('regions.gerer');
    }

    public function update(User $user, Region $region): bool
    {
        return $user->can('regions.gerer');
    }

    public function delete(User $user, Region $region): bool
    {
        return $user->can('regions.gerer');
    }
}
