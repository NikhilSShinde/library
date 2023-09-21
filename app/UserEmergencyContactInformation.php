<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserEmergencyContactInformation extends Model
{
    //
    protected $fillable = ['id','user_id','person_name','relation','mobile_no','mobile_code','status'];

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
    
}
