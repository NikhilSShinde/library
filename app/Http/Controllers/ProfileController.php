<?php
namespace App\Http\Controllers;
use App\User;
use App\UserInformation;
use App\UserAddress;
use App\PiplModules\roles\Models\Role;
use Validator;
use Auth;
use Mail;
use Hash;
use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class  ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/redirect-dashboard';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
      //  $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

     protected function validator(Request $request)
    {
        //only common files if we have multiple registration
        return Validator::make($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'suburb' => 'required',
            'zipcode' => 'required',
			
        ]);
    }
   protected function verifyUserEmail($activation_code)
    {
      $user_informations=UserInformation::where('activation_code', $activation_code)->first();
    
      if(!empty($user_informations) && isset($user_informations->user_status))
      {  
          $successMsg  = "Congratulations! your email has been updated successfully. Please login to continue";
          if($user_informations->temp_email!='' )
          {
            //updating the user status to active
            $user_informations->user->email= $user_informations->temp_email;
            $user_informations->activation_code='';
            $user_informations->temp_email="";
            $user_informations->save();
            $user_informations->user->save();
            Auth::logout();
            if($user_informations->user_type!='1')
            {
                  return redirect("login")->with("register-success",$successMsg);
            }else{
                return redirect("admin/login")->with("register-success",$successMsg);
           
            }
          }else if($user_informations->user_type=='1'||$user_informations->user_type=='3' ||$user_informations->user_type=='2' || $user_informations->user_type=='4' ||$user_informations->user_type=='5' ||$user_informations->user_type=='6' || $user_informations->user_type=='7')
          {
                $user_informations->activation_code='';
               if($user_informations->user_status==0)
               {
                 $user_informations->user_status='1';               
               }
               $user_informations->save();
               
               if($user_informations->user_type!='1')
                {
                      return redirect("login")->with("register-success",$successMsg);
                }else{
                    return redirect("admin/login")->with("register-success",$successMsg);

                }
          }
          else{
            $user_informations->activation_code='';
            $user_informations->save();
            $errorMsg  = "Error! this link has been expired";
            Auth::logout();
            if($user_informations->user_type=='1')
            {
                 return redirect("admin/login")->with("register-success",$successMsg);
            }else{
                return redirect("login")->with("login-error",$errorMsg);
            }
        }
        }else{
            $errorMsg  = "Error! this link has been expired";
            Auth::logout();
            return redirect("login")->with("login-error",$errorMsg);
           
        }
      
    }
    
    protected function show()
    {
        if (Auth::user())
        {
           if(Auth::user()->userInformation->user_type=="8")
            {
                Session::put('support_chat_access', "1");
                return redirect("/admin/support-chat");exit;
            }else  if(Auth::user()->userInformation->user_type=="1")
            {
                Session::put('support_chat_access', "0");
                return redirect("/admin/login");exit;
            }
              $arr_user_data=  Auth::user();
              return view('profile',array("user_info"=>$arr_user_data));	
              
        }else{
            $errorMsg  = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile",$errorMsg);
        }
    }
    
    protected function updateProfile()
    {
        if (Auth::user())
        {
              $arr_user_data=  Auth::user();
              return view('update-profile',array("user_info"=>$arr_user_data));	
              
        }else{
            $errorMsg  = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile",$errorMsg);
        }
    }
    protected function updateProfileInfo(Request $data)
    {
      
        if (Auth::user())
        {
               $arr_user_data=  Auth::user();
               $hasAddress=0;
               // update User Information
		/*
		* Adjusted user specific columns, which may not passed on front end and adjusted with the default values
		*/
               
               
                /** user information goes here ****/
               if(isset($data["profile_picture"]))
               {
                $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
               }
               if(isset($data["gender"]))
               {
                 $arr_user_data->userInformation->gender = $data["gender"];
               }
		if(isset($data["user_birth_date"]))
               {
                  $arr_user_data->userInformation->user_birth_date = $data["user_birth_date"];
               }
               if(isset($data["first_name"]))
               {
                  $arr_user_data->userInformation->first_name = $data["first_name"];
               }
               if(isset($data["last_name"]))
               {
                 $arr_user_data->userInformation->last_name = $data["last_name"];
               }
               if(isset($data["about_me"]))
               {
                 $arr_user_data->userInformation->about_me = $data["about_me"];
               }
		if(isset($data["user_phone"]))
               {
                 $arr_user_data->userInformation->user_phone = $data["user_phone"];
               }
		if(isset($data["user_mobile"]))
               {
                 $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
               }
		$arr_user_data->userInformation->save();
                
                
                /** user addesss informations goes here ****/
                if($data["addressline1"]!='')
                {
                     $arr_user_data->userAddress->addressline1 = $data["addressline1"];
                     $hasAddress=1;
                }
                if($data["addressline2"]!='')
                {
                    $arr_user_data->userAddress->addressline2 = $data["addressline2"];
                     $hasAddress=1;
                }
                if($data["user_country"]!='')
                {
                     $arr_user_data->userAddress->user_country = $data["user_country"];
                      $hasAddress=1;
                }
                  if($data["user_state"]!='')
                {
                     $arr_user_data->userAddress->user_state = $data["user_state"];
                     $hasAddress=1;
                }
                if($data["user_city"]!='')
                {
                     $arr_user_data->userAddress->user_city = $data["user_city"];
                      $hasAddress=1;
                }
                  if($data["suburb"]!='')
                {
                     $arr_user_data->userAddress->suburb = $data["suburb"];
                     $hasAddress=1;
                }
                if($data["user_custom_city"]!='')
                {
                      $arr_user_data->userAddress->user_custom_city = $data["user_custom_city"];
                      $hasAddress=1;
                }
                if($data["zipcode"]!='')
                {
                     $arr_user_data->userAddress->zipcode = $data["zipcode"];
                     $hasAddress=1;
                }
                
              
                if($hasAddress)
                {
                    $arr_user_data->userAddress->save();
                }
                $succes_msg="Your profile has been updated successfully!";
                return redirect("profile")->with("profile-updated",$succes_msg);
              
        }else{
            $errorMsg  = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile",$errorMsg);
        }
    }
    
	
    protected function updateEmail()
    {
        if (Auth::user())
        {
              $arr_user_data=  Auth::user();
              return view('update-email',array("user_info"=>$arr_user_data));	
              
        }else{
            $errorMsg  = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile",$errorMsg);
        }
    }
    
    
    protected function updatePassword()
    {
        if (Auth::user())
        {
              $arr_user_data=  Auth::user();
              return view('update-password',array("user_info"=>$arr_user_data));	
              
        }else{
            $errorMsg  = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile",$errorMsg);
        }
    }
    protected function updateEmailInfo(Request $data)
    {
        $data_values = $data->all();
        if (Auth::user())
        {
                $arr_user_data=  Auth::user();
                $validate_response = Validator::make($data_values, array(
                            'email' => 'required|email|max:500|unique:users,email,'.$arr_user_data->id,
                          
                ));
              
               if($validate_response->fails())
                {
                        return redirect('change-email')
                         ->withErrors($validate_response)
                         ->withInput();
                }else{
                    //updating user email
                    $arr_user_data->email=$data->email;
                    $arr_user_data->save();
                    
                    //updating user status to inactive
                    $arr_user_data->userInformation->user_status=0;
                    $arr_user_data->userInformation->save();
                    //sending email with verification link
                     //sending an email to the user on successfull registration.

                    $arr_keyword_values = array();
                    $activation_code=$this->generateReferenceNumber();
                    //Assign values to all macros
                    $arr_keyword_values['FIRST_NAME'] =   $arr_user_data->userInformation->first_name;
                    $arr_keyword_values['LAST_NAME'] =    $arr_user_data->userInformation->last_name;
                    $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/'.$activation_code);

                    // updating activation code                 
                      $arr_user_data->userInformation->activation_code=$activation_code;
                      $arr_user_data->userInformation->save();   

                    Mail::send('emailtemplate::email-change',$arr_keyword_values, function ($message) use ($arr_user_data)  {

                                    $message->to( $arr_user_data->email)->subject("Email Changed Successfully!");

                    });
                    
                    $successMsg  = "Congratulations! your email has been updated successfully. We have sent email verification email to your email address. Please verify";
                    Auth::logout();
                    return redirect("login")->with("register-success",$successMsg);
              }
              
              
        }else{
            $errorMsg  = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile",$errorMsg);
        }
    }
    protected function updatePasswordInfo(Request $data)
    {
      $current_password=$data->current_password;
        if (Auth::user())
        {
                $arr_user_data=  Auth::user();
                $user_password_chk=Hash::check($current_password, $arr_user_data->password);
                if($user_password_chk)
                {
                        //updating user Password
                        $arr_user_data->password=$data->new_password;
                        $arr_user_data->save();
                        $successMsg  = "Congratulations! your password has been updated successfully.";
                        return redirect("profile")->with("password-update-success",$successMsg);
                }else{
                        $errorMsg  = "Error! Something is wrong going on.";
                        return redirect("change-password")->with("password-update-fail",$errorMsg);
               }
        }else{
                $errorMsg  = "Error! Something is wrong going on.";
                Auth::logout();
                return redirect("login")->with("issue-profile",$errorMsg);
        }
    }
    private function generateReferenceNumber()
   {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0, 0xffff), mt_rand(0, 0xffff),mt_rand(0, 0xffff),mt_rand(0, 0x0fff) | 0x4000,mt_rand(0, 0x3fff) | 0x8000,mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) );
  
   }
   
   //check email duplicate
    protected function chkEmailDuplicate(Request $request)
    {
         $email=$request->email;
         if($email)
         {
             $user_info=User::where('email',$email)->get()->first();
             if($user_info)
             {
                 return "false";
             }else{
                 return "true";
             }
         }
         
    }
   //check current password
    protected function chkCurrentPassword(Request $request)
    {
         $current_password=$request->current_password;
         $user_info=Auth::User();
        
         if($current_password)
         {
           $user_info=Hash::check($current_password, $user_info->password);
             if($user_info)
             {
                 return "true";
             }else{
                 return "false";
             }
         }
         
    }
    
	
}
