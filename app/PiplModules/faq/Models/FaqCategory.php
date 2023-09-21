<?php
namespace App\PiplModules\faq\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
class FaqCategory extends Model 
{

	  use \Dimsav\Translatable\Translatable, NodeTrait;
	
	  public $useTranslationFallback = true;
	
	  public $translatedAttributes = ['name'];
    
	  protected $fillable = ['name','created_by','parent_id','level','cat_url'];
	
	 public function parentCat()
	 {
		return $this->belongsTo('App\PiplModules\faq\Models\FaqCategory','parent_id','id');
	 }
	
}