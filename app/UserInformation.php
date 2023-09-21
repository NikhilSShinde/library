<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    
    protected $fillable = ['owner_number','owner_name','civil_id','nationality','working_time','prefer_language','temp_email','profile_picture','mobile_code','gender','activation_code','facebook_id','twitter_id','google_id','linkedin_id','pintrest_id','user_birth_date','first_name','last_name','user_phone','user_mobile','user_status','user_type','is_company','user_id','device_id','device_type'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['facebook_id','twitter_id','google_id','linkedin_id'];
    public function user()
    {
            return $this->belongsTo('App\User');
    }
  
   
	
}
