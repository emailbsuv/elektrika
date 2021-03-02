<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCard extends Model
{
    protected $fillable = [
        'user_id', 'can', 'looking', 'waiting_call'
    ];

//    protected $hidden = [ ];

    protected $casts = [
        'created_at' => 'datetime:U',
        'updated_at' => 'datetime:U',
    ];
}
