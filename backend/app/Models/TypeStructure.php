<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeStructure extends Model
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
}
