<?php

namespace App\PiplModules\vehicle\Models;

use Illuminate\Database\Eloquent\Model;

class UserVehicleInformation extends Model {

    protected $fillable = ['year_manufacture','financial_type','vehicle_name','user_id','added_by_agent','plate_number','plate_number_image', 'vehicle_desc', 'vehicle_image', 'status'];
   
    public function UserInformation() {
        return $this->belongsTo('App\UserInformation', 'user_id', 'user_id');
    }

}
