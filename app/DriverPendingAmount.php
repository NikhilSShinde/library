<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverPendingAmount extends Model
{
    
    protected $fillable = ['user_id','amount','status'];
    
    //protected $table = 'driver_pending_amounts';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    
    public function countryinfo()
    {
            return $this->belongsTo('App\PiplModules\admin\Models\Country','user_country');
    }
    public function user()
    {
            return $this->belongsTo('App\User');
    }
    public function stateInfo()
    {
            return $this->belongsTo('App\PiplModules\admin\Models\State','user_state');
    }
    public function cityInfo()
    {
            return $this->belongsTo('App\PiplModules\admin\Models\City','user_city');
    }
}
