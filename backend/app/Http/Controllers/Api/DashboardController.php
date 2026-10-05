<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Capacite;
use App\Models\Intervention;
use App\Models\StructureSanitaire;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Indicateurs temps réel pour le tableau de bord des responsables SAMU (section 16).
     */
    public function index(Request $request)
    {
        $query = StructureSanitaire::query();
        $interventions = Intervention::query();

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
            $interventions->where('region_id', $request->integer('region_id'));
        }

        $structureIds = $query->pluck('id');

        $capacites = Capacite::whereIn('structure_sanitaire_id', $structureIds)->get();

        return response()->json([
            'interventions' => [
                'aujourd_hui' => (clone $interventions)->whereDate('created_at', today())->count(),
                'en_cours' => (clone $interventions)->whereNotIn('statut', ['intervention_terminee', 'annulee'])->count(),
                'terminees' => (clone $interventions)->where('statut', 'intervention_terminee')->count(),
                'patients_en_attente_orientation' => (clone $interventions)->where('statut', 'recherche_structure')->count(),
                'patients_orientes' => (clone $interventions)->whereIn('statut', ['structure_selectionnee', 'en_route_structure'])->count(),
                'patients_arrives' => (clone $interventions)->where('statut', 'patient_arrive')->count(),
            ],
            'structures' => [
                'total' => $structureIds->count(),
                'disponibles' => $capacites->where('statut_saturation', 'disponible')->count(),
                'presque_saturees' => $capacites->where('statut_saturation', 'presque_saturee')->count(),
                'saturees' => $capacites->where('statut_saturation', 'saturee')->count(),
            ],
            'lits' => [
                'totaux' => $capacites->sum('lits_totaux'),
                'occupes' => $capacites->sum('lits_occupes'),
                'disponibles' => $capacites->sum('lits_disponibles'),
            ],
            'par_region' => StructureSanitaire::query()
                ->selectRaw('region_id, count(*) as nb_structures')
                ->groupBy('region_id')
                ->with('region:id,nom')
                ->get(),
        ]);
    }
}
