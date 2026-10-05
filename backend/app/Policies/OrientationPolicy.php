<?php

namespace App\Policies;

use App\Models\Orientation;
use App\Models\User;

class OrientationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('orientations.suivre')
            || $user->can('orientations.enregistrer')
            || $user->can('accueil.confirmer');
    }

    /**
     * Un responsable/agent de structure ne voit que les orientations qui la concernent ;
     * une équipe mobile, que celles de ses propres interventions (section 20).
     */
    public function view(User $user, Orientation $orientation): bool
    {
        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire'])) {
            return $user->structure_sanitaire_id === $orientation->structure_sanitaire_id;
        }

        if ($user->hasRole('Équipe mobile SAMU')) {
            return $user->equipe_mobile_id !== null
                && $user->equipe_mobile_id === $orientation->intervention?->equipe_mobile_id;
        }

        return $user->can('orientations.suivre');
    }

    public function create(User $user): bool
    {
        return $user->can('orientations.enregistrer');
    }

    /**
     * Confirmer/refuser l'accueil est réservé au responsable de la structure concernée.
     */
    public function update(User $user, Orientation $orientation): bool
    {
        if ($user->can('accueil.confirmer')) {
            return $user->structure_sanitaire_id === $orientation->structure_sanitaire_id;
        }

        return false;
    }
}
