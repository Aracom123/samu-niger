<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueCapacite extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['capacite_id', 'structure_sanitaire_id', 'user_id', 'ancienne_valeur', 'nouvelle_valeur'];

    protected $casts = [
        'ancienne_valeur' => 'array',
        'nouvelle_valeur' => 'array',
        'created_at' => 'datetime',
    ];

    public function capacite(): BelongsTo
    {
        return $this->belongsTo(Capacite::class);
    }

    public function structureSanitaire(): BelongsTo
    {
        return $this->belongsTo(StructureSanitaire::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
