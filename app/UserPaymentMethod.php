<?php
namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class UserPaymentMethod extends Eloquent
{
   	
	   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $translatedAttributes = ['user_id'];
    protected $fillable = ['id','payment_method_id','status','user_id'];

    
   
}