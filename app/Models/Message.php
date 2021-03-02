<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'dialog_id', 'user_id', 'text'
    ];

    protected $hidden = [
        'dialog_id',
    ];
    protected $casts = [
        'created_at' => 'datetime:U',
    ];
}
