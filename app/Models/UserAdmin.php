<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAdmin extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Tentukan user bisa mengakses panel tertentu
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($this->role) {
            'administrator' => $panel->getId() === 'admin',
            'operator_user' => $panel->getId() === 'operatoruser',
            'operator_transaksi' => $panel->getId() === 'operatortransaksi',
            'pengaduan' => $panel->getId() === 'pengaduan',
            default => false,
        };
    }
}
