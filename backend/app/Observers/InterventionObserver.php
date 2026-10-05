<?php

namespace App\Observers;

use App\Models\Intervention;
use Illuminate\Support\Str;

class InterventionObserver
{
    public function creating(Intervention $intervention): void
    {
        if (blank($intervention->numero)) {
            $intervention->numero = 'INT-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        }

        if (blank($intervention->statut)) {
            $intervention->statut = 'demande_recue';
        }

        if (blank($intervention->demande_recue_at)) {
            $intervention->demande_recue_at = now();
        }

        if (blank($intervention->created_by) && auth()->check()) {
            $intervention->created_by = auth()->id();
        }
    }

    /**
     * Horodate l'étape correspondant au nouveau statut, quel que soit le point
     * d'entrée (API, backoffice, ...) — section 13 du cahier des charges.
     */
    public function saving(Intervention $intervention): void
    {
        if (! $intervention->isDirty('statut')) {
            return;
        }

        $colonne = Intervention::STATUTS_HORODATAGE[$intervention->statut] ?? null;

        if ($colonne && blank($intervention->{$colonne})) {
            $intervention->{$colonne} = now();
        }
    }

    /**
     * L'arrivée effective du patient occupe un lit dans la structure de
     * destination — la disponibilité affichée aux équipes SAMU doit refléter
     * l'admission réelle, pas seulement l'orientation proposée. À la clôture
     * (ou à l'annulation après arrivée), le lit du service d'urgence est
     * libéré : ce n'est qu'un point de passage, que le patient soit ensuite
     * admis en interne ou sorti — le SAMU n'a pas à suivre la suite.
     */
    public function updated(Intervention $intervention): void
    {
        if (! $intervention->wasChanged('statut')) {
            return;
        }

        if ($intervention->statut === 'patient_arrive') {
            $intervention->structureDestination?->capacite?->enregistrerArriveePatient();

            return;
        }

        $estTerminale = in_array($intervention->statut, ['intervention_terminee', 'annulee'], true);

        if ($estTerminale && $intervention->patient_arrive_at !== null) {
            $intervention->structureDestination?->capacite?->libererLit();
        }
    }
}
