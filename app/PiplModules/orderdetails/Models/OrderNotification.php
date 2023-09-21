<?php

namespace App\PiplModules\orderdetails\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNotification extends Model {
    
    protected $fillable = ['created_at','updated_at','order_id','user_id', 'message'];
    
    public $timestamps = false;
    public function orderDetailsInfo()
    {
        return $this->belongsTo('App\PiplModules\orderdetails\Models\Order','order_id','id');
    }
}
