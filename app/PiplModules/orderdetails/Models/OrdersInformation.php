<?php

namespace App\PiplModules\orderdetails\Models;

use Illuminate\Database\Eloquent\Model;

class ordersInformation extends Model {

    protected $fillable = ['ride_end_address','ride_end_latitude','ride_end_longitude','ride_final_distance','fuel_amt','pickup_detail_address','dropoff_detail_address','created_at','updated_at','marine_duration','number_of_hours','number_of_person','distance_value','item_description','order_id','selected_pickup_lat', 'selected_pickup_long','pickup_lat','pickup_long','selected_drop_lat','selected_drop_long','drop_lat','drop_long','pickup_area','drop_area','contact_person_for_pickup','contact_person_for_destination','pickup_person_contact_no','destination_person_contact_no','distance','coupon_code','duration'];
      public $timestamps = false;
}
