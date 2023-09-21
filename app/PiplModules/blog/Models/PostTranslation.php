<?php
namespace App\PiplModules\blog\Models;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model 
{

	protected $fillable = ['title','short_description','description','post_image'];
	
}