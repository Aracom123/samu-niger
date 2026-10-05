<?php

namespace Tests\Feature;

use App\Models\EquipeMobile;
use App\Models\Intervention;
use App\Models\Patient;
use App\Models\Region;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterventionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_equipe_mobile_peut_creer_une_intervention_pour_elle_meme(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $equipe = EquipeMobile::create(['nom' => 'Équipe Alpha', 'region_id' => $region->id]);
        $autreEquipe = EquipeMobile::create(['nom' => 'Équipe Bravo', 'region_id' => $region->id]);

        $membre = User::factory()->create(['equipe_mobile_id' => $equipe->id]);
        $membre->assignRole('Équipe mobile SAMU');

        $patient = Patient::create(['sexe' => 'M', 'niveau_urgence' => 'urgent']);

        $this->assertTrue($membre->can('create', Intervention::class));

        $this->actingAs($membre);

        $response = $this->postJson('/api/interventions', [
            'patient_id' => $patient->id,
            'region_id' => $region->id,
            'motif' => 'Accident de la circulation',
            // Tentative de se faire passer pour une autre équipe : doit être ignorée.
            'equipe_mobile_id' => $autreEquipe->id,
        ]);

        $response->assertCreated();

        $intervention = Intervention::first();
        $this->assertSame($equipe->id, $intervention->equipe_mobile_id);
        $this->assertNotSame($autreEquipe->id, $intervention->equipe_mobile_id);

        // Elle doit pouvoir la consulter et la gérer, puisqu'elle lui est bien affectée.
        $this->assertTrue($membre->can('view', $intervention));
        $this->assertTrue($membre->can('update', $intervention));
    }

    public function test_une_equipe_mobile_sans_equipe_rattachee_ne_peut_pas_creer(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $membreSansEquipe = User::factory()->create(['equipe_mobile_id' => null]);
        $membreSansEquipe->assignRole('Équipe mobile SAMU');

        $this->assertFalse($membreSansEquipe->can('create', Intervention::class));
    }

    public function test_le_responsable_samu_ne_peut_toujours_pas_creer_dintervention(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $responsableSamu = User::factory()->create();
        $responsableSamu->assignRole('Responsable SAMU');

        $this->assertFalse($responsableSamu->can('create', Intervention::class));
    }
}
