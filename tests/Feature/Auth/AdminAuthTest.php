<?php

namespace Tests\Feature\Auth;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_admin_can_view_login_page()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Auth/Login'));
    }

    public function test_admin_can_register()
    {
        $response = $this->post('/admin/register', [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertSame(UserType::ADMIN, $user->type);
    }

    public function test_admin_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'type' => UserType::ADMIN,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }
}
