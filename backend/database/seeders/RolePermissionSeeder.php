<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Rôles et permissions issus de la section 4 du cahier des charges.
     */
    public function run(): void
    {
        $permissions = [
            // Administration
            'utilisateurs.gerer',
            'regions.gerer',
            'types-structures.gerer',
            'structures.gerer',
            'parametres.gerer',
            'donnees.consulter-tout',

            // Structures / capacités
            'structure.utilisateurs.gerer',
            'capacite.consulter',
            'capacite.mettre-a-jour',
            'saturation.declarer',
            'accueil.confirmer',

            // Interventions / patients
            'interventions.consulter',
            'interventions.consulter-affectees',
            'interventions.creer',
            'interventions.gerer',
            'patients.enregistrer',
            'patients.consulter',
            'orientations.enregistrer',
            'orientations.suivre',

            // Pilotage
            'dashboard.consulter',
            'statistiques.consulter',
            'audit.consulter',

            // Régional
            'region.consulter',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'Administrateur national' => $permissions, // accès complet
            'Responsable SAMU' => [
                'interventions.consulter',
                'capacite.consulter',
                'orientations.suivre',
                'dashboard.consulter',
                'statistiques.consulter',
            ],
            'Responsable régional' => [
                'region.consulter',
                'capacite.consulter',
                'interventions.consulter',
            ],
            'Responsable de structure sanitaire' => [
                'structure.utilisateurs.gerer',
                'capacite.mettre-a-jour',
                'capacite.consulter',
                'accueil.confirmer',
                'saturation.declarer',
            ],
            'Agent de structure sanitaire' => [
                'capacite.consulter',
                'capacite.mettre-a-jour',
            ],
            'Équipe mobile SAMU' => [
                'interventions.consulter-affectees',
                'interventions.creer',
                'patients.enregistrer',
                'patients.consulter',
                'capacite.consulter',
                'orientations.enregistrer',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}
