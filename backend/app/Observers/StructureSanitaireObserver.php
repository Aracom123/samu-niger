<?php

namespace App\Observers;

use App\Models\StructureSanitaire;

class StructureSanitaireObserver
{
    /**
     * Chaque structure a un unique service d'urgence : sa fiche de capacité
     * (0 lit par défaut) est créée en même temps que la structure.
     */
    public function created(StructureSanitaire $structureSanitaire): void
    {
        $structureSanitaire->capacite()->create(['lits_totaux' => 0]);
    }
}
