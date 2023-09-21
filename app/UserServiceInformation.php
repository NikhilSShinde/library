<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserServiceInformation extends Model
{
    //
    protected $fillable = ['user_id','service_id','vehicle_id','goe_fence_area','status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    
    public function user()
    {
            return $this->belongsTo('App\User');
    }
    public function userService()
    {
            return $this->hasOne('App\PiplModules\service\Models\ServiceTranslation','service_id','service_id');
    }
    public function userServiceName()
    {
            return $this->hasOne('App\PiplModules\service\Models\Service','id','service_id');
    }
    public function serviceInfo()
    {
            return $this->hasOne('App\PiplModules\service\Models\Service','id','service_id');
    }
  
    
    
    
}
