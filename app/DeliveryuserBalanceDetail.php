<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class DeliveryuserBalanceDetail extends Model
{
    
    protected $fillable = ['user_id','is_incentive','star_amount','total_amount','pay_type','is_paid','star_payable_amt','type'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    
    public function user()
    {
         return $this->belongsTo('App\User');
    }
    
}
