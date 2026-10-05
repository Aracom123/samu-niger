<?php

namespace App\Policies;

use App\Models\EquipeMobile;
use App\Models\User;

class EquipeMobilePolicy
{
    /**
     * Référentiel géré par l'administrateur, consultable par toute personne
     * suivant les interventions (pour savoir à quelle équipe elles sont affectées).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('interventions.gerer')
            || $user->can('interventions.consulter')
            || $user->can('interventions.consulter-affectees');
    }

    public function view(User $user, EquipeMobile $equipeMobile): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('interventions.gerer');
    }

    public function update(User $user, EquipeMobile $equipeMobile): bool
    {
        return $user->can('interventions.gerer');
    }

    public function delete(User $user, EquipeMobile $equipeMobile): bool
    {
        return $user->can('interventions.gerer');
    }
}
