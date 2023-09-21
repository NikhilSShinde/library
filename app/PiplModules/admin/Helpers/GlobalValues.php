<?php
namespace App\PiplModules\admin\Helpers;

use App\PiplModules\admin\Models\GlobalSetting;
use Cache;
use Carbon\Carbon;
class GlobalValues
{
	//this function is used to retrun all Global setting values.
	public function getAll()
	{
		$glabal_values = GlobalSetting::all();
	}
	
	public static function get($slug)
	{

		if (Cache::has($slug))
		{
			return Cache::get($slug);
		}
		else
		{
				$expiresAt = Carbon::now()->addMinutes(10);
				$setting = GlobalSetting::where('slug',$slug)->get();
		
				if($setting!==null && $setting!="[]")
				{
					$value = $setting->first()->value;
					Cache::put($slug,$value ,$expiresAt);
					return $value;
				}
				else
				{
					return "";
				}
		}
	}
	
	public static function formatDate($dtstr)
	{
			
			if (Cache::has('date-format')) {
					
					$format = Cache::get('date-format');
			}
			else
			{
				
				$expiresAt = Carbon::now()->addMinutes(10);

				$format = GlobalValues::get('date-format');

				Cache::put('date-format',$format,$expiresAt);
				
			}
			
			return date($format,strtotime($dtstr));
	}
}