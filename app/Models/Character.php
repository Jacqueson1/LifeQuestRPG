<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $fillable = [
        'name',
        'level',
        'xp',
        'max_hp',
        'hp',
    ];
}
