<?php
namespace App\PiplModules\contentpage\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model 
{
	use \Dimsav\Translatable\Translatable;
	
	public $translatedAttributes = ['page_title','page_content','page_seo_title','page_meta_keywords','page_meta_descriptions'];
    
	protected $fillable = ['page_alias','page_status','created_by','page_title','page_content','page_seo_title','page_meta_keywords','page_meta_descriptions'];
	
	protected $hidden = ['created_by'];
	
	
	
}