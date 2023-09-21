<?php
namespace App\PiplModules\admin\Models;

use Illuminate\Database\Eloquent\Model;

class CountryServices extends Model
{
    //
    protected $fillable = ['id','night_time_from','night_time_to','night_percentage','city_id','price_per_min','flat_price','check_point_distance','sort_index_arabic','sort_index','service_id','country_id','base_km','price_type','base_price','price_per_km'];

    public function country()
    {
            return $this->belongsTo('App\PiplModules\admin\Models\Country');
    }
    public function serviceInformation()
    {
            return $this->belongsTo('App\PiplModules\service\Models\Service','service_id','id');
    }
    
    
    
}
