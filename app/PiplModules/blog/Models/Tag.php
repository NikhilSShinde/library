<?php
namespace App\PiplModules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model 
{
    protected $fillable = array('name','slug');
	
	public function posts()
    {
         return $this->belongsToMany('App\PiplModules\blog\Models\Post');
    }
	
}