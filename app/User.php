<?php
namespace App;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use App\PiplModules\roles\Traits\HasRoleAndPermission;
use App\PiplModules\roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract,HasRoleAndPermissionContract
{
    use Authenticatable,  CanResetPassword,HasRoleAndPermission;
	
	   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email','username','supervisor_id', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function userInformation()
    {
          return $this->hasOne('App\UserInformation');
    }
    public function userAddress()
    { 
          return $this->hasMany('App\UserAddress');
    }	
    public function driverUserInformation()
    {
          return $this->hasOne('App\DriverUserInformation');
    }	
    
    public function companyInformation()
    {
          return $this->hasOne('App\CompanyInformation');
    }	
     public function userEmergencyContactInformation()
    {
          return $this->hasMany('App\UserEmergencyContactInformation');
    }
     public function UserSpokenLanguageInformation()
    {
          return $this->hasMany('App\UserSpokenLanguageInformation');
    }	
    /**
     * Set the password to be hashed when saved
     */
    public function setPasswordAttribute($password)
    {
            $this->attributes['password'] = \Hash::make($password);
    }
    
     /* for user services */
    public function UserServicesInformation()
    {
          return $this->hasMany('App\UserServiceInformation');
    }	
    
    
    /* for user services */
   
}