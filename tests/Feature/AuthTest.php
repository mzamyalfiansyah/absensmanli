<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SMAN 5 KARAWANG');
    }

    public function test_superadmin_can_login_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_student_must_change_password_on_first_login(): void
    {
        $response = $this->post('/login', [
            'username' => '20261002', // Annisa Rahmawati (has must_change_password = true)
            'password' => 'smanli2026',
        ]);

        $response->assertRedirect(route('password.change'));
        $this->assertAuthenticated();

        // While must_change_password is true, navigating to /dashboard should redirect back to /change-password
        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertRedirect(route('password.change'));

        // Perform password change
        $updateResponse = $this->post('/change-password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $updateResponse->assertRedirect('/dashboard');
        $this->assertFalse(auth()->user()->fresh()->must_change_password);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::where('username', '20261003')->first();
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'username' => '20261003',
            'password' => 'smanli2026',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('username', 'superadmin')->first();
        $this->actingAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
