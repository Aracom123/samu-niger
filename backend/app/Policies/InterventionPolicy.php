<?php

namespace App\Policies;

use App\Models\Intervention;
use App\Models\User;

class InterventionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('interventions.consulter') || $user->can('interventions.consulter-affectees');
    }

    /**
     * L'administrateur peut créer n'importe quelle intervention ; une équipe
     * mobile ne peut créer que les siennes (elle est l'appelante au contact
     * du patient) — son compte doit alors être rattaché à une équipe.
     */
    public function create(User $user): bool
    {
        if ($user->can('interventions.gerer')) {
            return true;
        }

        return $user->can('interventions.creer') && $user->equipe_mobile_id !== null;
    }

    public function delete(User $user, Intervention $intervention): bool
    {
        return $user->can('interventions.gerer');
    }

    /**
     * Une équipe mobile SAMU n'accède qu'aux interventions qui lui sont
     * affectées ; un responsable régional, qu'à celles de sa région (section 20).
     */
    public function view(User $user, Intervention $intervention): bool
    {
        if ($user->hasRole('Équipe mobile SAMU')) {
            return $user->equipe_mobile_id !== null && $user->equipe_mobile_id === $intervention->equipe_mobile_id;
        }

        if ($user->hasRole('Responsable régional')) {
            return $user->region_id === $intervention->region_id;
        }

        return $user->can('interventions.consulter');
    }

    public function update(User $user, Intervention $intervention): bool
    {
        if ($user->hasRole('Équipe mobile SAMU')) {
            return $user->equipe_mobile_id !== null && $user->equipe_mobile_id === $intervention->equipe_mobile_id;
        }

        return $user->can('interventions.gerer');
    }
}
