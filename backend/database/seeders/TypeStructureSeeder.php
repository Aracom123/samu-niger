<?php

namespace Database\Seeders;

use App\Models\TypeStructure;
use Illuminate\Database\Seeder;

class TypeStructureSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'HN', 'nom' => 'Hôpital National'],
            ['code' => 'HD', 'nom' => 'Hôpital de District'],
            ['code' => 'CSI', 'nom' => 'Centre de Santé Intégré'],
        ];

        foreach ($types as $type) {
            TypeStructure::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
