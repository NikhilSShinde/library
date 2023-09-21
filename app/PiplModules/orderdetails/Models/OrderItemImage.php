<?php

namespace App\PiplModules\orderdetails\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemImage extends Model {

    protected $fillable = ['item_image', 'order_id'];

    public function orderDetails()
    {
            return $this->belongsTo('App\PiplModules\orderdetails\Models\Order');
    }
}
