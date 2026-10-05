<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Les patients sont enregistrés et consultés par les équipes mobiles SAMU (section 4).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('patients.consulter');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->can('patients.consulter');
    }

    public function create(User $user): bool
    {
        return $user->can('patients.enregistrer');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->can('patients.enregistrer');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('patients.enregistrer');
    }
}
