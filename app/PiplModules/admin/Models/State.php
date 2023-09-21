<?php
namespace App\PiplModules\admin\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model 
{
    use \Dimsav\Translatable\Translatable;

    public $translatedAttributes = ['name'];
    protected $fillable = ['name','country_id'];
	
    public function country()
    {
            return $this->belongsTo('App\PiplModules\admin\Models\Country');
    }
    public function cityInfo()
    {
        return $this->hasMany('App\PiplModules\admin\Models\City');
    }


}