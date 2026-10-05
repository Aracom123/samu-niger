<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Intervention::class);

        $query = Intervention::query()->with(['patient', 'region', 'equipeMobile', 'structureDestination']);

        $user = $request->user();

        if ($user->hasRole('Équipe mobile SAMU') && $user->equipe_mobile_id) {
            $query->where('equipe_mobile_id', $user->equipe_mobile_id);
        }

        if ($user->hasRole('Responsable régional') && $user->region_id) {
            $query->where('region_id', $user->region_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut'));
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        return $query->latest()->paginate($request->integer('per_page', 20));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Intervention::class);

        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'lieu_prise_en_charge' => ['nullable', 'string', 'max:255'],
            'motif' => ['nullable', 'string', 'max:255'],
            'niveau_urgence' => ['sometimes', 'in:critique,urgent,modere,faible'],
            'equipe_mobile_id' => ['nullable', 'exists:equipes_mobiles,id'],
            'ambulance_id' => ['nullable', 'exists:ambulances,id'],
        ]);

        $user = $request->user();

        // Une équipe mobile ne peut créer une intervention que pour elle-même :
        // on ignore toute autre équipe soumise et on s'auto-assigne.
        if (! $user->can('interventions.gerer')) {
            $data['equipe_mobile_id'] = $user->equipe_mobile_id;
        }

        $intervention = Intervention::create($data);

        return response()->json($intervention->load(['patient', 'region']), 201);
    }

    public function show(Intervention $intervention)
    {
        $this->authorize('view', $intervention);

        return $intervention->load([
            'patient', 'region', 'equipeMobile', 'ambulance', 'structureDestination', 'orientations.structureSanitaire',
        ]);
    }

    public function update(Request $request, Intervention $intervention)
    {
        $this->authorize('update', $intervention);

        $data = $request->validate([
            'equipe_mobile_id' => ['nullable', 'exists:equipes_mobiles,id'],
            'ambulance_id' => ['nullable', 'exists:ambulances,id'],
            'structure_destination_id' => ['nullable', 'exists:structures_sanitaires,id'],
            'motif' => ['nullable', 'string', 'max:255'],
            'niveau_urgence' => ['sometimes', 'in:critique,urgent,modere,faible'],
        ]);

        $intervention->update($data);

        return $intervention;
    }

    /**
     * Fait progresser l'intervention dans son parcours (section 13).
     */
    public function changerStatut(Request $request, Intervention $intervention)
    {
        $this->authorize('update', $intervention);

        $data = $request->validate([
            'statut' => ['required', 'in:'.implode(',', array_keys(Intervention::STATUTS_HORODATAGE))],
        ]);

        $intervention->changerStatut($data['statut']);

        return $intervention->fresh();
    }
}
