<?php

namespace App\Observers;

use App\Models\Capacite;
use App\Models\HistoriqueCapacite;

class CapaciteObserver
{
    private const CHAMPS_SUIVIS = ['lits_totaux', 'lits_occupes', 'lits_reserves', 'lits_indisponibles'];

    /**
     * Recalcule le statut de saturation avant toute écriture (création ou mise à jour).
     */
    public function saving(Capacite $capacite): void
    {
        $capacite->statut_saturation = $capacite->calculerStatutSaturation();
    }

    /**
     * PostgreSQL ne renvoie pas la colonne générée `lits_disponibles` après
     * un insert/update : on la recharge pour que l'objet en mémoire soit fiable.
     */
    public function saved(Capacite $capacite): void
    {
        $capacite->refresh();
    }

    /**
     * Consigne l'historique des changements de capacité (section 8.1 du cahier des charges).
     */
    public function updated(Capacite $capacite): void
    {
        $changements = array_intersect_key($capacite->getChanges(), array_flip(self::CHAMPS_SUIVIS));

        if (empty($changements)) {
            return;
        }

        $original = $capacite->getOriginal();

        HistoriqueCapacite::create([
            'capacite_id' => $capacite->id,
            'structure_sanitaire_id' => $capacite->structure_sanitaire_id,
            'user_id' => $capacite->updated_by,
            'ancienne_valeur' => array_intersect_key($original, array_flip(self::CHAMPS_SUIVIS)),
            'nouvelle_valeur' => array_intersect_key($capacite->getAttributes(), array_flip(self::CHAMPS_SUIVIS)),
        ]);
    }
}
