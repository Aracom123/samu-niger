<?php

namespace Tests\Feature;

use App\Models\HistoriqueCapacite;
use App\Models\Intervention;
use App\Models\Patient;
use App\Models\Region;
use App\Models\StructureSanitaire;
use App\Models\TypeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapaciteInterventionLinkTest extends TestCase
{
    use RefreshDatabase;

    private function creerInterventionOrienteeVers(StructureSanitaire $structure): Intervention
    {
        $patient = Patient::create(['sexe' => 'M', 'niveau_urgence' => 'urgent']);
        $intervention = Intervention::create(['patient_id' => $patient->id, 'region_id' => $structure->region_id]);
        $intervention->orientations()->create(['structure_sanitaire_id' => $structure->id]);

        return $intervention->fresh();
    }

    public function test_larrivee_du_patient_occupe_un_lit_de_la_structure_de_destination(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structure->capacite->update(['lits_totaux' => 10, 'lits_occupes' => 2]);

        $intervention = $this->creerInterventionOrienteeVers($structure);
        $this->assertSame($structure->id, $intervention->structure_destination_id);

        $intervention->changerStatut('patient_arrive');

        $this->assertSame(3, $structure->capacite->fresh()->lits_occupes);
    }

    public function test_le_meme_statut_applique_deux_fois_ne_double_pas_lincrement(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structure->capacite->update(['lits_totaux' => 10, 'lits_occupes' => 0]);

        $intervention = $this->creerInterventionOrienteeVers($structure);

        $intervention->changerStatut('patient_arrive');
        $intervention->update(['motif' => 'précision tardive']); // autre sauvegarde, statut inchangé

        $this->assertSame(1, $structure->capacite->fresh()->lits_occupes);
    }

    /**
     * Reproduit le signalement : peu importe le chemin utilisé pour faire passer
     * une intervention à "Patient arrivé" (API dédiée ou simple mise à jour, comme
     * le fait le formulaire générique du backoffice Filament), la capacité doit
     * se mettre à jour.
     */
    public function test_une_mise_a_jour_directe_du_statut_declenche_aussi_loccupation_du_lit(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structure->capacite->update(['lits_totaux' => 5, 'lits_occupes' => 0]);

        $intervention = $this->creerInterventionOrienteeVers($structure);

        // Simule une édition directe du champ "statut" (ex : formulaire Filament),
        // sans passer par Intervention::changerStatut().
        $intervention->update(['statut' => 'patient_arrive']);

        $this->assertSame(1, $structure->capacite->fresh()->lits_occupes);
    }

    public function test_larrivee_du_patient_recalcule_le_statut_de_saturation_et_lhistorique(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structure->capacite->update(['lits_totaux' => 1, 'lits_occupes' => 0]);
        $historiqueAvant = HistoriqueCapacite::where('structure_sanitaire_id', $structure->id)->count();

        $intervention = $this->creerInterventionOrienteeVers($structure);
        $intervention->changerStatut('patient_arrive');

        $capacite = $structure->capacite->fresh();
        $this->assertSame('saturee', $capacite->statut_saturation);
        $this->assertSame(
            $historiqueAvant + 1,
            HistoriqueCapacite::where('structure_sanitaire_id', $structure->id)->count(),
        );
    }

    /**
     * Le lit du service d'urgence n'est qu'un point de passage : la clôture
     * de l'intervention doit le libérer, que le patient soit ensuite admis
     * en interne ou sorti.
     */
    public function test_la_cloture_de_lintervention_libere_le_lit(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structure->capacite->update(['lits_totaux' => 10, 'lits_occupes' => 2]);

        $intervention = $this->creerInterventionOrienteeVers($structure);
        $intervention->changerStatut('patient_arrive');
        $this->assertSame(3, $structure->capacite->fresh()->lits_occupes);

        $intervention->changerStatut('intervention_terminee');

        $this->assertSame(2, $structure->capacite->fresh()->lits_occupes);
    }

    public function test_une_annulation_apres_arrivee_libere_aussi_le_lit(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structure->capacite->update(['lits_totaux' => 10, 'lits_occupes' => 0]);

        $intervention = $this->creerInterventionOrienteeVers($structure);
        $intervention->changerStatut('patient_arrive');
        $intervention->changerStatut('annulee');

        $this->assertSame(0, $structure->capacite->fresh()->lits_occupes);
    }

    public function test_une_annulation_avant_arrivee_ne_touche_pas_au_lit(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        $structure->capacite->update(['lits_totaux' => 10, 'lits_occupes' => 2]);

        $intervention = $this->creerInterventionOrienteeVers($structure);
        // Annulée directement (ex : fausse alerte), sans jamais passer par "patient arrivé".
        $intervention->changerStatut('annulee');

        $this->assertSame(2, $structure->capacite->fresh()->lits_occupes);
    }

    public function test_le_lit_ne_descend_jamais_en_dessous_de_zero(): void
    {
        $region = Region::create(['code' => 'NIA', 'nom' => 'Niamey']);
        $type = TypeStructure::create(['code' => 'HN', 'nom' => 'Hôpital National']);
        $structure = StructureSanitaire::create([
            'code' => 'S1', 'nom' => 'Structure', 'region_id' => $region->id, 'type_structure_id' => $type->id,
        ]);
        // Capacité déjà à 0 occupé au moment de l'arrivée (cas limite / données incohérentes).
        $structure->capacite->update(['lits_totaux' => 10, 'lits_occupes' => 0]);

        $intervention = $this->creerInterventionOrienteeVers($structure);
        $intervention->changerStatut('patient_arrive');
        $structure->capacite->fresh()->update(['lits_occupes' => 0]); // remis à 0 manuellement entre temps
        $intervention->changerStatut('intervention_terminee');

        $this->assertSame(0, $structure->capacite->fresh()->lits_occupes);
    }
}
