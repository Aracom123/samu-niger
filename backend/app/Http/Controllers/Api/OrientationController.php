<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orientation;
use Illuminate\Http\Request;

class OrientationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Orientation::class);

        $query = Orientation::query()->with(['intervention.patient', 'structureSanitaire']);

        $user = $request->user();

        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire']) && $user->structure_sanitaire_id) {
            $query->where('structure_sanitaire_id', $user->structure_sanitaire_id);
        }

        if ($user->hasRole('Équipe mobile SAMU') && $user->equipe_mobile_id) {
            $query->whereHas('intervention', fn ($q) => $q->where('equipe_mobile_id', $user->equipe_mobile_id));
        }

        if ($request->filled('structure_sanitaire_id')) {
            $query->where('structure_sanitaire_id', $request->integer('structure_sanitaire_id'));
        }

        if ($request->filled('intervention_id')) {
            $query->where('intervention_id', $request->integer('intervention_id'));
        }

        return $query->latest()->paginate($request->integer('per_page', 20));
    }

    /**
     * L'équipe SAMU sélectionne une structure d'orientation pour une intervention (section 11).
     */
    public function store(Request $request)
    {
        $this->authorize('create', Orientation::class);

        $data = $request->validate([
            'intervention_id' => ['required', 'exists:interventions,id'],
            'structure_sanitaire_id' => ['required', 'exists:structures_sanitaires,id'],
        ]);

        $orientation = Orientation::create($data);

        return response()->json($orientation->load(['intervention', 'structureSanitaire']), 201);
    }

    public function show(Orientation $orientation)
    {
        $this->authorize('view', $orientation);

        return $orientation->load(['intervention.patient', 'structureSanitaire', 'confirmationAccueil']);
    }
}
