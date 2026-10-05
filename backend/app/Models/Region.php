<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'nom', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function structuresSanitaires(): HasMany
    {
        return $this->hasMany(StructureSanitaire::class);
    }

    public function equipesMobiles(): HasMany
    {
        return $this->hasMany(EquipeMobile::class);
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
