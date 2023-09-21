<?php
namespace App\PiplModules\contactrequest\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequestReply extends Model 
{

	protected $fillable = ['from_user_id','contact_request_id','reply_subject','reply_message','reply_attachment','reply_email'];
	
	
	 protected $casts = [
        'reply_attachment' => 'array',
    ];
	
}