<?php
namespace App\PiplModules\blog\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model 
{
	
	protected $fillable = array('commented_by','comment','comment_attachments','post_id');
	
	protected $casts = array(
        'comment_attachments' => 'array',
    );
	
	public function commentUser()
	{
		return $this->belongsTo('App\User','commented_by');
	}
		
}