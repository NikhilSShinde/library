<?php
namespace App\PiplModules\category\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model 
{

	use \Dimsav\Translatable\Translatable;
	
	public $translatedAttributes = ['name','description'];
    
	protected $fillable = ['request_range','name','is_drop_location','number_of_person','create_by'];
	
        public function serviceInfo()
	{
		return $this->hasMany('App\PiplModules\service\Models\Service');
	}
        
}