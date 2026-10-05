<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipeMobile extends Model
{
    use HasFactory;

    protected $table = 'equipes_mobiles';

    protected $fillable = ['nom', 'region_id', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function ambulances(): HasMany
    {
        return $this->hasMany(Ambulance::class);
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
}
