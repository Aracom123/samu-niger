<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            TypeStructureSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrateur SAMU',
            'email' => 'admin@samu-niger.ne',
        ]);

        $admin->assignRole('Administrateur national');
    }
}
