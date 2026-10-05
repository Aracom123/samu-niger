<?php

namespace App\Models;

use App\Observers\StructureSanitaireObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(StructureSanitaireObserver::class)]
class StructureSanitaire extends Model
{
    use HasFactory;

    protected $table = 'structures_sanitaires';

    protected $fillable = [
        'code', 'nom', 'region_id', 'type_structure_id',
        'departement', 'commune', 'localite', 'adresse',
        'latitude', 'longitude', 'telephone', 'email',
        'responsable_user_id', 'statut',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function typeStructure(): BelongsTo
    {
        return $this->belongsTo(TypeStructure::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_user_id');
    }

    /**
     * Une structure sanitaire n'a qu'un seul service d'urgence, point d'entrée
     * unique des patients — sa capacité d'accueil lui est donc directement rattachée.
     */
    public function capacite(): HasOne
    {
        return $this->hasOne(Capacite::class);
    }

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class, 'structure_sanitaire_id');
    }

    public function orientations(): HasMany
    {
        return $this->hasMany(Orientation::class);
    }

    public function patientsOrigine(): HasMany
    {
        return $this->hasMany(Patient::class, 'structure_origine_id');
    }

    public function patientsDestination(): HasMany
    {
        return $this->hasMany(Patient::class, 'structure_destination_id');
    }
}
