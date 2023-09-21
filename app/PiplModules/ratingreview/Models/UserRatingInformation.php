<?php

namespace App\PiplModules\ratingreview\Models;

use Illuminate\Database\Eloquent\Model;

class UserRatingInformation extends Model {

    protected $fillable = ['order_id', 'to_id', 'from_id', 'rating_ques_id', 'rating', 'review', 'review_selected_options', 'status'];

    public function getOrderDetails() {
        return $this->belongsTo("App\PiplModules\orderdetails\Models\Order", 'order_id', 'id');
    }

    public function getFromUserDetails() {
        return $this->belongsTo('App\UserInformation', 'from_id', 'user_id');
    }

    public function getToUserDetails() {
        return $this->belongsTo('App\UserInformation', 'to_id', 'user_id');
    }

}
