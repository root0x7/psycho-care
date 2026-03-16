<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodScoreDefault extends Model
{
    protected $fillable = ['score', 'locale', 'label', 'description'];

    protected $casts = [
        'score' => 'integer',
    ];
}
