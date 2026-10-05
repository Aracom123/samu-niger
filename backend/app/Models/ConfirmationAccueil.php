<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfirmationAccueil extends Model
{
    use HasFactory;

    protected $table = 'confirmations_accueil';

    protected $fillable = ['orientation_id', 'reponse', 'motif', 'confirmed_by', 'confirmed_at'];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function orientation(): BelongsTo
    {
        return $this->belongsTo(Orientation::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
