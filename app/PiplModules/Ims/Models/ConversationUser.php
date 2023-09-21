<?php
namespace App\PiplModules\Ims\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationUser extends Model 
{

	 protected $fillable = ['conversation_id','user_id'];
	 public $timestamps = false;
	
	public function user()
	{
		return $this->belongsTo('App\User');
	}
}