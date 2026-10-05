<?php

namespace App\Models;

use App\Observers\CapaciteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(CapaciteObserver::class)]
class Capacite extends Model
{
    use HasFactory;

    protected $fillable = [
        'structure_sanitaire_id', 'lits_totaux', 'lits_occupes',
        'lits_reserves', 'lits_indisponibles', 'statut_saturation', 'updated_by',
    ];

    protected $casts = [
        'lits_totaux' => 'integer',
        'lits_occupes' => 'integer',
        'lits_reserves' => 'integer',
        'lits_indisponibles' => 'integer',
        'lits_disponibles' => 'integer',
    ];

    public function structureSanitaire(): BelongsTo
    {
        return $this->belongsTo(StructureSanitaire::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function historique(): HasMany
    {
        return $this->hasMany(HistoriqueCapacite::class);
    }

    /**
     * Déduit le statut de saturation à partir des seuils configurés
     * (config/capacites.php) et du nombre de lits disponibles/totaux.
     */
    public function calculerStatutSaturation(): string
    {
        if ($this->lits_totaux <= 0) {
            return 'saturee';
        }

        $tauxDisponible = ($this->lits_totaux - $this->lits_occupes - $this->lits_reserves - $this->lits_indisponibles)
            / $this->lits_totaux;

        $seuils = config('capacites.seuils');

        return match (true) {
            $tauxDisponible <= $seuils['saturee'] => 'saturee',
            $tauxDisponible <= $seuils['presque_saturee'] => 'presque_saturee',
            $tauxDisponible <= $seuils['capacite_reduite'] => 'capacite_reduite',
            default => 'disponible',
        };
    }

    public function estPerimee(): bool
    {
        $minutes = config('capacites.fraicheur_max_minutes');

        return $this->updated_at?->diffInMinutes(now()) > $minutes;
    }

    /**
     * L'arrivée effective d'un patient orienté occupe un lit du service
     * d'urgence (section 33). Déclenché par InterventionObserver.
     */
    public function enregistrerArriveePatient(): void
    {
        $this->update([
            'lits_occupes' => $this->lits_occupes + 1,
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Le lit du service d'urgence n'est qu'un point de passage : la clôture
     * (ou l'annulation après arrivée) de l'intervention libère le lit, que le
     * patient soit ensuite admis en interne ou sorti. Déclenché par
     * InterventionObserver.
     */
    public function libererLit(): void
    {
        $this->update([
            'lits_occupes' => max(0, $this->lits_occupes - 1),
            'updated_by' => auth()->id(),
        ]);
    }
}
