<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyInformation extends Model
{
    //
    protected $fillable = ['name','user_id','description','comp_reg_no'];
    
    public function user()
    {
            return $this->belongsTo('App\User');
    }
}
