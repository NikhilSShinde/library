<?php
namespace App\Http\Controllers;
use App\User;
use App\UserInformation;
use App\UserAddress;
use App\UserOtpCodes;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\admin\Models\State;
use App\PiplModules\admin\Models\City;
use App\PiplModules\roles\Models\Role;
use App\PiplModules\contentpage\Models\ContentPage;
use App\PiplModules\contentpage\Models\ContentPageTranslation;
use Validator;
use Auth;
use Mail;
use Hash;
use Lang;
use App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\PiplModules\contactrequest\Models\ContactRequestCategory;
use App\PiplModules\contactrequest\Models\ContactRequest;
use GlobalValues;
use Twilio;
class  DlvrallController extends Controller
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
    protected function mateRegistration(Request $request)
    {
        
        $arr_to_return=array();
        //getting all inputs
        $first_name =  isset($request['first_name'])?$request['first_name']:'';
        $last_name =  isset($request['last_name'])?$request['last_name']:'';
        $username =  isset($request['mobile_number'])?$request['mobile_number']:'';
        $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $email = isset($request['email'])?$request['email']:'';
        $password = isset($request['password'])?$request['password']:'';
        $profile_picture = isset($request['profile_picture'])?$request['profile_picture']:'';
        $country = isset($request['country'])?$request['country']:'';
        $region = isset($request['region'])?$request['region']:'';
        $city = isset($request['city'])?$request['city']:'';
        $address = isset($request['address'])?$request['address']:'';
        $latitude = isset($request['latitude'])?$request['latitude']:'';
        $lontitude = isset($request['lontitude'])?$request['lontitude']:'';
        $device_type = isset($request['device_type'])?$request['device_type']:'';
        $device_id = isset($request['device_id'])?$request['device_id']:'';
        $user_type = 3;
        
        //checking if user is not using existing details
        
        $arrUserEmail = User::where("email", $email)->first();
        $arrUserName = User::where("username", $username)->first();
       
        if (count($arrUserName) > 0) {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_already_exist',$locale));
            return response()->json($arr_to_return);
        }
        if (count($arrUserEmail) > 0) {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.email_already_exist',$locale));
             return response()->json($arr_to_return);
        } 
      elseif ($username!='') 
      {
            
        //creating user
        $created_user = User::create([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
        ]);
        
        //entering details in user Information Table.
        $arr_userinformation["profile_picture"] = $profile_picture;
        $arr_userinformation["first_name"] = $first_name;
        $arr_userinformation["last_name"] = $last_name;
        $arr_userinformation["user_mobile"] = $username;
        $arr_userinformation["mobile_code"] = $mobile_code;
        $arr_userinformation["device_id"] = $device_id;
        $arr_userinformation["device_type"] = $device_type;
        $arr_userinformation["user_status"] = 1;
        $arr_userinformation["user_type"] = $user_type;
        $arr_userinformation["user_id"] = $created_user->id;
        $updated_user_info = UserInformation::create($arr_userinformation);
        
        //entering details in user address Table.
        $arr_userAddress["user_id"] = $created_user->id;
        $arr_userAddress["address"] = $address;
        $arr_userAddress["user_country"] = $country;
        $arr_userAddress["user_state"] = $region;
        $arr_userAddress["user_city"] = $city;
        $arr_userAddress["latitude"] = $latitude;
        $arr_userAddress["longitude"] =$lontitude;
       if($latitude!='' && $country!='')
       {
         UserAddress::create($arr_userAddress);
       }
       
        // asign role to respective user		
        $userRole = Role::where("slug","registered.user")->first();

        $created_user->attachRole($userRole);
                
       //sending email for successfull registration
       //getting from global setting
        $site_email=GlobalValues::get('site-email');
        $site_title=GlobalValues::get('site-title');
        $arr_keyword_values = array();
        //Assign values to all macros
        $arr_keyword_values['FIRST_NAME'] = $first_name;
        $arr_keyword_values['LAST_NAME'] = $last_name;
        
        $email_subject=Lang::choice('messages.register_email_subject',$locale);
        $tempate_name="emailtemplate::registration-successfull-".$locale;
        Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($created_user,$email_subject,$site_email,$site_title)  {

            $message->to( $created_user->email)->subject($email_subject)->from($site_email,$site_title);

        });
        if($created_user->id)
        {
              $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.register_successfull',$locale),"data"=>$created_user);
              return response()->json($arr_to_return);
        }
      }
    }
    protected function starRegistration(Request $request)
    {
        
        $arr_to_return=array();
        //getting all inputs
        $first_name =  isset($request['first_name'])?$request['first_name']:'';
        $last_name =  isset($request['last_name'])?$request['last_name']:'';
        $username =  isset($request['mobile_number'])?$request['mobile_number']:'';
        $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $email = isset($request['email'])?$request['email']:'';
        $password = isset($request['password'])?$request['password']:'';
        $profile_picture = isset($request['profile_picture'])?$request['profile_picture']:'';
        $country = isset($request['country'])?$request['country']:'';
        $region = isset($request['region'])?$request['region']:'';
        $city = isset($request['city'])?$request['city']:'';
        $address = isset($request['address'])?$request['address']:'';
        $latitude = isset($request['latitude'])?$request['latitude']:'';
        $lontitude = isset($request['lontitude'])?$request['lontitude']:'';
        $device_type = isset($request['device_type'])?$request['device_type']:'';
        $device_id = isset($request['device_id'])?$request['device_id']:'';
        $user_type = 2;
        
        //checking if user is not using existing details
        
        $arrUserEmail = User::where("email", $email)->first();
        $arrUserName = User::where("username", $username)->first();
       
        if (count($arrUserName) > 0) {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_already_exist',$locale));
            return response()->json($arr_to_return);
        }
        if (count($arrUserEmail) > 0) {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.email_already_exist',$locale));
             return response()->json($arr_to_return);
        } 
      elseif ($username!='') 
      {
            
        //creating user
        $created_user = User::create([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
        ]);
        
        //entering details in user Information Table.
        $arr_userinformation["profile_picture"] = $profile_picture;
        $arr_userinformation["first_name"] = $first_name;
        $arr_userinformation["last_name"] = $last_name;
        $arr_userinformation["user_mobile"] = $username;
        $arr_userinformation["mobile_code"] = $mobile_code;
        $arr_userinformation["device_id"] = $device_id;
        $arr_userinformation["device_type"] = $device_type;
        $arr_userinformation["user_status"] = 0;
        $arr_userinformation["user_type"] = $user_type;
        $arr_userinformation["user_id"] = $created_user->id;
        $updated_user_info = UserInformation::create($arr_userinformation);
        
        //entering details in user address Table.
        $arr_userAddress["user_id"] = $created_user->id;
        $arr_userAddress["address"] = $address;
        $arr_userAddress["user_country"] = $country;
        $arr_userAddress["user_state"] = $region;
        $arr_userAddress["user_city"] = $city;
        $arr_userAddress["latitude"] = $latitude;
        $arr_userAddress["longitude"] =$lontitude;
       if($latitude!='' && $country!='')
       {
        UserAddress::create($arr_userAddress);
       }
       
        // asign role to respective user		
        $userRole = Role::where("slug","registered.user")->first();

        $created_user->attachRole($userRole);
                
       //sending email for successfull registration
       //getting from global setting
        $site_email=GlobalValues::get('site-email');
        $site_title=GlobalValues::get('site-title');
        $arr_keyword_values = array();
        //Assign values to all macros
        $arr_keyword_values['FIRST_NAME'] = $first_name;
        $arr_keyword_values['LAST_NAME'] = $last_name;
        
        $email_subject=Lang::choice('messages.register_email_subject',$locale);
        $tempate_name="emailtemplate::registration-successfull-".$locale;
        Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($created_user,$email_subject,$site_email,$site_title)  {

            $message->to( $created_user->email)->subject($email_subject)->from($site_email,$site_title);

        });
        
        if($created_user->id)
        {
              $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.register_successfull',$locale),"data"=>$created_user);
              return response()->json($arr_to_return);
        }
      }
    }
    
    protected function sendOtpForRegstration(Request $request)
    {
        $arr_to_return=array();
        //getting mobile number
        $rand=  rand(1000, 9999);
        $mobile_no =  isset($request['mobile_no'])?$request['mobile_no']:'';
        $email =  isset($request['email'])?$request['email']:'';
        if($mobile_no!='')
        {
            //checking if email or email is already register.
            $arrUserEmail = User::where("email", $email)->first();
            $arrUserName = User::where("username", $mobile_no)->first();
            $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';            
            $locale =  isset($request['locale'])?$request['locale']:'';
            $mobile_number_to_send="+".$mobile_code."".$mobile_no;
            Twilio::message($mobile_number_to_send, $rand);
            
            //inserting opt code to tabl
            $arr_otp['mobile']=$mobile_no;
            $arr_otp['otp_code']=$rand;
            $arr_otp['status']=1;
            $arr_otp['otp_for']=1;
            UserOtpCodes::create($arr_otp);
           
         //seding email also if emails is provided   
          if($email!='')
          { 
            $site_email=GlobalValues::get('site-email');
            $site_title=GlobalValues::get('site-title');
            $arr_keyword_values = array();
            //Assign values to all macros
            $arr_keyword_values['OTP_CODE'] = $rand;
            $arr_keyword_values['SITE_TITLE'] = $site_title; 
            $email_subject=Lang::choice('messages.otp_sent_email_subject',$locale);
            $tempate_name="emailtemplate::send-otp-".$locale;
            Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($email_subject,$site_email,$site_title)  {

                $message->to($email)->subject($email_subject)->from($site_email,$site_title);

            });
        }
           $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.otp_sent_successfully',$locale));

       }else{
           $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_not_exist',$locale));
       }
       return response()->json($arr_to_return);
   
   }
    
    protected function sendOtpForForgotPassword(Request $request)
    {
        $arr_to_return=array();
        //getting mobile number
        $rand=  rand(1000, 9999);
        $mobile_no =  isset($request['mobile_no'])?$request['mobile_no']:'';
        $email =  isset($request['email'])?$request['email']:'';
        $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';            
        $locale =  isset($request['locale'])?$request['locale']:'';
        $mobile_number_to_send="+".$mobile_code."".$mobile_no;
        if($mobile_no!='')
        {
            //checking if email or email is already register.
            $arrUserEmail = User::where("email", $email)->first();
            $arrUserName = User::where("username", $mobile_no)->first();
            if (count($arrUserName) > 0) {
               
            
           
            Twilio::message($mobile_number_to_send, $rand);
            
            //inserting opt code to tabl
            $arr_otp['mobile']=$mobile_no;
            $arr_otp['otp_code']=$rand;
            $arr_otp['status']=1;
            $arr_otp['otp_for']=0;
            UserOtpCodes::create($arr_otp);
           
         //seding email also if emails is provided   
          if($email!='')
          { 
            $site_email=GlobalValues::get('site-email');
            $site_title=GlobalValues::get('site-title');
            $arr_keyword_values = array();
            //Assign values to all macros
            $arr_keyword_values['OTP_CODE'] = $rand;
            $arr_keyword_values['SITE_TITLE'] = $site_title; 
            $email_subject=Lang::choice('messages.otp_sent_email_subject',$locale);
            $tempate_name="emailtemplate::send-otp-".$locale;
            Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($email_subject,$site_email,$site_title)  {

                $message->to($email)->subject($email_subject)->from($site_email,$site_title);

            });
         }
           $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.otp_sent_successfully',$locale));

         }else{
           $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_not_exist',$locale));
         }
         return response()->json($arr_to_return);
      }
    }
    
    protected function verifyOtp(Request $request)
    {
        
        $arr_to_return=array();
        //getting mobile number
        $mobile_no =  isset($request['mobile_no'])?$request['mobile_no']:'';
        $otp =  isset($request['otp'])?$request['otp']:'';
        $otp_for =  isset($request['otp_for'])?$request['otp_for']:'';
        $locale =  isset($request['locale'])?$request['locale']:'';
        $arrVerifyOtp=UserOtpCodes::where(array("mobile" => $mobile_no,"otp_code" => $otp,"status" => 1,"otp_for" =>$otp_for))->first();

        if(count($arrVerifyOtp)>0)
        {
            $arrVerifyOtp->status=0;
            $arrVerifyOtp->save();
            $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.otp_is_valid',$locale));
        }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.otp_is_not_valid_expired',$locale));
        }
        return response()->json($arr_to_return);
        
    }
    protected function userLogin(Request $request)
    {
        
        $arr_to_return=array();
        //getting mobile number
        $mobile_no =  isset($request['user_name'])?$request['user_name']:'';
        $password =  isset($request['password'])?$request['password']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $arrUserLogin = User::where(array("username" => $mobile_no))->first();
       
        if(count($arrUserLogin)>0)
        {
            if(Hash::check($password, $arrUserLogin->password) == true)
            {
                if($arrUserLogin->userInformation->user_status=='0')
                {
                    $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_is_inactive',$locale));
                }else if($arrUserLogin->userInformation->user_status=='2')
                {
                    $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.user_is_blocked',$locale));
                }else{
                      
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.login_success',$locale),"user" =>$arrUserLogin,"userInformation"=>$arrUserLogin->userInformation,"userAddress"=>$arrUserLogin->userAddress);
                }
                
            }else{
              $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_username_password',$locale));
            }
        }else{
             $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_username_password',$locale));
        }
        return response()->json($arr_to_return);
        
    }
    protected function countryData(Request $request)
    {
        
        //getting mobile number
        $locale =  isset($request['locale'])?$request['locale']:'en';
        
        if($locale!='')
        {
            $all_countries = Country::with('statesInfo')->with('citiesInfo')->translatedIn($locale)->get();
           
            return $all_countries;
        }else{
            return response()->json(array("error_code" => 1));
        }
        
    }
   protected function locationData(Request $request)
    {
         $arr_to_return = array();
        //getting mobile number
        $locale =  isset($request['locale'])?$request['locale']:'en';
        
        if($locale!='')
        {
            $all_countries = Country::translatedIn($locale)->get();
            $all_regions = State::translatedIn($locale)->get();
            $all_cities = City::translatedIn($locale)->get();
            $arr_to_return= array("error_code" => 0, "countries" => $all_countries,"regions" => $all_regions,"cities" => $all_cities);
            
        }else{
            return response()->json(array("error_code" => 1));
        }
      return response()->json($arr_to_return);
    }
    
   protected function resetPassword(Request $request)
    {
        
        $arr_to_return=array();
        //getting mobile number
        $mobile_no =  isset($request['mobile_number'])?$request['mobile_number']:'';
        $otp =  isset($request['otp'])?$request['otp']:'';
        $password =  isset($request['password'])?$request['password']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $arrUserLogin = User::where(array("username" => $mobile_no))->first();
       
        if(count($arrUserLogin)>0)
        {
            if(Hash::check($password, $arrUserLogin->password) == true)
            {
               $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.previous_password_used',$locale)); 
            }else{
                //check for valid otp once and if valid 
                $arrVerifyOtp=UserOtpCodes::where(array("mobile" => $mobile_no,"otp_code" => $otp,"otp_for" =>'0'))->first();
               if(count($arrVerifyOtp)>0)
               {
                $arrUserLogin->password=$password;
                $arrUserLogin->save();
               }
               $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.password_reset_successfully',$locale)); 
            }
            
        }else{
             $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_does_not_exist',$locale));
        }
        return response()->json($arr_to_return);
        
    }
    protected function getContentPages(Request $request)
    {
       
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $page_alias =  isset($request['page_alias'])?$request['page_alias']:'';
        $page_data = ContentPage::where('page_alias',$page_alias)->translatedIn(\App::getLocale())->get();
        $arr_to_return = array("error_code" => 0, "data"=>$page_data);
        return response()->json($arr_to_return);
    }
    protected function getContactUsCategories(Request $request)
    {       
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $all_categories = ContactRequestCategory::translatedIn(\App::getLocale())->get();
        $arr_to_return = array("error_code" => 0, "data"=>$all_categories);
        return response()->json($arr_to_return);
    }
    protected function contactUs(Request $request)
    {       
        $arr_to_return=array();
        $contact_subject =  isset($request['contact_subject'])?$request['contact_subject']:'';
        $contact_message =  isset($request['contact_message'])?$request['contact_message']:'';
        $contact_category_name =  isset($request['category_name'])?$request['category_name']:'';
        $contact_request_category =  isset($request['contact_request_category'])?$request['contact_request_category']:'';
        $contact_name =  isset($request['contact_name'])?$request['contact_name']:'';
        $contact_email =  isset($request['contact_email'])?$request['contact_email']:'';
        $contact_phone =  isset($request['contact_phone'])?$request['contact_phone']:'';
        $locale =  isset($request['locale'])?$request['locale']:'';
       
        if($contact_subject!='')
        {
            
            $reference_no=rand(10000,999999);
            //adding contact request
            $arr_contact=array();
            $arr_contact['contact_subject']=$contact_subject;
            $arr_contact['contact_message']=$contact_message;
            $arr_contact['contact_request_category']=$contact_request_category;
            $arr_contact['contact_name']=$contact_name;
            $arr_contact['contact_email']=$contact_email;
            $arr_contact['contact_phone']=$contact_phone;
            $arr_contact['reference_no']=$reference_no;
            ContactRequest::create($arr_contact);
            
            //sent emaill to admin 
            //sending email for successfull registration
            //getting from global setting
             $site_email=GlobalValues::get('site-email');
             $site_title=GlobalValues::get('site-title');
             $arr_keyword_values = array();
             //Assign values to all macros
             $arr_keyword_values['USER_NAME'] = $contact_name;
             $arr_keyword_values['USER_EMAIL'] = $contact_email;
             $arr_keyword_values['USER_PHONE'] = $contact_phone;
             $arr_keyword_values['CATEGORY'] = $contact_category_name;
             $arr_keyword_values['SUBJECT'] = $contact_subject;
             $arr_keyword_values['MESSAGE'] = $contact_message;
             $arr_keyword_values['REFERENCE'] = $reference_no;
             $arr_keyword_values['SITE_TITLE'] = $site_title;

             $email_subject=Lang::choice('messages.contact_sent_success',$locale);
             $tempate_name="emailtemplate::contact-request";
             Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($email_subject,$site_email,$site_title)  {

                 $message->to($site_email)->subject($email_subject)->from($site_email,$site_title);

             });
        
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.contact_done_successfully',$locale));
        }else{
             $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_contact',$locale));
        }
         return response()->json($arr_to_return);
    }
    
   
}
