<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zku extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','vehicle_info','license_number','status'];

    public function user() { return $this->belongsTo(ZonaUser::class); }
    public function deliveries() { return $this->hasMany(Delivery::class, 'driver_id'); }
}
