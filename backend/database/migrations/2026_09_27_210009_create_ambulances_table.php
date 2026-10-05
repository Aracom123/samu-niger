<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambulances', function (Blueprint $table) {
            $table->id();
            $table->string('immatriculation')->unique();
            $table->foreignId('equipe_mobile_id')->nullable()->constrained('equipes_mobiles')->nullOnDelete();
            $table->enum('statut', ['disponible', 'en_intervention', 'hors_service'])->default('disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambulances');
    }
};
