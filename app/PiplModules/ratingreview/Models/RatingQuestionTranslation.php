<?php

namespace App\PiplModules\ratingreview\Models;

use Illuminate\Database\Eloquent\Model;

class RatingQuestionTranslation extends Model {

     protected $fillable = ['ques_title', 'rating_ques_id','ques_desc','locale'];

}
