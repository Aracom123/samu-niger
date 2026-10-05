<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Capacite;
use Illuminate\Http\Request;

class CapaciteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Capacite::class);

        $query = $this->scoped(Capacite::query()->with('structureSanitaire'), $request->user());

        if ($request->filled('structure_sanitaire_id')) {
            $query->where('structure_sanitaire_id', $request->integer('structure_sanitaire_id'));
        }

        if ($request->filled('statut_saturation')) {
            $query->where('statut_saturation', $request->string('statut_saturation'));
        }

        return $query->get()->map($this->avecFraicheur(...));
    }

    public function show(Capacite $capacite)
    {
        $this->authorize('view', $capacite);

        return $this->avecFraicheur($capacite->load('structureSanitaire'));
    }

    /**
     * Mise à jour de la capacité par la structure sanitaire (section 8).
     * Le recalcul de disponibilité, du statut de saturation et l'historisation
     * sont gérés automatiquement par CapaciteObserver.
     */
    public function update(Request $request, Capacite $capacite)
    {
        $this->authorize('update', $capacite);

        $data = $request->validate([
            'lits_totaux' => ['sometimes', 'integer', 'min:0'],
            'lits_occupes' => ['sometimes', 'integer', 'min:0'],
            'lits_reserves' => ['sometimes', 'integer', 'min:0'],
            'lits_indisponibles' => ['sometimes', 'integer', 'min:0'],
        ]);

        $data['updated_by'] = $request->user()->id;

        $capacite->update($data);

        return $this->avecFraicheur($capacite->load('structureSanitaire'));
    }

    public function historique(Capacite $capacite)
    {
        $this->authorize('view', $capacite);

        return $capacite->historique()->with('user')->latest('created_at')->paginate(20);
    }

    /**
     * Portée des données selon le rôle (section 20).
     */
    private function scoped($query, $user)
    {
        if ($user->can('structures.gerer')) {
            return $query;
        }

        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire'])) {
            return $query->where('structure_sanitaire_id', $user->structure_sanitaire_id);
        }

        if ($user->hasRole('Responsable régional') && $user->region_id) {
            return $query->whereHas('structureSanitaire', fn ($q) => $q->where('region_id', $user->region_id));
        }

        return $query;
    }

    private function avecFraicheur(Capacite $capacite): Capacite
    {
        $capacite->setAttribute('perimee', $capacite->estPerimee());

        return $capacite;
    }
}
