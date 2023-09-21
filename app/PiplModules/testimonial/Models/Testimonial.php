<?php
namespace App\PiplModules\testimonial\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model 
{
	
	protected $fillable = ['name','status','created_by','user_description','photo','description'];
	
}