<?php

namespace App\Policies;

use App\Models\Capacite;
use App\Models\User;

class CapacitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('capacite.consulter');
    }

    public function view(User $user, Capacite $capacite): bool
    {
        return $user->can('capacite.consulter');
    }

    /**
     * Seuls les responsables/agents de la structure concernée (ou un titulaire
     * de la permission globale) peuvent modifier une capacité (section 20).
     */
    public function update(User $user, Capacite $capacite): bool
    {
        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire'])) {
            return $user->structure_sanitaire_id === $capacite->structure_sanitaire_id;
        }

        return $user->can('capacite.mettre-a-jour');
    }

    public function create(User $user): bool
    {
        return $user->can('capacite.mettre-a-jour');
    }

    public function delete(User $user, Capacite $capacite): bool
    {
        return $user->can('structures.gerer');
    }
}
