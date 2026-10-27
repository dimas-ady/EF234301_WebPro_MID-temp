<?php

use App\Services\PasswordWordlistService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_wordlist', function (Blueprint $table): void {
            $table->id();
            $passwordColumn = $table->string('password_value', PasswordWordlistService::MAX_ENTRY_LENGTH);

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $passwordColumn->collation('utf8mb4_0900_bin');
            }

            $table->string('source_name')->nullable();
            $table->timestamps();
            $table->unique('password_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_wordlist');
    }
};
