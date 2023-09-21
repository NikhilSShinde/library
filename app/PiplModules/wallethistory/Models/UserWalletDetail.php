<?php
namespace App\PiplModules\wallethistory\Models;

use Illuminate\Database\Eloquent\Model;

class UserWalletDetail extends Model 
{
    
	protected $fillable = ['ref_id','transaction_amount','user_id','final_amout','trans_desc','transaction_type'];
        
        public function UserInformation()
	{
		return $this->belongsTo('App\UserInformation','user_id','user_id');
	}
        public function userMainInformation()
	{
		return $this->belongsTo('App\User');
	}
        
}