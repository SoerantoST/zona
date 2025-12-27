<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class ZonaUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'total_transaksi',
        'google_id',
        'two_factor_enabled',
        'two_factor_type'

    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'total_transaksi' => 'float',
    ];

    /* ================= RELATIONS ================= */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function ewallet()
    {
        return $this->hasOne(EWallet::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
