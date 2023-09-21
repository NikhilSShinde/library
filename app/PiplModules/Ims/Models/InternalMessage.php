<?php
namespace App\PiplModules\Ims\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMessage extends Model 
{

	 protected $fillable = ['subject','content','attachments','conversation_id','sender_id'];
	protected $casts = [
        'attachments' => 'array',
    ];
	public function sender()
	{
		return $this->belongsTo('App\User');
	}
	public function conversation()
	{
		return $this->belongsTo('App\PiplModules\Ims\Models\Conversation');
	}
	
	public function status()
	{
		return $this->hasMany('App\PiplModules\Ims\Models\InternalMessageStatus');	
	}
	
	
	
}