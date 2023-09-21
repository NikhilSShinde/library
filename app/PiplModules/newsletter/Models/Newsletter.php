<?php
namespace App\PiplModules\newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Newsletter extends Model 
{
	protected $fillable = array('subject','content','status');
}