<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Order
 *
 * @OA\Schema (
 *     schema="Order",
 *     description="Order model",
 *     title="Order model",
 *     required={"name", "photoUrls"},
 *     @OA\Xml(
 *         name="User"
 *     )
 * )
 */
class Order extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'address'
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:U',
        'updated_at' => 'datetime:U',
    ];

    public function fileAttachments()
    {
        return $this->hasMany('App\Models\FileAttachment');
    }

    public function photoAttachments()
    {
        return $this->hasMany('App\Models\PhotoAttachment');
    }

    public function proposals()
    {
        return $this->hasMany('App\Models\Proposal');
    }

    public function proposalsCount()
    {
//        return $this->proposals()
//            ->selectRaw('order_id, count(*) as aggregate')
//            ->groupBy('order_id');
        return $this->hasOne('App\Models\Proposal')
            ->selectRaw('order_id, count(*) as aggregate')
            ->groupBy('order_id');
    }
}
