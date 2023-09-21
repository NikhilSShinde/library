<?php
namespace App\PiplModules\admin\Models;
use Illuminate\Database\Eloquent\Model;

class Route extends Model 
{
	
        protected $fillable = ['origin_city_id','destination_city_id'];
	
	public function origincityDetails()
	{
		return $this->belongsTo('App\PiplModules\admin\Models\City','origin_city_id','id');
	}
        public function destinationcityDetails()
	{
		return $this->belongsTo('App\PiplModules\admin\Models\City','destination_city_id','id');
	}

}