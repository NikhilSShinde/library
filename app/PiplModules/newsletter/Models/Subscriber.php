<?php
namespace App\PiplModules\newsletter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Subscriber extends Model 
{
	protected $fillable = array('email');
}