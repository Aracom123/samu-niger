<?php

namespace App\Models;

use App\Observers\InterventionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(InterventionObserver::class)]
class Intervention extends Model
{
    use HasFactory;

    /**
     * Statuts possibles, dans l'ordre du parcours (section 10 et 13 du cahier des charges).
     * Chaque statut est associé à la colonne d'horodatage correspondante.
     */
    public const STATUTS_HORODATAGE = [
        'demande_recue' => 'demande_recue_at',
        'intervention_affectee' => 'affectee_at',
        'equipe_en_route' => 'equipe_en_route_at',
        'patient_pris_en_charge' => 'patient_pris_en_charge_at',
        'recherche_structure' => null,
        'structure_selectionnee' => 'structure_selectionnee_at',
        'en_route_structure' => 'en_route_structure_at',
        'patient_arrive' => 'patient_arrive_at',
        'intervention_terminee' => 'intervention_terminee_at',
        'annulee' => 'annulee_at',
    ];

    protected $fillable = [
        'numero', 'patient_id', 'region_id', 'lieu_prise_en_charge', 'motif', 'niveau_urgence',
        'equipe_mobile_id', 'ambulance_id', 'structure_destination_id', 'statut',
        'demande_recue_at', 'affectee_at', 'equipe_en_route_at', 'patient_pris_en_charge_at',
        'structure_selectionnee_at', 'en_route_structure_at', 'patient_arrive_at',
        'intervention_terminee_at', 'annulee_at', 'created_by',
    ];

    protected $casts = [
        'demande_recue_at' => 'datetime',
        'affectee_at' => 'datetime',
        'equipe_en_route_at' => 'datetime',
        'patient_pris_en_charge_at' => 'datetime',
        'structure_selectionnee_at' => 'datetime',
        'en_route_structure_at' => 'datetime',
        'patient_arrive_at' => 'datetime',
        'intervention_terminee_at' => 'datetime',
        'annulee_at' => 'datetime',
    ];

    /**
     * Fait avancer l'intervention vers un nouveau statut. L'horodatage de
     * l'étape (et les effets de bord comme l'occupation d'un lit à l'arrivée
     * du patient) sont gérés par InterventionObserver, quel que soit le point
     * d'entrée qui modifie le statut.
     */
    public function changerStatut(string $statut): void
    {
        $this->statut = $statut;
        $this->save();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function equipeMobile(): BelongsTo
    {
        return $this->belongsTo(EquipeMobile::class);
    }

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class);
    }

    public function structureDestination(): BelongsTo
    {
        return $this->belongsTo(StructureSanitaire::class, 'structure_destination_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orientations(): HasMany
    {
        return $this->hasMany(Orientation::class);
    }
}
