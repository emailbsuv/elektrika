<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MyObject
 *
 * @OA\Schema (
 *     schema="MyObject",
 *     description="MyObject model",
 *     title="MyObject model",
 *     required={"name", "photoUrls"},
 *     @OA\Xml(
 *         name="User"
 *     )
 * )
 */
class MyObject extends Model
{
    protected $fillable = [
        'user_id', 'title', 'xml_path',
    ];

    protected $hidden = [
        'user_id', 'xml_path',
    ];

    protected $casts = [
        'created_at' => 'datetime:U',
        'updated_at' => 'datetime:U',
    ];
}
