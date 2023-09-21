<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class DriverUserInformation extends Model
{
    //
    
     protected $fillable = ['bank_name','ifsc_code','branch_name','account_number','driver_license_flle','driver_license','geo_fence','id_number','availability','user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    public function user()
    {
            return $this->belongsTo('App\User');
    }
  
   
}
