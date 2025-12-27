<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    // Tabel ini hanya untuk mencatat (Log), tidak perlu kolom updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'device_name',
        'ip_address',
        'platform',
        'login_at'
    ];

    protected $casts = [
        'login_at' => 'datetime',
    ];

    // Relasi ke User (ZonaUser)
    public function user()
    {
        return $this->belongsTo(ZonaUser::class, 'user_id');
    }
}