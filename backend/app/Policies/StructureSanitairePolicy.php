<?php

namespace App\Policies;

use App\Models\StructureSanitaire;
use App\Models\User;

class StructureSanitairePolicy
{
    /**
     * Visible par l'administrateur, un responsable régional, un utilisateur
     * rattaché à une structure (pour consulter la sienne), ou toute personne
     * habilitée à consulter les capacités — une équipe mobile SAMU doit
     * pouvoir parcourir les structures pour orienter un patient (section 11).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('structures.gerer')
            || $user->can('region.consulter')
            || $user->can('capacite.consulter')
            || $user->structure_sanitaire_id !== null;
    }

    /**
     * Consultation : responsable régional limité à sa région, responsable/agent
     * de structure limité à sa propre structure (section 20 du cahier des charges).
     */
    public function view(User $user, StructureSanitaire $structureSanitaire): bool
    {
        if ($user->hasRole('Responsable régional')) {
            return $user->region_id === $structureSanitaire->region_id;
        }

        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire'])) {
            return $user->structure_sanitaire_id === $structureSanitaire->id;
        }

        return $user->can('structures.gerer') || $user->can('capacite.consulter');
    }

    public function update(User $user, StructureSanitaire $structureSanitaire): bool
    {
        if ($user->hasRole('Responsable de structure sanitaire')) {
            return $user->structure_sanitaire_id === $structureSanitaire->id;
        }

        return $user->can('structures.gerer');
    }

    public function create(User $user): bool
    {
        return $user->can('structures.gerer');
    }

    public function delete(User $user, StructureSanitaire $structureSanitaire): bool
    {
        return $user->can('structures.gerer');
    }
}
