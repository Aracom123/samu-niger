<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_sanitaire_id')->unique()->constrained('structures_sanitaires')->cascadeOnDelete();
            $table->unsignedInteger('lits_totaux')->default(0);
            $table->unsignedInteger('lits_occupes')->default(0);
            $table->unsignedInteger('lits_reserves')->default(0);
            $table->unsignedInteger('lits_indisponibles')->default(0);
            $table->integer('lits_disponibles')
                ->storedAs('lits_totaux - lits_occupes - lits_reserves - lits_indisponibles');
            $table->enum('statut_saturation', ['disponible', 'capacite_reduite', 'presque_saturee', 'saturee'])
                ->default('disponible');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacites');
    }
};
