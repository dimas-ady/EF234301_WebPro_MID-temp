<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credentials', function (Blueprint $table): void {
            $table->boolean('wordlist_match')->nullable()->after('is_favorite');
            $table->timestamp('wordlist_checked_at')->nullable()->after('wordlist_match');
        });
    }

    public function down(): void
    {
        Schema::table('credentials', function (Blueprint $table): void {
            $table->dropColumn(['wordlist_match', 'wordlist_checked_at']);
        });
    }
};
