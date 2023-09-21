<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    
    protected $fillable = ['user_id','order_id','subject','message','notification_date','read_status','type','redirect_flag'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    
}
