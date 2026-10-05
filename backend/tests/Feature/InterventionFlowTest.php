<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Region;
use App\Models\StructureSanitaire;
use App\Models\TypeStructure;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterventionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_parcours_complet_demande_jusqu_a_confirmation_accueil(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'HNN', 'nom' => 'Hôpital National de Niamey',
            'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Administrateur national');
        $this->actingAs($admin);

        $patient = Patient::create(['sexe' => 'M', 'niveau_urgence' => 'urgent', 'motif' => 'accident']);

        $intervention = $this->postJson('/api/interventions', [
            'patient_id' => $patient->id,
            'region_id' => $region->id,
        ])->assertCreated()->json();

        $this->assertSame('demande_recue', $intervention['statut']);

        $orientation = $this->postJson('/api/orientations', [
            'intervention_id' => $intervention['id'],
            'structure_sanitaire_id' => $structure->id,
        ])->assertCreated()->json();

        $this->assertSame('structure_selectionnee', $orientation['intervention']['statut']);

        $this->postJson("/api/orientations/{$orientation['id']}/confirmation", [
            'reponse' => 'acceptee',
        ])->assertCreated();

        $interventionApres = $this->getJson("/api/interventions/{$intervention['id']}")->json();

        $this->assertSame('en_route_structure', $interventionApres['statut']);
        $this->assertNotNull($interventionApres['structure_selectionnee_at']);
        $this->assertNotNull($interventionApres['en_route_structure_at']);
    }

    public function test_refus_de_l_accueil_renvoie_l_intervention_en_recherche_de_structure(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'HNN', 'nom' => 'Hôpital National de Niamey',
            'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Administrateur national');
        $this->actingAs($admin);

        $patient = Patient::create(['sexe' => 'F', 'niveau_urgence' => 'critique']);

        $intervention = $this->postJson('/api/interventions', [
            'patient_id' => $patient->id,
            'region_id' => $region->id,
        ])->json();

        $orientation = $this->postJson('/api/orientations', [
            'intervention_id' => $intervention['id'],
            'structure_sanitaire_id' => $structure->id,
        ])->json();

        $this->postJson("/api/orientations/{$orientation['id']}/confirmation", [
            'reponse' => 'refusee',
            'motif' => 'Service saturé',
        ])->assertCreated();

        $interventionApres = $this->getJson("/api/interventions/{$intervention['id']}")->json();

        $this->assertSame('recherche_structure', $interventionApres['statut']);
    }
}
