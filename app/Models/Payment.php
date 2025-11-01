<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','wallet_transaction_id','status','method'];

    public function order() { return $this->belongsTo(Order::class); }
    public function walletTransaction() { return $this->belongsTo(EWalletTransaction::class, 'wallet_transaction_id'); }
}
