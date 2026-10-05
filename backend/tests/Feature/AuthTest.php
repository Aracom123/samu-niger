<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_reussi_retourne_un_token(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@samu-niger.ne',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'roles', 'permissions', 'token']);
    }

    public function test_login_avec_mauvais_mot_de_passe_est_refuse(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@samu-niger.ne',
            'password' => 'mauvais-mot-de-passe',
        ]);

        $response->assertUnprocessable();
    }

    public function test_route_protegee_refuse_sans_token(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $this->seed(DatabaseSeeder::class);

        User::where('email', 'admin@samu-niger.ne')->update(['actif' => false]);

        $this->postJson('/api/auth/login', [
            'email' => 'admin@samu-niger.ne',
            'password' => 'password',
        ])->assertUnprocessable();
    }
}
