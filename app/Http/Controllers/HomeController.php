<?php
namespace App\Http\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Session;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Lang;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\contentpage\Models\ContentPage;
use App\PiplModules\faq\Models\Faq;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth',['except' => array('changeLocale','index','userLoginSite')]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      echo "We are coming live soon...";die;
         $arr_user_data = array("name" => '', 'email' => '');
            if (Auth::check()) {
                $arr_user_data['name'] = Auth::user()->userInformation->first_name;
                $arr_user_data['email'] = Auth::user()->email;
            }
        //get cms 
        $about_us = ContentPage::where('page_alias','about-us')->first();
        $terms = ContentPage::where('page_alias','terms-of-accession')->first();
        $faqs = Faq::whereIn('faq_type',[0,2])->first();
        return view("welcome",array("terms"=>$terms,"faqs"=>$faqs,"about_us"=>$about_us,'user_data' => $arr_user_data));
    }
    
      protected function userLoginSite(Request $request) {
       
       
        $arrUserCards=array();
        //getting mobile number
        $mobile_no = isset($request['username']) ? $request['username'] : '';
        $password = isset($request['password']) ? $request['password'] : '';
       
        if ($mobile_code != '') {
            $mobile_code = str_replace("+", "", $mobile_code);
        }
       
       $arrUserLoginDetails= UserInformation::where('user_mobile',$mobile_no)->where('user_type',$user_type)->first();
       if(count($arrUserLoginDetails)<=0)
        {
            $mobile_no=ltrim($mobile_no, '0');
            $arrUserLoginDetails = UserInformation::where('user_mobile',$mobile_no)->where('user_type',$user_type)->first();
         
        }
        $login_user_id=isset($arrUserLoginDetails->user_id)?$arrUserLoginDetails->user_id:'0';
        $arrUserLogin = User::where('id',$login_user_id)->first();
        
        if ((count($arrUserLogin) > 0) && ($arrUserLogin->userInformation->user_type == $user_type) && ($arrUserLogin->userInformation->mobile_code == $mobile_code)) {
            if (Hash::check($password, $arrUserLogin->password) == true) {
                if ($arrUserLogin->userInformation->user_status == '0') {
                    $errorMsg="Please check your username or password";
                    return redirect("login")->with("login-error",$errorMsg);
                } else if ($arrUserLogin->userInformation->user_status == '2') {
                    $errorMsg="Your account has been blocked by admin";
                    return redirect("login")->with("login-error",$errorMsg);
                } else {
                    $successMsg="Your have logged in successfully.";
                    return redirect("/")->with("success",$errorMsg);
                }
            } else {
                 $errorMsg="Please check your username or password";
                    return redirect("login")->with("login-error",$errorMsg);
            }
        } else {
              $errorMsg="Please check your username or password";
                    return redirect("login")->with("login-error",$errorMsg);
        }
      
    }
    public function permissionDenied() {

            $arr_user= Auth::user();
            $arr_user_data=$arr_user->userInformation;
            return view('permission_denied',array("user_info"=>$arr_user_data));

    }
    public function changeLocale($locale) {
        
       
         Session::put('language', $locale);
         return "true";
            

    }
		
	/**
	*
	*  Checks, whether user has role of administrator. If yes, then forwards to Admin Panel. If user registered from front end, then checks it's email verified status 
	*  and redirect to error page is not activated. If valid email, then checks for status and forward to respective dashboard.
	*	
	*/
	
	public function toDashboard(Request $request)
	{
		// he is admin, redirect to admin panel
           
		if(Auth::user()->isSuperadmin() || Auth::user()->isAdmin() || Auth::user()->userInformation->user_type=='1' || Auth::user()->userInformation->user_type=='4' || Auth::user()->userInformation->user_type=='5' || Auth::user()->userInformation->user_type=='6')
		{
                   
                    if(Auth::user()->userInformation->user_status=="1")
			{  
                        if(Auth::user()->userInformation->user_type=="8")
                         {
                             Session::put('support_chat_access', "1");
                            return redirect("/admin/support-chat");exit;
                         }
                         if(Auth::user()->userInformation->user_type=="1")
                         {
                             return redirect("admin/dashboard");exit;
                         }else  if(Auth::user()->userInformation->user_type=="4")
                         {
                             return redirect("agent/dashboard");exit;
                         }else  if(Auth::user()->userInformation->user_type=="5")
                         {
                             return redirect("company/dashboard");exit;
                         }
                         else  if(Auth::user()->userInformation->user_type=="6")
                         {
                             return redirect("agent-manager/dashboard");exit;
                         }
                         else  if(Auth::user()->userInformation->user_type=="7")
                         {
                             return redirect("free-toner/dashboard");exit;
                         }
                            
                        }
                        elseif(Auth::user()->userInformation->user_status=="0")
			{
                            $errorMsg  = "We found your account is not yet verified. Kindly see the verification email, sent to your email address, used at the time of registration.";
                        }elseif(Auth::user()->userInformation->user_status=="2")
                        {
                            $errorMsg = "We apologies, your account is blocked by administrator. Please contact to administrator for further details.";
                        }
                        Auth::logout();
			return redirect("/admin/login")->with("login-error",$errorMsg);
		}
		// he is not admin. check whether he has activated, ask him to verify the account, otherwise forward to profile page.
		else
		{
			
			if(Auth::user()->userInformation->user_status=="1")
			{
                           if(Auth::user()->userInformation->user_type=="1")
                         {
                             return redirect("admin/dashboard");exit;
                         }else  if(Auth::user()->userInformation->user_type=="4")
                         {
                             return redirect("agent/dashboard");exit;
                         }else  if(Auth::user()->userInformation->user_type=="5")
                         {
                             return redirect("company/dashboard");exit;
                         }
                         else  if(Auth::user()->userInformation->user_type=="6")
                         {
                             return redirect("agent-manager/dashboard");exit;
                         }else  if(Auth::user()->userInformation->user_type=="7")
                         {
                             return redirect("free-toner/dashboard");exit;
                         }
                            
                         else{
                           return redirect("profile");
                         }
			
			}
			elseif(Auth::user()->userInformation->user_status=="0" || Auth::user()->userInformation->user_status=="2" )
			{
				// some issue with the account activation. Redirect to login page.
				
				$is_register = $request->session()->pull('is_sign_up');	
				if(Auth::user()->userInformation->user_status=="0")
				{
					if($is_register)
					{
						$successMsg  = "Congratulations! your account is successfully created. We have sent email verification email to your email address. Please verify";
						
						Auth::logout();
						return redirect("login")->with("register-success",$successMsg);

					}
					else
					{
						$errorMsg  = "We found your account is not yet verified. Kindly see the verification email, sent to your email address, used at the time of registration.";
					}
				}
				else
				{
					$errorMsg = "We apologies, your account is blocked by administrator. Please contact to administrator for further details.";
				}
				
				Auth::logout();
				
				return redirect("login")->with("login-error",$errorMsg);
			}
			
		}
		
	}
	
	
	
}
