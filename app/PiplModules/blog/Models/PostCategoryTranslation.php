<?php
namespace App\PiplModules\blog\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategoryTranslation extends Model 
{

	protected $fillable = ['name','parent_id'];
	

	
}