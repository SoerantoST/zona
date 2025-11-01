<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonaUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','email','password','role_id','ewallet_id','total_transaksi'
    ];

    public function role() { return $this->belongsTo(Role::class); }
    public function ewallet() { return $this->hasOne(EWallet::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function store() { return $this->hasOne(Store::class); }
    public function loans() { return $this->hasMany(Loan::class); }
    public function notifications() { return $this->hasMany(Notification::class); }
}
