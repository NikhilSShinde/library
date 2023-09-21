<?php
namespace App\PiplModules\Ims\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMessageStatus extends Model 
{

	 protected $fillable = ['user_id','status','self','internal_message_id'];
	 public $timestamps = false;
}