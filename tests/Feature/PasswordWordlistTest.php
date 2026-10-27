<?php

namespace Tests\Feature;

use App\Models\Credential;
use App\Models\PasswordWordlist;
use App\Models\User;
use App\Services\PasswordWordlistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PasswordWordlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_wordlist_import_streams_entries_and_reports_duplicates_and_blanks(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'vaultify-wordlist-');
        file_put_contents($path, "Password123\r\n\r\nPassword123\npassword123");

        $this->artisan('wordlist:import', ['path' => $path])
            ->expectsOutputToContain('Import complete: 2 added, 1 duplicates, 1 blank lines, 0 skipped.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('password_wordlist', 2);
        $this->assertDatabaseHas('password_wordlist', ['password_value' => 'Password123']);
        $this->assertDatabaseHas('password_wordlist', ['password_value' => 'password123']);

        unlink($path);
    }

    public function test_import_rejects_missing_and_overlong_files_without_exposing_entries(): void
    {
        $this->artisan('wordlist:import', ['path' => '/tmp/vaultify-wordlist-does-not-exist'])
            ->expectsOutput('The wordlist file was not found or cannot be read.')
            ->assertExitCode(1);

        $path = tempnam(sys_get_temp_dir(), 'vaultify-wordlist-');
        $overlong = str_repeat('x', PasswordWordlistService::MAX_ENTRY_LENGTH + 1);
        file_put_contents($path, $overlong);

        $this->artisan('wordlist:import', ['path' => $path])
            ->expectsOutputToContain('1 skipped')
            ->doesntExpectOutputToContain($overlong)
            ->assertExitCode(0);
        $this->assertDatabaseCount('password_wordlist', 0);

        unlink($path);
    }

    public function test_matching_is_exact_and_case_sensitive(): void
    {
        PasswordWordlist::create([
            'password_value' => 'Password123',
            'source_name' => 'test.txt',
        ]);

        $service = app(PasswordWordlistService::class);

        $this->assertTrue($service->matches('Password123'));
        $this->assertFalse($service->matches('password123'));
        $this->assertFalse($service->matches('Password123!'));
        $this->assertFalse($service->matches('Password'));
    }

    public function test_credential_status_is_set_on_create_and_preserved_without_new_password(): void
    {
        PasswordWordlist::create([
            'password_value' => 'Password123',
            'source_name' => 'test.txt',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('vault.credentials.store'), [
            'title' => 'Example',
            'password' => 'Password123',
            'notes' => '',
        ])->assertRedirect(route('vault'));

        $credential = Credential::firstOrFail();
        $checkedAt = $credential->wordlist_checked_at;
        $this->assertTrue($credential->wordlist_match);
        $this->actingAs($user)->get(route('vault'))
            ->assertOk()
            ->assertSee('Found in local wordlist')
            ->assertSee('This check only compares passwords');

        $this->actingAs($user)->put(route('vault.credentials.update', $credential), [
            'title' => 'Example updated',
            'password' => '',
            'notes' => '',
        ])->assertRedirect(route('vault'));

        $credential->refresh();
        $this->assertTrue($credential->wordlist_match);
        $this->assertEquals($checkedAt?->timestamp, $credential->wordlist_checked_at?->timestamp);
        $this->assertSame('Password123', Crypt::decryptString($credential->password_encrypted));
    }

    public function test_update_changes_status_between_match_and_non_match(): void
    {
        PasswordWordlist::create([
            'password_value' => 'Password123',
            'source_name' => 'test.txt',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('vault.credentials.store'), [
            'title' => 'Transition',
            'password' => 'Password123',
        ]);
        $credential = Credential::firstOrFail();
        $this->assertTrue($credential->wordlist_match);

        $this->actingAs($user)->put(route('vault.credentials.update', $credential), [
            'title' => 'Transition',
            'password' => 'NotInWordlist-20261026',
        ]);
        $this->assertFalse($credential->refresh()->wordlist_match);

        $this->actingAs($user)->put(route('vault.credentials.update', $credential), [
            'title' => 'Transition',
            'password' => 'Password123',
        ]);
        $this->assertTrue($credential->refresh()->wordlist_match);
    }

    public function test_empty_wordlist_keeps_new_credential_unchecked(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('vault.credentials.store'), [
            'title' => 'Unchecked',
            'password' => 'NotInWordlist-20261026',
        ]);

        $credential = Credential::firstOrFail();
        $this->assertNull($credential->wordlist_match);
        $this->assertNull($credential->wordlist_checked_at);
    }

    public function test_import_rechecks_existing_credentials_and_skips_invalid_ciphertext(): void
    {
        $user = User::factory()->create();
        $credential = $user->credentials()->create([
            'title' => 'Before import',
            'password_encrypted' => Crypt::encryptString('Password123'),
        ]);

        $invalid = $user->credentials()->create([
            'title' => 'Invalid ciphertext',
            'password_encrypted' => 'not-valid-ciphertext',
        ]);

        $path = tempnam(sys_get_temp_dir(), 'vaultify-wordlist-');
        file_put_contents($path, "Password123\n");

        $this->artisan('wordlist:import', ['path' => $path])
            ->expectsOutputToContain('1 checked')
            ->assertExitCode(0);

        $this->assertTrue($credential->refresh()->wordlist_match);
        $this->assertNull($invalid->refresh()->wordlist_match);
        unlink($path);
    }
}
