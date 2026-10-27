<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title',
    'website_url',
    'username',
    'password_encrypted',
    'notes_encrypted',
    'is_favorite',
    'wordlist_match',
    'wordlist_checked_at',
])]
#[Hidden(['password_encrypted', 'notes_encrypted'])]
class Credential extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'wordlist_match' => 'boolean',
            'wordlist_checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
