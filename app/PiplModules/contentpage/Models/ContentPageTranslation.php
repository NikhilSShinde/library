<?php
namespace App\PiplModules\contentpage\Models;

use Illuminate\Database\Eloquent\Model as Eloquent ;

class ContentPageTranslation extends Eloquent
{

    protected $fillable = array('page_title','page_content','page_seo_title','page_meta_keywords','page_meta_descriptions');

}