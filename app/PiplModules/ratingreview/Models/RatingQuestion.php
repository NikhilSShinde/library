<?php

namespace App\PiplModules\ratingreview\Models;
use Illuminate\Database\Eloquent\Model;

class RatingQuestion extends Model {
    use \Dimsav\Translatable\Translatable;
     public $translatedAttributes = ['ques_title','ques_desc'];
     protected $fillable = ['rating_star_no', 'status'];
    
    public function hasLanguageTranslationsData() {
        return $this->hasMany('App\PiplModules\ratingreview\Models\RatingQuestionTranslation');
   
     }
}
