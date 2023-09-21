<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    
    protected $fillable = ['document_name','file','user_id'];

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
