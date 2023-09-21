<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class GeoLimit extends Model
{
    
    protected $fillable = ['location2_long','location2_lat','location1_long','location1_lat','city_id','location1','location2','southwest_lat','northeast_lat','southwest_long','northeast_long'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
	
}
