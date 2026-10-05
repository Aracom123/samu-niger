<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StructureSanitaire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StructureSanitaireController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', StructureSanitaire::class);

        $query = StructureSanitaire::query()->with(['region', 'typeStructure', 'capacite']);

        $user = $request->user();

        // Un responsable régional ne voit que les structures de sa région.
        if ($user->hasRole('Responsable régional') && $user->region_id) {
            $query->where('region_id', $user->region_id);
        }

        // Un responsable/agent de structure ne voit que sa propre structure.
        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire'])) {
            $query->where('id', $user->structure_sanitaire_id);
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->filled('type_structure_id')) {
            $query->where('type_structure_id', $request->integer('type_structure_id'));
        }

        return $query->orderBy('nom')->paginate($request->integer('per_page', 20));
    }

    public function store(Request $request)
    {
        $this->authorize('create', StructureSanitaire::class);

        $data = $this->validated($request);

        return response()->json(StructureSanitaire::create($data)->load(['region', 'typeStructure', 'capacite']), 201);
    }

    public function show(Request $request, StructureSanitaire $structureSanitaire)
    {
        $this->authorize('view', $structureSanitaire);

        return $structureSanitaire->load(['region', 'typeStructure', 'capacite', 'responsable']);
    }

    public function update(Request $request, StructureSanitaire $structureSanitaire)
    {
        $this->authorize('update', $structureSanitaire);

        $data = $this->validated($request, $structureSanitaire->id);

        $structureSanitaire->update($data);

        return $structureSanitaire->load(['region', 'typeStructure']);
    }

    public function destroy(StructureSanitaire $structureSanitaire)
    {
        $this->authorize('delete', $structureSanitaire);

        $structureSanitaire->update(['statut' => 'inactive']);

        return response()->json(['message' => 'Structure désactivée.']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('structures_sanitaires', 'code')->ignore($ignoreId)],
            'nom' => ['required', 'string', 'max:150'],
            'region_id' => ['required', 'exists:regions,id'],
            'type_structure_id' => ['required', 'exists:type_structures,id'],
            'departement' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'localite' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'responsable_user_id' => ['nullable', 'exists:users,id'],
            'statut' => ['sometimes', 'in:active,inactive'],
        ]);
    }
}
