<?php
namespace App\PiplModules\blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model 
{

	use \Dimsav\Translatable\Translatable;
        
	protected $fillable = ['created_by','post_category_id','title','short_description','description','post_url','post_image','post_attachments','allow_comments','seo_description','seo_title','seo_keywords','allow_attachments_in_comments','post_status'];
	public $translatedAttributes = ['short_description','description','title','seo_description','seo_title','seo_keywords'];
	
	protected $casts = [
        'post_attachments' => 'array',
    ];
	
	public function category()
	{
		return $this->hasOne('App\PiplModules\blog\Models\PostCategory','id','post_category_id');
	}
	
	public function comments()
	{
		return $this->hasMany('App\PiplModules\blog\Models\PostComment','post_id','id');	
	}
	
	public function tags()
	{
		return $this->belongsToMany('App\PiplModules\blog\Models\Tag');	
	}
	
	public function scopeSearch(Builder $query, $key=array(), $value='', $locale = null)
    {
		
        return $query->whereHas('translations', function (Builder $query) use ($key, $value, $locale) {
            
			foreach($key as $num=>$keyword)
			{
				if($num<1)
				{
					$query->where($this->getTranslationsTable().'.'.$keyword, 'LIKE', $value);
				}
				else
				{
					$query->orWhere($this->getTranslationsTable().'.'.$keyword, 'LIKE', $value);
				}
			}

			if ($locale) {
                $query->where($this->getTranslationsTable().'.'.$this->getLocaleKey(), 'LIKE', $locale);
            }
			
        })->orWhereHas('tags', function(Builder $query) use ($key, $value, $locale){
			
			$query->where('tags.name', 'LIKE', $value);
			
		});
    }
	
}