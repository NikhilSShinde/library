<?php
namespace App\PiplModules\faq\Models;

use Illuminate\Database\Eloquent\Model;

class FaqTranslation extends Model 
{

	protected $fillable = ['question','answer'];
	
}