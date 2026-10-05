<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confirmations_accueil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orientation_id')->unique()->constrained('orientations')->cascadeOnDelete();
            $table->enum('reponse', ['acceptee', 'refusee']);
            $table->string('motif')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confirmations_accueil');
    }
};
