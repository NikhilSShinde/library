<?php

namespace App\PiplModules\vehicle\Models;

use Illuminate\Database\Eloquent\Model;

class DriverAssignedDetail extends Model {

    protected $fillable = ['vehicle_id','user_id'];
    public function UserInformation() {
        return $this->belongsTo('App\UserInformation', 'user_id', 'user_id');
    }
    public function vehicleInformation() {
        return $this->belongsTo('App\PiplModules\vehicle\Models\UserVehicleInformation', 'vehicle_id', 'id');
    }

}
