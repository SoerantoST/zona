<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id','driver_id','pickup_location','dropoff_location','status'
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function driver() { return $this->belongsTo(Zku::class, 'driver_id'); }
}
