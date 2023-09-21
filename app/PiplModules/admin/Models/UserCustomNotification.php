<?php

namespace App\PiplModules\admin\Models;

use Illuminate\Database\Eloquent\Model;

class UserCustomNotification extends Model {

    
    protected $fillable = ['user_id','type','sent_by','title', 'description'];

    public function sentMessageUserInfo() {
        return $this->belongsTO('App\User','user_id','id');
    }

}
