<?php

namespace App\PiplModules\admin\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model {

    use \Dimsav\Translatable\Translatable;

    public $translatedAttributes = ['name'];
    protected $fillable = ['name','time_zone','cancellation_charge', 'iso', 'country_code', 'currency_code', 'max_mobile_digit', 'payment_gateway'];

    public function statesInfo() {
        return $this->hasMany('App\PiplModules\admin\Models\State');
    }

    public function allstatesInfo() {
        return $this->hasMany('App\PiplModules\admin\Models\State');
    }

    

    public function countryServices() {
        return $this->hasMany('App\PiplModules\admin\Models\CountryServices');
    }

    public function citiesInfo() {
        return $this->hasManyThrough(City::class, State::class);
    }

}
