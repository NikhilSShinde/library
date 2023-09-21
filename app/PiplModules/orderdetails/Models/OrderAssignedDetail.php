<?php

namespace App\PiplModules\orderdetails\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAssignedDetail extends Model {
    
    protected $fillable = ['order_id','user_id', 'reason_text'];
    public function userDetails()
    {
            return $this->belongsTo('App\User','user_id','id');
    }
}
