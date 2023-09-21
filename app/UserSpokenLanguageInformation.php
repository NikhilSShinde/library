<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSpokenLanguageInformation extends Model
{
    //
    protected $fillable = ['user_id','spoken_language_id','status'];

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
    
    public function languageDetails()
    {
          return $this->hasMany('App\PiplModules\admin\Models\SpokenLanguage','id','spoken_language_id');
    }	
    
}
