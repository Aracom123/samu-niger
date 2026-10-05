<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ambulance extends Model
{
    use HasFactory;

    protected $fillable = ['immatriculation', 'equipe_mobile_id', 'statut'];

    public function equipeMobile(): BelongsTo
    {
        return $this->belongsTo(EquipeMobile::class);
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
}
