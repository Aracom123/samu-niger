<?php

namespace App\Policies;

use App\Models\Ambulance;
use App\Models\User;

class AmbulancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('interventions.gerer')
            || $user->can('interventions.consulter')
            || $user->can('interventions.consulter-affectees');
    }

    public function view(User $user, Ambulance $ambulance): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('interventions.gerer');
    }

    public function update(User $user, Ambulance $ambulance): bool
    {
        return $user->can('interventions.gerer');
    }

    public function delete(User $user, Ambulance $ambulance): bool
    {
        return $user->can('interventions.gerer');
    }
}
