<?php
namespace App\PiplModules\slider\Models;
use Illuminate\Database\Eloquent\Model;
class SliderImage extends Model 
{

	protected $fillable = ['title','type','locale','image_path'];
	
	
}