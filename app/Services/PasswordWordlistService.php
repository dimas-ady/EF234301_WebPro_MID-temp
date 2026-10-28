<?php

namespace App\Services;

use App\Models\Credential;
use App\Models\PasswordWordlist;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PasswordWordlistService
{
    public const MAX_ENTRY_LENGTH = 255;

    public function isAvailable(): bool
    {
        return PasswordWordlist::query()->exists();
    }

    public function matches(string $password): bool
    {
        $query = PasswordWordlist::query()->where('password_value', $password);

        if (DB::connection()->getDriverName() === 'mysql') {
            $query->whereRaw('BINARY password_value = BINARY ?', [$password]);
        }

        return $query->exists();
    }

    public function check(string $password): ?bool
    {
        return $this->isAvailable() ? $this->matches($password) : null;
    }

    public function refreshCredentials(): array
    {
        $checked = 0;
        $skipped = 0;

        Credential::query()->chunkById(100, function (Collection $credentials) use (&$checked, &$skipped): void {
            foreach ($credentials as $credential) {
                try {
                    $password = Crypt::decryptString($credential->password_encrypted);
                } catch (DecryptException) {
                    $skipped++;

                    continue;
                }

                $credential->forceFill([
                    'wordlist_match' => $this->matches($password),
                    'wordlist_checked_at' => now(),
                ])->save();
                $checked++;
            }
        });

        return compact('checked', 'skipped');
    }
}
