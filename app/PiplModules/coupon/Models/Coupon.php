<?php

namespace App\PiplModules\coupon\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model {

    protected $fillable = ['title', 'discount', 'country_id', 'start_date', 'end_date', 'usage_time', 'additional_parameter', 'type', 'allow_user_for_multi_use', 'status'];
    
    public function getCountry(){
        return $this->belongsTo('App\PiplModules\admin\Models\Country','country_id','id');
    }

}
