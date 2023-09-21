<?php

namespace App\PiplModules\admin\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentReceivedDetail extends Model {

    
    protected $fillable = ['user_id','paid_by','bank_name', 'cheque_number', 'transaction_number', 'payment_mode', 'branch_name', 'amount'];

    public function paidUserInfo() {
        return $this->belongsTO('App\User','user_id','id');
    }

}
