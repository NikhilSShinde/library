<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCreditCard extends Model
{
    
    protected $fillable = ['user_id','name_on_card','card_type','card_no','exp_month','exp_year','is_default','status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    
    public function userInfo()
    {
            return $this->belongsTo('App\User');
    }
 
}
