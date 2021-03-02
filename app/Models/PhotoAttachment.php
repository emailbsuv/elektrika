<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoAttachment extends Model
{
    protected $fillable = [
        'order_id', 'file_path',
    ];

    protected $hidden = [
        'order_id',
    ];

    public $timestamps = false;
}
