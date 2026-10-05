<?php

namespace Tests\Feature;

use App\Models\Intervention;
use App\Models\Patient;
use App\Models\Region;
use App\Models\StructureSanitaire;
use App\Models\TypeStructure;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmationAccueilTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepter_l_accueil_fait_passer_l_intervention_en_route_vers_la_structure(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);

        $responsable = User::factory()->create(['structure_sanitaire_id' => $structure->id]);
        $responsable->assignRole('Responsable de structure sanitaire');

        $patient = Patient::create(['sexe' => 'M', 'niveau_urgence' => 'urgent']);
        $intervention = Intervention::create(['patient_id' => $patient->id, 'region_id' => $region->id]);
        $orientation = $intervention->orientations()->create(['structure_sanitaire_id' => $structure->id]);

        $confirmation = $orientation->confirmerAccueil('acceptee', null, $responsable->id);

        $this->assertSame('acceptee', $orientation->fresh()->statut);
        $this->assertSame('en_route_structure', $intervention->fresh()->statut);
        $this->assertSame($responsable->id, $confirmation->confirmed_by);
    }

    public function test_refuser_l_accueil_renvoie_l_intervention_en_recherche_de_structure(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structureA = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure A', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structureB = StructureSanitaire::create([
            'code' => 'S2', 'nom' => 'Structure B', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);

        $responsableA = User::factory()->create(['structure_sanitaire_id' => $structureA->id]);
        $responsableA->assignRole('Responsable de structure sanitaire');

        $patient = Patient::create(['sexe' => 'F', 'niveau_urgence' => 'critique']);
        $intervention = Intervention::create(['patient_id' => $patient->id, 'region_id' => $region->id]);
        $orientationA = $intervention->orientations()->create(['structure_sanitaire_id' => $structureA->id]);
        $orientationB = $intervention->orientations()->create(['structure_sanitaire_id' => $structureB->id]);

        // Le responsable de la structure A ne peut confirmer que les orientations qui la concernent.
        $this->assertTrue($responsableA->can('update', $orientationA));
        $this->assertFalse($responsableA->can('update', $orientationB));

        $orientationA->confirmerAccueil('refusee', 'Service saturé', $responsableA->id);

        $this->assertSame('refusee', $orientationA->fresh()->statut);
        $this->assertSame('Service saturé', $orientationA->confirmationAccueil->motif);
        $this->assertSame('recherche_structure', $intervention->fresh()->statut);
    }
}
