<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RolePrivilege extends Model
{
    //
	
	 protected $fillable = ['role_id','privilege_id','read','create','update','delete'];
	 
	  /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
	

	
}
