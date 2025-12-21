<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','amount','status','approved_by'];

    public function user() { return $this->belongsTo(ZonaUser::class, 'user_id'); }
    public function approver() { return $this->belongsTo(ZonaUser::class, 'approved_by'); }
    public function histories() { return $this->hasMany(LoanHistory::class); }
}
