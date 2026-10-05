<?php

namespace App\Observers;

use App\Models\Orientation;

class OrientationObserver
{
    public function creating(Orientation $orientation): void
    {
        if (blank($orientation->created_by) && auth()->check()) {
            $orientation->created_by = auth()->id();
        }
    }

    /**
     * Sélectionner une structure d'orientation fait avancer l'intervention
     * dans son parcours (section 11 du cahier des charges).
     */
    public function created(Orientation $orientation): void
    {
        $orientation->intervention->update(['structure_destination_id' => $orientation->structure_sanitaire_id]);
        $orientation->intervention->changerStatut('structure_selectionnee');
    }
}
