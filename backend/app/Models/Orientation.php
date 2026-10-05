<?php

namespace App\Models;

use App\Observers\OrientationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(OrientationObserver::class)]
class Orientation extends Model
{
    use HasFactory;

    protected $fillable = [
        'intervention_id', 'structure_sanitaire_id', 'statut', 'motif_refus', 'created_by',
    ];

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function structureSanitaire(): BelongsTo
    {
        return $this->belongsTo(StructureSanitaire::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmationAccueil(): HasOne
    {
        return $this->hasOne(ConfirmationAccueil::class);
    }

    /**
     * La structure sanitaire confirme ou refuse l'accueil du patient orienté (section 12) :
     * met à jour le statut de l'orientation et fait progresser l'intervention en conséquence.
     */
    public function confirmerAccueil(string $reponse, ?string $motif, int $confirmedByUserId): ConfirmationAccueil
    {
        $confirmation = $this->confirmationAccueil()->create([
            'reponse' => $reponse,
            'motif' => $motif,
            'confirmed_by' => $confirmedByUserId,
            'confirmed_at' => now(),
        ]);

        $this->update(['statut' => $reponse === 'acceptee' ? 'acceptee' : 'refusee']);

        $this->intervention->changerStatut(
            $reponse === 'acceptee' ? 'en_route_structure' : 'recherche_structure'
        );

        return $confirmation;
    }
}
