<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordWordlist extends Model
{
    protected $table = 'password_wordlist';

    protected $fillable = [
        'password_value',
        'source_name',
    ];
}
