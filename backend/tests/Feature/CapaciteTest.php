<?php

namespace Tests\Feature;

use App\Models\HistoriqueCapacite;
use App\Models\Region;
use App\Models\StructureSanitaire;
use App\Models\TypeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CapaciteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Une structure n'a qu'un seul service d'urgence : sa capacité est créée
     * automatiquement (StructureSanitaireObserver) en même temps qu'elle.
     */
    private function creerStructure(): StructureSanitaire
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);

        return StructureSanitaire::create([
            'code' => 'HNN', 'nom' => 'Hôpital National de Niamey',
            'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
    }

    public function test_la_capacite_est_creee_automatiquement_avec_la_structure(): void
    {
        $structure = $this->creerStructure();

        $this->assertNotNull($structure->capacite);
        $this->assertSame(0, $structure->capacite->lits_totaux);
    }

    public function test_lits_disponibles_est_calcule_automatiquement(): void
    {
        $structure = $this->creerStructure();

        $structure->capacite->update([
            'lits_totaux' => 20,
            'lits_occupes' => 5,
            'lits_reserves' => 2,
            'lits_indisponibles' => 1,
        ]);

        $this->assertSame(12, $structure->capacite->lits_disponibles);
    }

    #[DataProvider('seuilsSaturation')]
    public function test_statut_saturation_respecte_les_seuils_configures(int $totaux, int $occupes, string $statutAttendu): void
    {
        $structure = $this->creerStructure();

        $structure->capacite->update(['lits_totaux' => $totaux, 'lits_occupes' => $occupes]);

        $this->assertSame($statutAttendu, $structure->capacite->statut_saturation);
    }

    public static function seuilsSaturation(): array
    {
        return [
            'toutes disponibles' => [20, 0, 'disponible'],
            'capacite reduite (30%)' => [20, 14, 'capacite_reduite'],
            'presque saturee (10%)' => [20, 18, 'presque_saturee'],
            'saturee (0%)' => [20, 20, 'saturee'],
        ];
    }

    public function test_une_mise_a_jour_est_historisee(): void
    {
        $structure = $this->creerStructure();

        $structure->capacite->update(['lits_totaux' => 20, 'lits_occupes' => 5]);
        $structure->capacite->update(['lits_occupes' => 10]);

        $this->assertSame(2, HistoriqueCapacite::count());

        $derniere = HistoriqueCapacite::latest('id')->first();
        $this->assertSame(5, $derniere->ancienne_valeur['lits_occupes']);
        $this->assertSame(10, $derniere->nouvelle_valeur['lits_occupes']);
    }

    public function test_capacite_periodique_est_detectee_comme_perimee(): void
    {
        $structure = $this->creerStructure();
        $capacite = $structure->capacite;

        $this->assertFalse($capacite->estPerimee());

        DB::table('capacites')
            ->where('id', $capacite->id)
            ->update(['updated_at' => now()->subHours(2)]);

        $this->assertTrue($capacite->fresh()->estPerimee());
    }
}
