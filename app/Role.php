<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
	
	 protected $fillable = ['name'];
	 
	  /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
	
	public function Priviliges()
	{
		
		return $this->hasMany('App\RolePrivilege','role_id','id');
		
	}
}
