<?php

namespace App\PiplModules\orderdetails\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersTransactionStatus extends Model {

    protected $fillable = ['order_id', 'transaction_content','user_id'];

    public function orderDetails()
    {
            return $this->belongsTo('App\PiplModules\orderdetails\Models\Order');
    }
}
