<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        return Region::orderBy('nom')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:regions,code'],
            'nom' => ['required', 'string', 'max:100'],
            'actif' => ['boolean'],
        ]);

        return response()->json(Region::create($data), 201);
    }

    public function show(Region $region)
    {
        return $region;
    }

    public function update(Request $request, Region $region)
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:10', 'unique:regions,code,'.$region->id],
            'nom' => ['sometimes', 'string', 'max:100'],
            'actif' => ['boolean'],
        ]);

        $region->update($data);

        return $region;
    }

    public function destroy(Region $region)
    {
        $region->update(['actif' => false]);

        return response()->json(['message' => 'Région désactivée.']);
    }
}
