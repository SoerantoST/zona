<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','title','message','type','read_status'
    ];
    protected $casts = [
    'read_status' => 'boolean',
    'created_at' => 'datetime:Y-m-d H:i:s',
];

    public function user() { return $this->belongsTo(ZonaUser::class); }
}
