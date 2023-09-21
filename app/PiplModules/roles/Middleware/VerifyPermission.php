<?php

namespace App\PiplModules\roles\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\PiplModules\Roles\Exceptions\PermissionDeniedException;
use Auth;
class VerifyPermission
{
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
		
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param int|string $permission
     * @return mixed
     * @throws \App\PiplModules\Roles\Exceptions\PermissionDeniedException
     */
    public function handle($request, Closure $next, $permission)
    {
      
        if ($this->auth->check() &&($this->auth->user()->can($permission) || $this->auth->user()->isSuperadmin()))
         {

             if($this->auth->user()->userInformation->user_status==0)
             {
                 $errorMsg = "We found your account is not yet verified. Kindly see the verification email, sent to your email address, used at the time of registration.";
                  Auth::logout();
                return redirect("/admin/login")->with("login-error",$errorMsg);
             }elseif($this->auth->user()->userInformation->user_status==2)
             {
                 $errorMsg = "We apologies, your account is blocked by administrator. Please contact to administrator for further details.";
                 Auth::logout();
                return redirect("/admin/login")->with("login-error",$errorMsg);
             }else{
                return $next($request);

             }
         }
        if(!$this->auth->check())
            {
		 return redirect("admin/login");   
            }
            else
            {
					
		 return redirect("permission/denied");  
                  abort(403);
            }
    }
}
