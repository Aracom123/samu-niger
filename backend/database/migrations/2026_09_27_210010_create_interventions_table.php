<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->string('lieu_prise_en_charge')->nullable();
            $table->string('motif')->nullable();
            $table->enum('niveau_urgence', ['critique', 'urgent', 'modere', 'faible'])->default('modere');
            $table->foreignId('equipe_mobile_id')->nullable()->constrained('equipes_mobiles')->nullOnDelete();
            $table->foreignId('ambulance_id')->nullable()->constrained('ambulances')->nullOnDelete();
            $table->foreignId('structure_destination_id')->nullable()->constrained('structures_sanitaires')->nullOnDelete();

            $table->enum('statut', [
                'demande_recue',
                'intervention_affectee',
                'equipe_en_route',
                'patient_pris_en_charge',
                'recherche_structure',
                'structure_selectionnee',
                'en_route_structure',
                'patient_arrive',
                'intervention_terminee',
                'annulee',
            ])->default('demande_recue');

            $table->timestamp('demande_recue_at')->nullable();
            $table->timestamp('affectee_at')->nullable();
            $table->timestamp('equipe_en_route_at')->nullable();
            $table->timestamp('patient_pris_en_charge_at')->nullable();
            $table->timestamp('structure_selectionnee_at')->nullable();
            $table->timestamp('en_route_structure_at')->nullable();
            $table->timestamp('patient_arrive_at')->nullable();
            $table->timestamp('intervention_terminee_at')->nullable();
            $table->timestamp('annulee_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
