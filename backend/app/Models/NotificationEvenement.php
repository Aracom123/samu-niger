<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationEvenement extends Model
{
    use HasFactory;

    protected $table = 'notifications_evenements';

    protected $fillable = [
        'type', 'structure_sanitaire_id', 'intervention_id', 'user_id', 'message', 'lu_at',
    ];

    protected $casts = [
        'lu_at' => 'datetime',
    ];

    public function structureSanitaire(): BelongsTo
    {
        return $this->belongsTo(StructureSanitaire::class);
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marquerCommeLue(): void
    {
        $this->update(['lu_at' => now()]);
    }
}
