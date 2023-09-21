<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\PiplModules\ResetsPasswords;

class PasswordController extends Controller
{

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
	 
	 use ResetsPasswords;
	
	protected $subject = "";
		 
    public function __construct()
    {
        $this->middleware('guest');
	$this->subject = "My Forgot password subject";
		
		
		
    }
	
	
}
