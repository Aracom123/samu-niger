<?php

namespace Tests\Feature;

use App\Filament\Resources\Capacites\CapaciteResource;
use App\Filament\Resources\Interventions\InterventionResource;
use App\Filament\Resources\Orientations\OrientationResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\Regions\RegionResource;
use App\Filament\Resources\StructureSanitaires\StructureSanitaireResource;
use App\Filament\Resources\TypeStructures\TypeStructureResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Intervention;
use App\Models\Region;
use App\Models\StructureSanitaire;
use App\Models\TypeStructure;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_responsable_regional_ne_voit_que_les_structures_de_sa_region(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $niamey = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $zinder = Region::create(['code' => 'ZIN', 'nom' => 'Zinder']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);

        $structureNiamey = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure Niamey', 'region_id' => $niamey->id, 'type_structure_id' => $type->id,
        ]);
        $structureZinder = StructureSanitaire::create([
            'code' => 'S2', 'nom' => 'Structure Zinder', 'region_id' => $zinder->id, 'type_structure_id' => $type->id,
        ]);

        $responsable = User::factory()->create(['region_id' => $zinder->id]);
        $responsable->assignRole('Responsable régional');

        $this->assertTrue($responsable->can('view', $structureZinder));
        $this->assertFalse($responsable->can('view', $structureNiamey));
    }

    public function test_agent_de_structure_ne_peut_pas_modifier_la_capacite_d_une_autre_structure(): void
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

        $capaciteA = $structureA->capacite;
        $capaciteB = $structureB->capacite;

        $agent = User::factory()->create(['structure_sanitaire_id' => $structureA->id]);
        $agent->assignRole('Agent de structure sanitaire');

        $this->assertTrue($agent->can('update', $capaciteA));
        $this->assertFalse($agent->can('update', $capaciteB));
    }

    public function test_administrateur_national_a_acces_a_tout(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Administrateur national');

        $this->assertTrue($admin->can('view', $structure));
        $this->assertTrue($admin->can('update', $structure));
    }

    /**
     * Reproduit le signalement : un utilisateur "Responsable de structure sanitaire"
     * + "Agent de structure sanitaire" ne doit voir, dans le menu du backoffice, que
     * ce qui relève de sa structure — pas les référentiels, patients ou interventions.
     */
    public function test_responsable_et_agent_de_structure_ne_voient_que_le_menu_pertinent(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);

        $user = User::factory()->create(['structure_sanitaire_id' => $structure->id]);
        $user->assignRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire']);
        $this->actingAs($user);

        // Masqué : hors du périmètre d'une structure.
        $this->assertFalse(RegionResource::canViewAny());
        $this->assertFalse(TypeStructureResource::canViewAny());
        $this->assertFalse(PatientResource::canViewAny());
        $this->assertFalse(InterventionResource::canViewAny());

        // Visible : nécessaire à la gestion de sa structure.
        $this->assertTrue(StructureSanitaireResource::canViewAny());
        $this->assertTrue(CapaciteResource::canViewAny());
        $this->assertTrue(OrientationResource::canViewAny());
        $this->assertTrue(UserResource::canViewAny());
    }

    public function test_les_listes_filament_sont_bornees_a_la_structure_de_l_utilisateur(): void
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

        $agent = User::factory()->create(['structure_sanitaire_id' => $structureA->id]);
        $agent->assignRole('Agent de structure sanitaire');
        $this->actingAs($agent);

        $structureIds = StructureSanitaireResource::getEloquentQuery()->pluck('id');
        $this->assertEquals([$structureA->id], $structureIds->all());

        $capaciteIds = CapaciteResource::getEloquentQuery()->pluck('structure_sanitaire_id');
        $this->assertEquals([$structureA->id], $capaciteIds->all());

        // Seul le "Responsable de structure sanitaire" gère les comptes (pas le simple agent).
        $responsable = User::factory()->create(['structure_sanitaire_id' => $structureA->id]);
        $responsable->assignRole('Responsable de structure sanitaire');
        $this->actingAs($responsable);

        $userIds = UserResource::getEloquentQuery()->pluck('id');
        $this->assertEqualsCanonicalizing([$agent->id, $responsable->id], $userIds->all());
    }

    public function test_seul_l_administrateur_peut_creer_une_intervention_depuis_le_backoffice(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $responsableSamu = User::factory()->create();
        $responsableSamu->assignRole('Responsable SAMU');

        $admin = User::factory()->create();
        $admin->assignRole('Administrateur national');

        $this->assertFalse($responsableSamu->can('create', Intervention::class));
        $this->assertTrue($admin->can('create', Intervention::class));
    }

    /**
     * Reproduit un second signalement : l'équipe mobile SAMU doit pouvoir
     * parcourir toutes les structures (et leur disponibilité) pour orienter
     * un patient, même si elle n'est rattachée à aucune structure ni région.
     */
    public function test_equipe_mobile_samu_voit_toutes_les_structures_pour_orienter(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure A', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        StructureSanitaire::create([
            'code' => 'S2', 'nom' => 'Structure B', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);

        $equipeMobile = User::factory()->create();
        $equipeMobile->assignRole('Équipe mobile SAMU');
        $this->actingAs($equipeMobile);

        $this->assertTrue(StructureSanitaireResource::canViewAny());
        $this->assertSame(2, StructureSanitaireResource::getEloquentQuery()->count());
    }
}
