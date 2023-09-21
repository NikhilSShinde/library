<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryStatusMsg extends Model
{
    //
    protected $fillable = ['category_id','status_value','status_description','locale','status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    
}
