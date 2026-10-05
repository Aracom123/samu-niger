<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historique_capacites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacite_id')->constrained('capacites')->cascadeOnDelete();
            $table->foreignId('structure_sanitaire_id')->constrained('structures_sanitaires')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('ancienne_valeur');
            $table->json('nouvelle_valeur');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_capacites');
    }
};
