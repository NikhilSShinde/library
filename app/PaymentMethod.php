<?php
namespace App;
use Illuminate\Database\Eloquent\Model as Eloquent ;

class PaymentMethod extends Eloquent
{
   	
	   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use \Dimsav\Translatable\Translatable;
    public $translatedAttributes = ['title'];
    protected $fillable = ['id','status'];

    
   
}