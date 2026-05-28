<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_returns_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_demo_accounts_are_seeded_with_expected_roles_and_password(): void
    {
        $this->seed();

        $accounts = [
            'admin@smartstock.id'   => 'Admin',
            'manajer@smartstock.id' => 'Manajer Gudang',
            'staf@smartstock.id'    => 'Staf Gudang',
            'viewer@smartstock.id'  => 'Viewer',
        ];

        foreach ($accounts as $email => $role) {
            $user = User::where('email', $email)->first();

            $this->assertNotNull($user, "Akun demo {$email} tidak ditemukan.");
            $this->assertSame($role, $user->role);
            $this->assertTrue(Hash::check('password', $user->password), "Password akun {$email} tidak sesuai.");
        }
    }
}
