<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class UserOtpCodes extends Model
{
    
    protected $fillable = ['mobile','mobile_code','otp_code','status','otp_for'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
	
}
