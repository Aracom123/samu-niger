<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_evenements', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'saturation_atteinte',
                'saturation_complete',
                'retour_disponible',
                'absence_maj',
                'patient_oriente',
                'intervention_creee',
                'intervention_modifiee',
            ]);
            $table->foreignId('structure_sanitaire_id')->nullable()->constrained('structures_sanitaires')->cascadeOnDelete();
            $table->foreignId('intervention_id')->nullable()->constrained('interventions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('message');
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_evenements');
    }
};
