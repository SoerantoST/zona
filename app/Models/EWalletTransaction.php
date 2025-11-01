<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_id','type','amount','status'];

    public function wallet() { return $this->belongsTo(EWallet::class, 'wallet_id'); }
    public function payment() { return $this->hasOne(Payment::class, 'wallet_transaction_id'); }
}
