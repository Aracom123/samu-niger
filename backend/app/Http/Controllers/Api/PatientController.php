<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Patient::class);

        $query = Patient::query();

        if ($request->filled('q')) {
            $terme = $request->string('q');
            $query->where(function ($q) use ($terme) {
                $q->where('nom', 'ilike', "%{$terme}%")
                    ->orWhere('prenom', 'ilike', "%{$terme}%")
                    ->orWhere('identifiant', 'ilike', "%{$terme}%")
                    ->orWhere('telephone', 'ilike', "%{$terme}%");
            });
        }

        return $query->latest()->paginate($request->integer('per_page', 20));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

        return response()->json(Patient::create($this->validated($request)), 201);
    }

    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);

        return $patient->load(['structureOrigine', 'structureDestination', 'interventions']);
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $data = $this->validated($request, $patient->id);
        $patient->update($data);

        return $patient;
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'identifiant' => ['nullable', 'string', 'max:30', Rule::unique('patients', 'identifiant')->ignore($ignoreId)],
            'nom' => ['nullable', 'string', 'max:100'],
            'prenom' => ['nullable', 'string', 'max:100'],
            'sexe' => ['sometimes', 'in:M,F,inconnu'],
            'date_naissance' => ['nullable', 'date'],
            'age_approximatif' => ['nullable', 'integer', 'min:0', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'localisation_prise_en_charge' => ['nullable', 'string', 'max:255'],
            'motif' => ['nullable', 'string', 'max:255'],
            'niveau_urgence' => ['sometimes', 'in:critique,urgent,modere,faible'],
            'observations' => ['nullable', 'string'],
            'structure_origine_id' => ['nullable', 'exists:structures_sanitaires,id'],
            'structure_destination_id' => ['nullable', 'exists:structures_sanitaires,id'],
        ]);
    }
}
