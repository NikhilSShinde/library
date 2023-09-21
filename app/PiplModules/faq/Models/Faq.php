<?php
namespace App\PiplModules\faq\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model 
{

	use \Dimsav\Translatable\Translatable;
    
	protected $fillable = ['faq_type','created_by','faq_category_id','question','answer'];
	public $translatedAttributes = ['question','answer'];
	
	
	public function category()
	{
		return $this->hasOne('App\PiplModules\faq\Models\FaqCategory','id','faq_category_id');
	}
		
}