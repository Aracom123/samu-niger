<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orientation;
use Illuminate\Http\Request;

class ConfirmationAccueilController extends Controller
{
    /**
     * La structure sanitaire confirme ou refuse l'accueil du patient orienté (section 12).
     */
    public function store(Request $request, Orientation $orientation)
    {
        $this->authorize('update', $orientation);

        $data = $request->validate([
            'reponse' => ['required', 'in:acceptee,refusee'],
            'motif' => ['nullable', 'string', 'max:255'],
        ]);

        $confirmation = $orientation->confirmerAccueil($data['reponse'], $data['motif'] ?? null, $request->user()->id);

        return response()->json($confirmation->load('orientation'), 201);
    }
}
