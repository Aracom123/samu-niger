<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['code' => 'AGA', 'nom' => 'Agadez'],
            ['code' => 'DIF', 'nom' => 'Diffa'],
            ['code' => 'DOS', 'nom' => 'Dosso'],
            ['code' => 'MAR', 'nom' => 'Maradi'],
            ['code' => 'NIA', 'nom' => 'Niamey'],
            ['code' => 'TAH', 'nom' => 'Tahoua'],
            ['code' => 'TIL', 'nom' => 'Tillabéri'],
            ['code' => 'ZIN', 'nom' => 'Zinder'],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(['code' => $region['code']], $region);
        }
    }
}
