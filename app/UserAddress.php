<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    
    protected $fillable = ['user_id','address_name','address','address_type','user_country','user_state','user_city','latitude','longitude','zipcode'];

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
