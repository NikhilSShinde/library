<?php

namespace App\PiplModules\orderdetails\Models;

use Illuminate\Database\Eloquent\Model;

class UserServiceQuotation extends Model {

    protected $fillable = ['order_id','pickup_location','user_id','qutation_amount','description','status'];

    public function getOrderInformations() {
        return $this->belongsTo('App\PiplModules\orderdetails\Models\Order', 'order_id', 'id');
    }
    public function getUserStarInformation() {
        return $this->belongsTo('App\UserInformation', 'user_id', 'user_id');
    }

   

    

}
