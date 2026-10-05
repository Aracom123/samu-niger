<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone')->nullable()->after('email');
            $table->boolean('actif')->default(true)->after('telephone');
            $table->foreignId('region_id')->nullable()->after('actif')->constrained('regions')->nullOnDelete();
            $table->foreignId('structure_sanitaire_id')->nullable()->after('region_id')->constrained('structures_sanitaires')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('structure_sanitaire_id');
            $table->dropConstrainedForeignId('region_id');
            $table->dropColumn(['telephone', 'actif']);
        });
    }
};
