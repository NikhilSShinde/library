<?php
namespace App\PiplModules\Ims\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model 
{

	 protected $fillable = ['title'];
	
	public function messages()
	{
		return $this->hasMany('App\PiplModules\Ims\Models\InternalMessage');
	}
	
	public function users()
	{
		return $this->hasMany('App\PiplModules\Ims\Models\ConversationUser');
	}
	
	
}