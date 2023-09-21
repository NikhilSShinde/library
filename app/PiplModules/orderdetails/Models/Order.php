<?php

namespace App\PiplModules\orderdetails\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $fillable = ['city_id','customer_rating_notify','passengers_count','is_shared_order','extra_time','admin_commission','star_commission','cancelled_by','picked_up_time','locale','created_at','updated_at','is_mate_canceled','is_payment_done','cancelled_date','cancellation_charge','is_cron_execute','additional_amount_desc','additional_amount','payment_completed_by','total_amount', 'country_id', 'order_unique_id', 'order_place_date_time', 'driver_id', 'mate_id', 'service_id', 'order_complete_date_time', 'fare_amount', 'waiting_charge', 'other_charges', 'order_type', 'status', 'status_by_star', 'payment_type'];
    public $timestamps = false;
    public function getUserStarInformation() {
        return $this->belongsTo('App\UserInformation', 'driver_id', 'user_id');
    }

    public function getUserMateInformation() {
        return $this->belongsTo('App\UserInformation', 'mate_id', 'user_id');
    }

    public function getServicesDetails() {
        return $this->belongsTo('App\PiplModules\service\Models\Service', 'service_id', 'id');
    }

    public function getOrderTransInformation() {
        return $this->hasOne('App\PiplModules\orderdetails\Models\OrdersInformation', 'order_id', 'id');
    }

    public function getOrderImages() {
        return $this->hasMany('App\PiplModules\orderdetails\Models\OrderItemImage', 'order_id', 'id');
    }

    public function getOrderCancellations() {
        return $this->hasMany('App\PiplModules\orderdetails\Models\OrderCancelationDetail', 'order_id', 'id');
    }

    public function country() {
        return $this->belongsTo('App\PiplModules\admin\Models\Country', 'country_id', 'id');
    }

}
