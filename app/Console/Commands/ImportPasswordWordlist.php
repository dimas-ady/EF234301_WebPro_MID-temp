<?php

namespace App\Console\Commands;

use App\Services\PasswordWordlistService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPasswordWordlist extends Command
{
    protected $signature = 'wordlist:import {path : Path to the local wordlist file}';

    protected $description = 'Import a local password wordlist and recheck credentials';

    public function handle(PasswordWordlistService $wordlistService): int
    {
        $path = $this->argument('path');

        if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            $this->error('The wordlist file was not found or cannot be read.');

            return self::FAILURE;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $this->error('The wordlist file could not be opened.');

            return self::FAILURE;
        }

        $sourceName = basename($path);
        $inserted = 0;
        $duplicates = 0;
        $blank = 0;
        $skipped = 0;
        $batch = [];

        try {
            while (($line = fgets($handle)) !== false) {
                $entry = rtrim($line, "\r\n");

                if ($entry === '') {
                    $blank++;

                    continue;
                }

                if (mb_strlen($entry, '8bit') > PasswordWordlistService::MAX_ENTRY_LENGTH) {
                    $skipped++;

                    continue;
                }

                if (array_key_exists($entry, $batch)) {
                    $duplicates++;

                    continue;
                }

                $batch[$entry] = [
                    'password_value' => $entry,
                    'source_name' => $sourceName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= 500) {
                    $inserted += $this->insertBatch($batch, $duplicates);
                    $batch = [];
                }
            }

            if (! feof($handle)) {
                $this->error('The wordlist file could not be read to the end.');

                return self::FAILURE;
            }

            $inserted += $this->insertBatch($batch, $duplicates);
        } finally {
            fclose($handle);
        }

        $result = $wordlistService->refreshCredentials();

        $this->info("Import complete: {$inserted} added, {$duplicates} duplicates, {$blank} blank lines, {$skipped} skipped.");
        $this->info("Credential recheck: {$result['checked']} checked, {$result['skipped']} skipped.");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, array<string, mixed>>  $batch
     */
    private function insertBatch(array $batch, int &$duplicates): int
    {
        if ($batch === []) {
            return 0;
        }

        $inserted = DB::table('password_wordlist')->insertOrIgnore(array_values($batch));
        $duplicates += count($batch) - $inserted;

        return $inserted;
    }
}
