<?php

namespace App\PiplModules\service\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    use \Dimsav\Translatable\Translatable;

    public $translatedAttributes = ['name', 'description'];
    protected $fillable = ['is_sharable','number_of_person_limit','show_number_of_person_limit','pickup_detail_address','dropoff_detail_address','fuel_price_field','required_pick_up_person','required_drop_up_address','number_of_person','required_pick_up_address','required_drop_up_address','required_goods_description','required_goods_image','no_of_hours','name','parent_id', 'category_id', 'basic_fare', 'service_type', 'status', 'min_range', 'max_range'];

    public function categoryInfo() {
        return $this->belongsTo('App\PiplModules\category\Models\Category','category_id','id');
    }
    public function serviceInfo() {
        return $this->belongsTo('App\PiplModules\service\Models\Service','parent_id','id');
    }
    public function getServiceTransDetails() {
         return $this->hasOne('App\PiplModules\service\Models\ServiceTranslation','service_id','id');
    }
}
