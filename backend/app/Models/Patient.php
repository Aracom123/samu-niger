<?php

namespace App\Models;

use App\Observers\PatientObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(PatientObserver::class)]
class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifiant', 'nom', 'prenom', 'sexe', 'date_naissance', 'age_approximatif',
        'telephone', 'localisation_prise_en_charge', 'motif', 'niveau_urgence',
        'observations', 'structure_origine_id', 'structure_destination_id',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function structureOrigine(): BelongsTo
    {
        return $this->belongsTo(StructureSanitaire::class, 'structure_origine_id');
    }

    public function structureDestination(): BelongsTo
    {
        return $this->belongsTo(StructureSanitaire::class, 'structure_destination_id');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    /**
     * Libellé d'affichage combinant le code patient et son nom/prénom
     * lorsqu'ils sont disponibles (section 9 : identité parfois inconnue).
     */
    public function getLibelleAttribute(): string
    {
        $nomComplet = trim(implode(' ', array_filter([$this->nom, $this->prenom])));

        return $nomComplet === '' ? $this->identifiant : "{$this->identifiant} — {$nomComplet}";
    }
}
