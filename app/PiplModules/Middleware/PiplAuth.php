<?php
namespace App\PiplModules\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;

class PiplAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
		
		// check if user is logged in

		if(!Auth::check())
		{
			return redirect("login");
		}
				
		
        return $next($request);
    }
}
