<?php

namespace App\PiplModules\loan\Models;

use Illuminate\Database\Eloquent\Model;

class LoanEmi extends Model {

    protected $fillable = [
        'loan_id',
        'emi',
        'emi_date',
        'paid',
        'paid_date'
    ];

    public function LoanDetail() {
        return $this->hasOne('App\PiplModules\loan\Model\Loan', 'id', 'loan_id');
    }

}
