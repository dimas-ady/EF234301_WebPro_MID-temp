<?php

namespace Tests\Feature;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class CredentialTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_an_encrypted_credential(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('vault.credentials.store'), [
            'title' => 'Email Kampus',
            'website_url' => 'https://mail.example.test',
            'username' => 'sari@example.test',
            'password' => 'rahasia-kredensial',
            'notes' => 'Catatan privat',
        ]);

        $response->assertRedirect(route('vault'));
        $credential = Credential::firstOrFail();
        $this->assertSame($user->id, $credential->user_id);
        $this->assertSame('rahasia-kredensial', Crypt::decryptString($credential->password_encrypted));
        $this->assertSame('Catatan privat', Crypt::decryptString($credential->notes_encrypted));
        $this->assertStringNotContainsString('rahasia-kredensial', $credential->password_encrypted);
    }

    public function test_user_can_view_only_their_credential_without_password_plaintext(): void
    {
        $user = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Email Kampus',
            'password_encrypted' => Crypt::encryptString('rahasia-kredensial'),
        ]);

        $response = $this->actingAs($user)->get(route('vault'));

        $response->assertOk()->assertSee('Email Kampus')->assertSee('••••••••');
        $response->assertDontSee('rahasia-kredensial');
        $response->assertDontSee($credential->password_encrypted);
    }

    public function test_user_can_update_credential_and_keep_old_password_when_blank(): void
    {
        $user = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Layanan Lama',
            'password_encrypted' => Crypt::encryptString('password-lama'),
            'notes_encrypted' => Crypt::encryptString('catatan-lama'),
        ]);

        $this->actingAs($user)->put(route('vault.credentials.update', $credential), [
            'title' => 'Layanan Baru',
            'password' => '',
            'notes' => 'catatan-baru',
        ])->assertRedirect(route('vault'));

        $credential->refresh();
        $this->assertSame('password-lama', Crypt::decryptString($credential->password_encrypted));
        $this->assertSame('catatan-baru', Crypt::decryptString($credential->notes_encrypted));
        $this->assertSame('Layanan Baru', $credential->title);
    }

    public function test_edit_form_decrypts_notes_without_prefilling_password(): void
    {
        $user = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Layanan',
            'password_encrypted' => Crypt::encryptString('password-lama'),
            'notes_encrypted' => Crypt::encryptString('catatan privat'),
        ]);

        $response = $this->actingAs($user)->get(route('vault.credentials.edit', $credential));

        $response->assertOk()
            ->assertSee('catatan privat')
            ->assertDontSee('password-lama')
            ->assertDontSee($credential->password_encrypted);
    }

    public function test_new_password_is_encrypted_when_updated(): void
    {
        $user = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Layanan',
            'password_encrypted' => Crypt::encryptString('password-lama'),
        ]);

        $this->actingAs($user)->put(route('vault.credentials.update', $credential), [
            'title' => 'Layanan',
            'password' => 'password-baru',
        ])->assertRedirect(route('vault'));

        $credential->refresh();
        $this->assertSame('password-baru', Crypt::decryptString($credential->password_encrypted));
        $this->assertStringNotContainsString('password-baru', $credential->password_encrypted);
    }

    public function test_user_can_delete_their_credential(): void
    {
        $user = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Layanan',
            'password_encrypted' => Crypt::encryptString('password'),
        ]);

        $this->actingAs($user)->delete(route('vault.credentials.destroy', $credential))
            ->assertRedirect(route('vault'));

        $this->assertDatabaseMissing('credentials', ['id' => $credential->id]);
    }

    public function test_guests_cannot_access_credential_crud_routes(): void
    {
        $credentialRoutes = [
            ['get', route('vault.credentials.create')],
            ['post', route('vault.credentials.store')],
            ['get', route('vault.credentials.edit', 1)],
            ['put', route('vault.credentials.update', 1)],
            ['delete', route('vault.credentials.destroy', 1)],
        ];

        foreach ($credentialRoutes as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('login'));
        }
    }

    public function test_users_cannot_access_another_users_credential(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $credential = $owner->credentials()->create([
            'title' => 'Privat',
            'password_encrypted' => Crypt::encryptString('password'),
        ]);

        $this->actingAs($otherUser)->get(route('vault.credentials.edit', $credential))->assertNotFound();
        $this->actingAs($otherUser)->put(route('vault.credentials.update', $credential), [
            'title' => 'Diubah',
            'password' => 'password-baru',
        ])->assertNotFound();
        $this->actingAs($otherUser)->delete(route('vault.credentials.destroy', $credential))->assertNotFound();

        $this->assertDatabaseHas('credentials', ['id' => $credential->id, 'title' => 'Privat']);
    }

    public function test_invalid_credential_input_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('vault.credentials.create'))
            ->post(route('vault.credentials.store'), [
                'title' => '',
                'website_url' => 'not-a-url',
                'password' => '',
            ])
            ->assertRedirect(route('vault.credentials.create'))
            ->assertSessionHasErrors(['title', 'website_url', 'password']);
    }

    public function test_search_matches_title_username_and_website_for_current_user_only(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->credentials()->createMany([
            ['title' => 'GitHub', 'username' => 'developer@example.test', 'website_url' => 'https://github.com', 'password_encrypted' => Crypt::encryptString('one')],
            ['title' => 'Email', 'username' => 'inbox@example.test', 'website_url' => 'https://mail.example.test', 'password_encrypted' => Crypt::encryptString('two')],
        ]);
        $otherUser->credentials()->create([
            'title' => 'GitHub milik orang lain',
            'username' => 'developer@example.test',
            'password_encrypted' => Crypt::encryptString('three'),
        ]);

        $this->actingAs($user)->get(route('vault', ['search' => 'github']))
            ->assertSee('GitHub')
            ->assertDontSee('Email')
            ->assertDontSee('GitHub milik orang lain');
        $this->actingAs($user)->get(route('vault', ['search' => 'inbox@example.test']))
            ->assertSee('Email');
        $this->actingAs($user)->get(route('vault', ['search' => 'mail.example.test']))
            ->assertSee('Email');
    }

    public function test_all_and_favorites_filters_are_scoped_to_current_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->credentials()->createMany([
            ['title' => 'Favorit Saya', 'is_favorite' => true, 'password_encrypted' => Crypt::encryptString('one')],
            ['title' => 'Bukan Favorit Saya', 'password_encrypted' => Crypt::encryptString('two')],
        ]);
        $otherUser->credentials()->create([
            'title' => 'Favorit Orang Lain',
            'is_favorite' => true,
            'password_encrypted' => Crypt::encryptString('three'),
        ]);

        $this->actingAs($user)->get(route('vault', ['filter' => 'favorites']))
            ->assertSee('Favorit Saya')
            ->assertDontSee('Bukan Favorit Saya')
            ->assertDontSee('Favorit Orang Lain');
        $this->actingAs($user)->get(route('vault', ['filter' => 'all']))
            ->assertSee('Favorit Saya')
            ->assertSee('Bukan Favorit Saya')
            ->assertDontSee('Favorit Orang Lain');
    }

    public function test_user_can_toggle_only_their_favorite(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Layanan',
            'password_encrypted' => Crypt::encryptString('password'),
        ]);
        $otherCredential = $otherUser->credentials()->create([
            'title' => 'Privat',
            'password_encrypted' => Crypt::encryptString('password'),
        ]);

        $this->actingAs($user)->patch(route('vault.credentials.favorite', $credential))
            ->assertOk()
            ->assertJson(['is_favorite' => true]);
        $this->assertDatabaseHas('credentials', ['id' => $credential->id, 'is_favorite' => true]);

        $this->actingAs($user)->patch(route('vault.credentials.favorite', $otherCredential))
            ->assertNotFound();
        $this->assertDatabaseHas('credentials', ['id' => $otherCredential->id, 'is_favorite' => false]);
    }

    public function test_password_is_not_in_initial_html_and_can_be_requested_explicitly(): void
    {
        $user = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Layanan',
            'password_encrypted' => Crypt::encryptString('password-rahasia'),
        ]);

        $this->actingAs($user)->get(route('vault'))
            ->assertDontSee('password-rahasia');

        $response = $this->actingAs($user)->post(route('vault.credentials.password', $credential));

        $response->assertOk()
            ->assertJson(['password' => 'password-rahasia']);
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_password_endpoint_is_protected_and_owner_scoped(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $credential = $owner->credentials()->create([
            'title' => 'Privat',
            'password_encrypted' => Crypt::encryptString('password-rahasia'),
        ]);

        $this->post(route('vault.credentials.password', $credential))->assertRedirect(route('login'));
        $this->actingAs($otherUser)->post(route('vault.credentials.password', $credential))->assertNotFound();
    }
}
