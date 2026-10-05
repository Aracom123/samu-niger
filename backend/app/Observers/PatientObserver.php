<?php

namespace App\Observers;

use App\Models\Patient;
use Illuminate\Support\Str;

class PatientObserver
{
    public function creating(Patient $patient): void
    {
        if (blank($patient->identifiant)) {
            $patient->identifiant = 'PAT-'.strtoupper(Str::random(8));
        }
    }
}
