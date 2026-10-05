<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeStructure;
use Illuminate\Http\Request;

class TypeStructureController extends Controller
{
    public function index()
    {
        return TypeStructure::orderBy('nom')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:type_structures,code'],
            'nom' => ['required', 'string', 'max:100'],
            'actif' => ['boolean'],
        ]);

        return response()->json(TypeStructure::create($data), 201);
    }

    public function show(TypeStructure $typeStructure)
    {
        return $typeStructure;
    }

    public function update(Request $request, TypeStructure $typeStructure)
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:20', 'unique:type_structures,code,'.$typeStructure->id],
            'nom' => ['sometimes', 'string', 'max:100'],
            'actif' => ['boolean'],
        ]);

        $typeStructure->update($data);

        return $typeStructure;
    }

    public function destroy(TypeStructure $typeStructure)
    {
        $typeStructure->update(['actif' => false]);

        return response()->json(['message' => 'Type de structure désactivé.']);
    }
}
