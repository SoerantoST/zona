<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanHistory extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id','amount_paid','date_paid'];

    public function loan() { return $this->belongsTo(Loan::class); }
}
