<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_login_and_registration_pages(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in to Vaultify');
        $this->get('/register')->assertOk()->assertSee('Create your Vaultify account');
    }

    public function test_mutation_forms_render_csrf_protection(): void
    {
        $this->get('/register')->assertOk()->assertSee('name="_token"', false);

        $this->actingAs(User::factory()->create());

        $this->get('/vault')
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="csrf-token"', false);
        $this->get(route('vault.credentials.create'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_valid_registration_logs_user_in_and_hashes_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Sari',
            'email' => 'sari@example.test',
            'password' => 'password-rahasia',
            'password_confirmation' => 'password-rahasia',
        ]);

        $response->assertRedirect(route('vault'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'sari@example.test']);
        $this->assertTrue(Hash::check('password-rahasia', User::firstOrFail()->password));
        $this->assertNotSame('password-rahasia', User::firstOrFail()->password);
    }

    public function test_duplicate_email_and_invalid_registration_are_rejected(): void
    {
        User::factory()->create(['email' => 'sari@example.test']);

        $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'sari@example.test',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertRedirect('/register')->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_valid_login_regenerates_session(): void
    {
        $user = User::factory()->create(['password' => 'password-rahasia']);
        $this->withSession(['before_login' => true]);
        $oldSessionId = $this->app['session.store']->getId();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password-rahasia',
        ]);

        $response->assertRedirect(route('vault'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSessionId, $this->app['session.store']->getId());
    }

    public function test_invalid_login_is_rejected_without_revealing_account_state(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors([
            'email' => 'The provided email or password is incorrect.',
        ]);
    }

    public function test_authenticated_users_are_redirected_away_from_guest_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')->assertRedirect(route('vault'));
        $this->get('/register')->assertRedirect(route('vault'));
    }

    public function test_guests_cannot_view_private_page(): void
    {
        $this->get('/vault')->assertRedirect(route('login'));
        $this->get('/password-generator')->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_vault_and_password_generator(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/vault')->assertOk()->assertSee('No credentials yet');
        $this->get('/password-generator')
            ->assertOk()
            ->assertSee('Password Generator')
            ->assertSee('No password generated')
            ->assertSee('data-password-generator', false)
            ->assertSee('min="8"', false)
            ->assertSee('max="64"', false);
    }

    public function test_logout_invalidates_session_and_get_is_not_allowed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/logout')->assertMethodNotAllowed();
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
