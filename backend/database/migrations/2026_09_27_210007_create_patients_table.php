<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('identifiant')->unique();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->enum('sexe', ['M', 'F', 'inconnu'])->default('inconnu');
            $table->date('date_naissance')->nullable();
            $table->unsignedTinyInteger('age_approximatif')->nullable();
            $table->string('telephone')->nullable();
            $table->string('localisation_prise_en_charge')->nullable();
            $table->string('motif')->nullable();
            $table->enum('niveau_urgence', ['critique', 'urgent', 'modere', 'faible'])->default('modere');
            $table->text('observations')->nullable();
            $table->foreignId('structure_origine_id')->nullable()->constrained('structures_sanitaires')->nullOnDelete();
            $table->foreignId('structure_destination_id')->nullable()->constrained('structures_sanitaires')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
