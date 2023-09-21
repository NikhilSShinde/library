<?php
namespace App\Http\Controllers;
use App\User;
use App\DriverPendingAmount;
use App\UserInformation;
use App\UserAddress;
use App\DeliveryuserBalanceDetail;
use App\UserEmergencyContactInformation;
use App\UserOtpCodes;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\admin\Models\CountryServices;
use App\PiplModules\ratingreview\Models\UserRatingInformation;
use App\PiplModules\admin\Models\State;
use App\PiplModules\admin\Models\City;
use App\PiplModules\vehicle\Models\DriverAssignedDetail;
use App\PiplModules\roles\Models\Role;
use Validator;
use Auth;
use Mail;
use Hash;
use Lang;
use App;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\PiplModules\contactrequest\Models\ContactRequestCategory;
use App\PiplModules\contactrequest\Models\ContactRequest;
use GlobalValues;
use App\PiplModules\wallethistory\Models\UserWalletDetail;
use App\PiplModules\supporttickets\Models\SupportTicket;
use App\UserSpokenLanguageInformation;
use App\PiplModules\admin\Models\SpokenLanguage;
use App\PiplModules\supporttickets\Models\TicketDescription;
use App\PiplModules\orderdetails\Models\Order;
use App\PiplModules\orderdetails\Models\OrderCancelationDetail;
use App\PiplModules\orderdetails\Models\OrderNotification;
use App\PiplModules\orderdetails\Models\OrderAssignedDetail;
use App\PiplModules\orderdetails\Models\OrdersTransactionStatus;
use App\PiplModules\orderdetails\Models\UserServiceQuotation;
use App\PiplModules\service\Models\Service;
Use App\DriverUserInformation;
use Storage;
use App\PaymentMethod;
use App\UserPaymentMethod;
use App\UserServiceInformation;
use DB;
use App\CategoryStatusMsg;
use App\UserCreditCard;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use DateTimeZone;
use App\PanaceaClasses\SendSms;
use App\PanaceaClasses\SendPushNotification;
use App\PanaceaClasses\AppNotification;
use App\Notification;
class  DeliveryUserController extends Controller
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
    
    /* to show mate user profile */
    public function customerUserProfile(Request $request){
        $arr_to_return=array();
        $mate_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:''; 
        $mate_app_version=GlobalValues::get('mate-app-version');
        $star_app_version=GlobalValues::get('star-app-version');
        $mate_app_ios_version=GlobalValues::get('mate-app-ios-version');
        $star_app_ios_version=GlobalValues::get('star-app-ios-version');
        \App::setLocale($locale);
        $support_chat_id=0;
        if($mate_id!=''){
              
            $arrMateDetails = UserInformation::where('user_id', $mate_id)->where('user_status','1')->where('user_type','3')->first();
            //get support chat user
            $userSupportInfo=UserInformation::where('user_type','8')->first();
            if(count($userSupportInfo)>0)
            {
                 $support_chat_id=$userSupportInfo->user_id;
            }
            if(count($arrMateDetails)<=0)
            {
               $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 4, "msg" => Lang::choice('messages.account_has_deleted_invalid_user', $locale));  
            }else if(count($arrMateDetails)>0 && $arrMateDetails->user_status=='2')
            {
               $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 5, "msg" => Lang::choice('messages.account_has_blocked_invalid_user', $locale));   
            }else{
                $arrMateDetails->profile_picture=asset("/storageasset/user-images/".$arrMateDetails->profile_picture);
                if(count($arrMateDetails)>0)
                {
                     //check for rating_notification
                     $unratedOrderData=Order::where('status',2)->where('mate_id',$mate_id)->where('customer_rating_notify','0')->orderBy('id','desc')->first();
                     $unratedOrder=array();
                     if(count($unratedOrderData)>0)
                     {
                         $unratedOrder['order_id']=$unratedOrderData->id;
                         $unratedOrder['driver_id']=$unratedOrderData->driver_id;
                         $unratedOrder['order_amount']=$unratedOrderData->total_amount;
                         $unratedOrder['customer_id']=$unratedOrderData->mate_id;
                         $unratedOrder['driver_image']=isset($unratedOrderData->getUserStarInformation->profile_picture)?asset("/storageasset/user-images/".$unratedOrderData->getUserStarInformation->profile_picture):'';
                         $unratedOrder['driver_mobile']=isset($unratedOrderData->getUserStarInformation->user_mobile)?$unratedOrderData->getUserStarInformation->user_mobile:'';
                         $unratedOrder['driver_name']=isset($unratedOrderData->getUserStarInformation->first_name)?$unratedOrderData->getUserStarInformation->first_name." ".$unratedOrderData->getUserStarInformation->last_name:'';
                         $unratedOrderData->customer_rating_notify=1;
                         $unratedOrderData->save();
                     }
                     $date_campare=date("Y-m-d 00:00:00",strtotime('-15 Days',strtotime(date('Y-m-d'))));
                     $arrAllNotifications=Notification::where("read_status",0)->where("user_id",$arrMateDetails->user_id)->whereDate('notification_date','>=',$date_campare)->get();                
                     $unreadNotification=count($arrAllNotifications);
                    
                     $arr_to_return = array("unreadNotification"=>$unreadNotification,"unrated_order"=>$unratedOrder,"mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 0, "user" =>$arrMateDetails->user,"user_informations"=>$arrMateDetails);
                    
                    
                }else{
                    $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
                }
            }
            
            
        }else{
            $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    
    public function deliveryuserUserProfile(Request $request){
        $arr_to_return=array();
        $driver_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:''; 
        \App::setLocale($locale);
         $mate_app_version=GlobalValues::get('mate-app-version');
         $star_app_version=GlobalValues::get('star-app-version');
         $mate_app_ios_version=GlobalValues::get('mate-app-ios-version');
        $star_app_ios_version=GlobalValues::get('star-app-ios-version');
          $support_chat_id=0;
        if($driver_id!=''){
            $userSupportInfo=UserInformation::where('user_type','8')->first();
            if(count($userSupportInfo)>0)
            {
                 $support_chat_id=$userSupportInfo->user_id;
            }
            $arrstarDetails = UserInformation::where('user_id', $driver_id)->where('user_status','1')->where('user_type','2')->first();
            if(count($arrstarDetails)<=0)
            {
               $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 4, "msg" => Lang::choice('messages.account_has_deleted_invalid_user', $locale));  
            }else if(count($arrstarDetails)>0 && $arrstarDetails->user_status=='2')
            {
               $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 5, "msg" => Lang::choice('messages.account_has_blocked_invalid_user', $locale));   
            }else{
                if(isset($arrstarDetails->profile_picture))
                {
                    $arrstarDetails->profile_picture=asset("/storageasset/user-images/".$arrstarDetails->profile_picture);
                }
                $nationality_name='';
                if(isset($arrstarDetails->nationality) && $arrstarDetails->nationality!=0)
                {
                    $nationality=  App\Nationality::where('id',$arrstarDetails->nationality)->first();
                    if($locale=='ar')
                    {
                        $nationality_name=$nationality->country_name_arabic;
                    }else{
                          $nationality_name=$nationality->country_name;
                    }
                }
                $arrUserAddress=UserAddress::with("countryinfo")->with("stateInfo")->with("cityInfo")->where("user_id",$driver_id)->first();  
                if(count($arrstarDetails)>0)
                {
                    $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 0,"nationality_name"=>$nationality_name, "user" =>$arrstarDetails->user,"user_informations"=>$arrstarDetails,"addressInformation"=>$arrUserAddress);
                }else{
                    $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 1, "msg" =>Lang::choice('messages.star_profile_not_found',$locale));
                }
        }
        }else{
            $arr_to_return = array("mate_app_ios_version"=>$mate_app_ios_version,"star_app_ios_version"=>$star_app_ios_version,"mate_app_version"=>$mate_app_version,"star_app_version"=>$star_app_version,"support_id"=>$support_chat_id,"error_code" => 1, "msg" =>Lang::choice('messages.star_profile_not_found',$locale));
        }
        return response()->json($arr_to_return);
       
    }
    
    public function updateCustomerUser(Request $request)
    {
        $arr_to_return=array();
        $mate_id =  isset($request['user_id'])?$request['user_id']:'';
		$email =  isset($request['email'])?$request['email']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if(isset($mate_id) && $mate_id>0){
        $arrMateDetails=  User::find($mate_id);
            if (count($arrMateDetails) > 0) {
				
				if (isset($request["email"])) {
                    $arrMateDetails->email = $request["email"];
					$arrMateDetails->save();
                }
				
                if (isset($request["first_name"])) {
                    $arrMateDetails->userInformation->first_name = $request["first_name"];
                }
                if (isset($request["last_name"])) {
                    $arrMateDetails->userInformation->last_name = $request["last_name"];
                }
                if (isset($request["birth_date"])) {
                    $arrMateDetails->userInformation->user_birth_date = $request["birth_date"];
                }
                if (isset($request["profile_picture"])) {
                    $extension = $request->file('profile_picture')->getClientOriginalExtension();
                    if($extension=='')
                    {
                        $extension="png";
                    }
                    $new_file_name = time().".".$extension;
                    Storage::put('public/user-images/'.$new_file_name,file_get_contents($request->file('profile_picture')->getRealPath()));
                    $path = realpath(dirname(__FILE__) . '/../../../');
                    $old_file = $path . '/storage/app/public/user-images/' . $new_file_name;
                    $new_file = $path . '/storage/app/public/user-images/thumbs/' . $new_file_name;
                    $command = "convert " . $old_file . " -resize 300x200^ " . $new_file;
                    exec($command);
                    $arrMateDetails->userInformation->profile_picture = $new_file_name;
                }
                 $arrMateDetails->userInformation->save();
                 $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_profile_update',$locale));
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
            }    
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
        }
        return response()->json($arr_to_return);
        
    }
    
    public function updateDeliveryuserUser(Request $request){
        $arr_to_return = array();
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (isset($driver_id) && $driver_id > 0) {
            $arrStarDetails = User::find($driver_id);
            if (count($arrStarDetails) > 0) {
                if ($request->file('profile_picture') != '') {
                    $extension = $request->file('profile_picture')->getClientOriginalExtension();
                    if ($extension == '') {
                        $extension = "png";
                    }
                    $new_file_name = time() . "." . $extension;
                    Storage::put('public/user-images/' . $new_file_name, file_get_contents($request->file('profile_picture')->getRealPath()));
                    $arrStarDetails->userInformation->profile_picture_temp = $new_file_name;
                }
                $arrStarDetails->userInformation->save();
                //sending email to admin users
                $adminusers = UserInformation::where('user_type', 1)->get();
                $adminusers = $adminusers->reject(function($admin) {
                    return ($admin->user->hasRole('superadmin'));
                });
                $userAddress = UserAddress::where('user_id', $driver_id)->first();
                $country_id = isset($userAddress->user_country) ? $userAddress->user_country : '0';

                if ($country_id > 0) {
                    $adminusers = $adminusers->reject(function($user_details) use ($country_id) {
                                $country = 0;
                                if (isset($user_details->user->userAddress)) {

                                    foreach ($user_details->user->userAddress as $address) {
                                        $country = $address->user_country;
                                    }
                                }
                                if ($country != '17' && $country != 0) {
                                    return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                }
                            })->values();
                }

                $arr_keyword_values = array();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $arr_keyword_values['FIRST_NAME'] = isset($arrStarDetails->userInformation->first_name) ? $arrStarDetails->userInformation->first_name : '';
                $arr_keyword_values['LAST_NAME'] = isset($arrStarDetails->userInformation->last_name) ? $arrStarDetails->userInformation->last_name : '';
                $arr_keyword_values['USER_UPDATE_LINK'] = url('admin/update-star-user/' . $arrStarDetails->id);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                $email_template_title = "emailtemplate::star-profile-picture-update-en";
                $email_template_subject = Lang::choice('messages.profile_picture_for_approval', $locale);
                if (count($adminusers) > 0) {
                    foreach ($adminusers as $admin) {
                        Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin, $site_email, $site_title) {
                            if (isset($admin->user->email)) {
                                $message->to($admin->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                            }
                        });
                    }
                }

                //Send email to star's agent
                $arrUserAddress = UserAddress::where('user_id', $driver_id)->first();
                $countryId = "";
                $stateId = "";
                $cityId = "";
                if (isset($arrUserAddress) && count($arrUserAddress)) {
                    $countryId = $arrUserAddress->user_country;
                    $stateId = $arrUserAddress->user_state;
                    $cityId = $arrUserAddress->user_city;
                }

                $arrAggentUserAddressDetails = UserAddress::where('user_country', $countryId)->where('user_state', $stateId)->where('user_city', $cityId)->where('user_id','<>',$driver_id)->get();
                
                if (isset($arrAggentUserAddressDetails) && count($arrAggentUserAddressDetails)) {
                    foreach ($arrAggentUserAddressDetails as $agent) {
                        $agentInfoDetails = UserInformation::where('user_id', $agent->user_id)->where('user_type','4')->first();
                        if (isset($agentInfoDetails) && count($agentInfoDetails)) {
                            $arr_keyword_values = array();
                            $site_email = GlobalValues::get('site-email');
                            $site_title = GlobalValues::get('site-title');
                            $arr_keyword_values['FIRST_NAME'] = isset($arrStarDetails->userInformation->first_name) ? $arrStarDetails->userInformation->first_name : '';
                            $arr_keyword_values['LAST_NAME'] = isset($arrStarDetails->userInformation->last_name) ? $arrStarDetails->userInformation->last_name : '';
                            $arr_keyword_values['USER_UPDATE_LINK'] = url('admin/update-star-user/' . $arrStarDetails->id);
                            $arr_keyword_values['SITE_TITLE'] = $site_title;
                            $email_template_title = "emailtemplate::star-profile-picture-update-en";
                            $email_template_subject = Lang::choice('messages.profile_picture_for_approval', $locale);
                            Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agentInfoDetails, $site_email, $site_title) {
                                if (isset($agentInfoDetails->user->email)) {
                                    $message->to($agentInfoDetails->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                }
                            });
                        }
                    }
                }

                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.star_profile_update_image', $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.star_profile_not_found', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.star_profile_not_found', $locale));
        }
        return response()->json($arr_to_return);
        
    }
    
      
    public function addCustomerEmergencyContact(Request $request){        
        $arr_to_return=array();
        $mate_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';         
        $person_name =  isset($request['person_name'])?$request['person_name']:'';
        $relation =  isset($request['relation'])?$request['relation']:'';
        $mobile_no =  isset($request['mobile_no'])?$request['mobile_no']:'';
        $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';
        \App::setLocale($locale);
        if(isset($mate_id) && $mate_id>0){ 
            
              $arrUserEmergencyDetails=UserEmergencyContactInformation::where("mobile_no",$mobile_no)->where("user_id",$mate_id)->first();
            if(count($arrUserEmergencyDetails)>0)
            {
                 $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.mate_contact_duplicate',$locale));
            }else{
              $arrUserDetails=UserInformation::where('user_id',$mate_id)->first();
              if(count($arrUserDetails)>0)
              {
                $arr_userContactInformation["user_id"] = $mate_id;
                $arr_userContactInformation["person_name"] = $person_name;
                $arr_userContactInformation["relation"] = $relation;
                $arr_userContactInformation["mobile_no"] = $mobile_no;
                $arr_userContactInformation["mobile_code"] = $mobile_code;
                $arr_userContactInformation["status"] = '1';
                UserEmergencyContactInformation::create($arr_userContactInformation);
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_contact_added',$locale));
              }
              else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
              }
            }
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    public function listCustomerEmergencyContact(Request $request){        
        $arr_to_return=array();
        $mate_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';  
        \App::setLocale($locale);
        if(isset($mate_id) && $mate_id>0){ 
            
              $arrUserEmergencyDetails=UserEmergencyContactInformation::where("user_id",$mate_id)->get();
            
              $arr_to_return = array("error_code" => 0, "data" =>$arrUserEmergencyDetails);
        }
        else{
          $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
        }
         
        return response()->json($arr_to_return);
    }
    
    public function updateCustomerEmergencyContact(Request $request){
        $arr_to_return=array();
        $contact_id =  isset($request['contact_id'])?$request['contact_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';         
        $person_name =  isset($request['person_name'])?$request['person_name']:'';
        $relation =  isset($request['relation'])?$request['relation']:'';
        $mobile_no =  isset($request['mobile_no'])?$request['mobile_no']:'';
        $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';
        $arr_userContactInformation=  UserEmergencyContactInformation::find($contact_id);
        \App::setLocale($locale);
        if(isset($arr_userContactInformation) && count($arr_userContactInformation)>0){
        
                $arr_userContactInformation->person_name = $person_name;
                $arr_userContactInformation->relation = $relation;
                $arr_userContactInformation->mobile_no = $mobile_no;
                $arr_userContactInformation->mobile_code = $mobile_code;
                $arr_userContactInformation->save();
       
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_contact_update',$locale));
            
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_contact_not_fount',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    public function deleteCustomerEmergencyContact(Request $request){
        $contact_id =  isset($request['contact_id'])?$request['contact_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en'; 
        $arr_userContactInformation=  UserEmergencyContactInformation::find($contact_id);
        \App::setLocale($locale);
        if(isset($arr_userContactInformation) && count($arr_userContactInformation)>0){
        
                $arr_userContactInformation->delete();
		
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_contact_delete',$locale));
            
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_contact_not_fount',$locale));
        }
        return response()->json($arr_to_return);
    }   
    
    public function changeCustomerPassword(Request $request){
        $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
        $current_password =  isset($request['current_password'])?$request['current_password']:'';
        $new_password =  isset($request['new_password'])?$request['new_password']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($mate_id>0 && $mate_id !=''){
            $arr_user_data=  User::find($mate_id);
            $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            if($user_password_chk)
            {
                //updating user Password
                $arr_user_data->password=$new_password;
                $arr_user_data->save();
                if(isset($arr_user_data->email))
                {
                    
                 //sending email on password change
                    $arr_keyword_values = array();                   
                    $site_email=GlobalValues::get('site-email');
                    $site_title=GlobalValues::get('site-title');
                    //Assign values to all macros
                    $arr_keyword_values['FIRST_NAME'] =   $arr_user_data->userInformation->first_name;
                    $arr_keyword_values['LAST_NAME'] =    $arr_user_data->userInformation->last_name;
                    $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $arr_keyword_values['PASSWORD'] = $new_password;
                    // updating activation code                 
                    $email_subject = Lang::choice('messages.password_change',$locale);
                    $email_template="emailtemplate::password-change-".$locale;
                  
                        Mail::send($email_template,$arr_keyword_values, function ($message) use ($arr_user_data,$site_email,$site_title,$email_subject)  {
                            
                            $message->to($arr_user_data->email)->subject($email_subject)->from($site_email,$site_title);

                        });
                    
                }
                 $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_change_password',$locale));

            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.current_password_not_match',$locale));

           }
        }else{
            $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.mate_invalid',$locale));
        }   
        return response()->json($arr_to_return);
    }  
    
    public function changeCustomerEmail(Request $request){
        $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
        $new_email =  isset($request['new_email'])?$request['new_email']:'';
        $old_email =  isset($request['old_email'])?$request['old_email']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';            
        $arr_user_data_existing=  User::where('email',$new_email)->where('id','!=',$mate_id)->first(); 
        \App::setLocale($locale);
        if((!isset($arr_user_data_existing->email) || empty($arr_user_data_existing->email))){           
         if($mate_id>0 && $mate_id !=''){
            $arr_user_data=  User::find($mate_id);
           
           if(isset($arr_user_data->email) && ($old_email!='') && ($old_email!=$arr_user_data->email))
           {
                $arr_to_return = array("error_code" => 4, "msg" =>Lang::choice('messages.old_email_does_not_match',$locale));
           }else{
            if(isset($arr_user_data) && $arr_user_data->userInformation->user_type=='3'){
                    $activation_code=$this->generateReferenceNumber();
                    $arr_user_data->userInformation->temp_email=$new_email;
                    $arr_user_data->userInformation->activation_code=$activation_code;                 
                    $arr_user_data->userInformation->save();                    

                    //sending email on email change
                    $arr_keyword_values = array();
                   
                    $site_email=GlobalValues::get('site-email');
                    $site_title=GlobalValues::get('site-title');
                    //Assign values to all macros
                    $arr_keyword_values['FIRST_NAME'] =   $arr_user_data->userInformation->first_name;
                    $arr_keyword_values['LAST_NAME'] =    $arr_user_data->userInformation->last_name;
                    $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/'.$activation_code);
                    $arr_keyword_values['SITE_TITLE'] = $site_title;
                    // updating activation code                 
//                    $arr_user_data->userInformation->activation_code=$activation_code;
//                    $arr_user_data->userInformation->save();   
                    $email_subject = Lang::choice('messages.email_subject_for_change_email',$locale);
                    $email_template="emailtemplate::user-email-change-".$locale;
                    Mail::send($email_template,$arr_keyword_values, function ($message) use ($new_email,$site_email,$site_title,$email_subject)  {

                                    $message->to($new_email)->subject($email_subject)->from($site_email,$site_title);

                    });
                   
                   $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_email_changed',$locale));
            }else{
                $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.mate_not_exist',$locale));
            }        
           }       
        }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_invalid',$locale));
        }
        }else{
            $arr_to_return = array("error_code" => 3, "msg" =>Lang::choice('messages.mate_email_already_exist',$locale));
        }
        return response()->json($arr_to_return);
        
        
    }
    
    protected function sendOtpForChangeMobile(Request $request)
    {
        $arr_to_return=array();
        //getting mobile number
        $rand=  rand(1000, 9999);
        $mobile_no =  isset($request['mobile_no'])?$request['mobile_no']:'';
        $old_mobile_no =  isset($request['old_mobile_no'])?$request['old_mobile_no']:'';
        $old_mobile_code =  isset($request['old_mobile_code'])?$request['old_mobile_code']:'';
        $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';            
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $email="";
        if($mobile_no!='')
        {
            //checking if email or email is already register.
           // $arrUserEmail = User::where("email", $email)->first();
            $arrUserName = User::where("username", $old_mobile_no)->first();
            $arrUserNew = User::where("username", $mobile_no)->first();
            if ((count($arrUserName) > 0) && (isset($arrUserName->userInformation->mobile_code)&& ($arrUserName->userInformation->mobile_code==$old_mobile_code)) ) {
            
                if($arrUserName->userInformation->user_status=='0')
                {
                    $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.user_is_inactive',$locale));
                     return response()->json($arr_to_return);exit;
                }else if($arrUserName->userInformation->user_status=='2')
                {
                    $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.user_is_blocked',$locale));
                    return response()->json($arr_to_return);exit;
                }
             
            if(count($arrUserNew)>0)
            {
                 $arr_to_return = array("error_code" => 4,"otp"=>$rand, "msg" =>Lang::choice('messages.mobile_already_exist',$locale));
            }else{
                if($mobile_code=='')
                {
                   $mobile_code= isset($arrUserName->userInformation->mobile_code)?$arrUserName->userInformation->mobile_code:'91';
                }
                $mobile_code=  str_replace("+","", $mobile_code);
                $mobile_number_to_send="+".$mobile_code."".$mobile_no;
                $message= Lang::choice('messages.otp_for_change_mobile',$locale);
                //$message.="Your otp code is ".$rand;
                 $rand_msg=($rand);
                if($locale=='ar')
               {
                 //  $rand_msg=strrev($rand);
               }
               
                $message.=" " . $rand_msg;
                $obj_sms=new SendSms();
                $obj_sms->sendMessage($mobile_number_to_send,$message); 
                //inserting opt code to tabl
                $arr_otp['mobile']=$mobile_no;
                $arr_otp['otp_code']=$rand;
                $arr_otp['status']=1;
                $arr_otp['otp_for']=2;
                UserOtpCodes::create($arr_otp);
                if($email=='' && isset($arrUserName->email))
                {
                     $email=isset($arrUserName->email)?$arrUserName->email:'';
                }
              
              
         //seding email also if emails is provided   
          if($email!='' && $email!='Null' && $email!=NULL)
           {  
            $site_email=GlobalValues::get('site-email');
            $site_title=GlobalValues::get('site-title');
            $arr_keyword_values = array();
            //Assign values to all macros
            $arr_keyword_values['OTP_CODE'] = $rand;
            $arr_keyword_values['SITE_TITLE'] = $site_title; 
            $email_subject=Lang::choice('messages.otp_sent_email_subject',$locale);
            $tempate_name="emailtemplate::send-otp-".$locale;
            Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($email,$email_subject,$site_email,$site_title)  {

                $message->to($email)->subject($email_subject)->from($site_email,$site_title);

            });
         }
         
         $arr_to_return = array("error_code" => 0,"otp"=>$rand, "msg" =>Lang::choice('messages.otp_sent_successfully',$locale));
       }
         }else{
           $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_not_exist',$locale));
         }
         return response()->json($arr_to_return);
      }
    }
    
    protected function updateCustomerMobile(Request $request)
    {
        $arr_to_return=array();
        //getting mobile number
        $mobile_no =  isset($request['mobile_no'])?$request['mobile_no']:'';
        $mobile_code =  isset($request['mobile_code'])?$request['mobile_code']:'';
        $otp =  isset($request['otp'])?$request['otp']:'';
        $otp_for =  isset($request['otp_for'])?$request['otp_for']:'';
        $locale =  isset($request['locale'])?$request['locale']:'';
        $arrVerifyOtp=UserOtpCodes::where("mobile" , $mobile_no)->where('otp_for',$otp_for)->where('otp_code',$otp)->where("status" , '1')->first();
        \App::setLocale($locale);
        if(count($arrVerifyOtp)>0)
        {
            $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
            $arrMateDetails=  User::find($mate_id);
            $mobile_code=str_replace("+","",$mobile_code);
            if (isset($request["mobile_no"])) 
            {
                $arrMateDetails->username = $mobile_no;
                $arrMateDetails->userInformation->user_mobile = $mobile_no;
                $arrMateDetails->userInformation->mobile_code = $mobile_code;
            }
            $arrMateDetails->userInformation->save();    
            $arrMateDetails->save();    
        }
        
        if(count($arrVerifyOtp)>0)
        {
            $arrVerifyOtp->status=0;
            $arrVerifyOtp->save();
            $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.email_has_been_changed',$locale));
        }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.otp_is_not_valid_expired',$locale));
        }
        return response()->json($arr_to_return);
        
    }
     public function addCustomerWalletBalance(Request $request){
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $wallet_amount =  isset($request['wallet_amount'])?$request['wallet_amount']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $mate_wallet_data = UserWalletDetail::where('user_id',$user_id)->orderBy('id', 'desc')->first(['final_amout']);
        $final_amount=isset($mate_wallet_data->final_amout)?$mate_wallet_data->final_amout:'0';
        $final_amount=(double)($final_amount+$wallet_amount);
        $arrWalletAmt=array();
        $arr_to_return=array();
        $arrWalletAmt['user_id']=$user_id;
        $arrWalletAmt['transaction_amount']=$wallet_amount;
        $arrWalletAmt['final_amout']=$final_amount;
        $arrWalletAmt['trans_desc']=Lang::choice('messages.wallet_recharge',$locale);
        $arrWalletAmt['transaction_type']='0';
        $arrWalletAmt['user_id']=$user_id;
        $mate_wallet_data = UserWalletDetail::create($arrWalletAmt);
       
        if(isset($mate_wallet_data->id))
        {
             $arr_to_return = array("error_code" => 0,"final_amount"=>$final_amount, "msg" =>Lang::choice('messages.wallet_recharge',$locale));
        }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.recharge_fail',$locale));
        }
        return response()->json($arr_to_return);
     }
    public function getCustomerWalletBalance(Request $request){
        $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($mate_id>0 && $mate_id !=''){
            $mate_wallet_data = UserWalletDetail::where('user_id',$mate_id)->orderBy('id', 'desc')->first(['final_amout']);
            if(count($mate_wallet_data)>0)
            {
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_wallet_amount',$locale),"amount"=>$mate_wallet_data->final_amout);
            }else{ 
                 $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_wallet_not_exist',$locale));
            }
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    
    public function getdeliveryuserWalletBalance(Request $request){
        $driver_id =  isset($request['driver_id'])?$request['driver_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($driver_id>0 && $driver_id !=''){
            
            $star_wallet_data = DeliveryuserBalanceDetail::where('is_paid','0')->where('user_id',$driver_id)->orderBy('id', 'desc')->get();
            $incentive_amount_data = DeliveryuserBalanceDetail::where('is_incentive','1')->where('user_id',$driver_id)->orderBy('id', 'desc')->get();
            $total_star_amount=0.00;
            $incentive_amount=0.00;
            if(count($star_wallet_data)>0)
            {
                $total_star_amount=$star_wallet_data->sum('total_amount');
                $star_wallet_total=$star_wallet_data->sum('star_amount');
            }
            if(count($incentive_amount_data)>0)
            {
                $incentive_amount=$incentive_amount_data->sum('star_amount');
            }
            if($total_star_amount=='')
            {
                $total_star_amount='0.00';
                $star_wallet_total='0.00';
            }
            $total_star_amount=sprintf('%0.3f', $total_star_amount);
            $star_wallet_total=sprintf('%0.3f', $star_wallet_total);
            if(count($star_wallet_data)>0)
            {
                $arr_to_return = array("error_code" => 0, "incentive_amount"=>$incentive_amount, "msg" =>Lang::choice('messages.star_wallet_amount',$locale),"amount"=>($star_wallet_total),"total_amount"=>$total_star_amount);
            }else{ 
                 $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.star_wallet_not_exist',$locale));
            }
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.star_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    
    public function getdeliveryuserTransactionHistory(Request $request){
        $driver_id =  isset($request['driver_id'])?$request['driver_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($driver_id>0 && $driver_id !=''){
            $star_wallet_data = UserWalletDetail::where('user_id',$driver_id)->orderBy('created_at', 'desc')->get();
            if(count($star_wallet_data)>0)
            {
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.star_wallet_amount',$locale),"data"=>$star_wallet_data);
            }else{ 
                 $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.star_wallet_history_not_exist',$locale));
            }
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.star_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    public function getCustomerTransactionHistory(Request $request){
         $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
         $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($mate_id>0 && $mate_id !=''){
            $mate_wallet_data = UserWalletDetail::where('user_id',$mate_id)->orderBy('id', 'created_at')->get();
            if(count($mate_wallet_data)>0)
            {
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.mate_wallet_amount',$locale),"data"=>$mate_wallet_data);
            }else{ 
                 $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_wallet_history_not_exist',$locale));
            }
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }

    public function getUserSupportTicketList(Request $request){
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $country_id =  isset($request['country_id'])?$request['country_id']:'en';
        \App::setLocale($locale);
        $support_chat_id=0;
        if($user_id>0 && $user_id !=''){
            $ticketData=array();
            $user_all_SupportTicket = SupportTicket::where('added_by',$user_id)->with('orderInformation')->get();
            if(count($user_all_SupportTicket)>0)
            {
                $i=0;
                foreach($user_all_SupportTicket as $ticket)
                {
                   $ticketData[$i]=$ticket;
                   $ticket_coversation=TicketDescription::where('is_read','0')->where('ticket_id',$ticket->id)->get();
                   $ticketData[$i]['unread_count']=count($ticket_coversation);
                }
            }
            
            //get support chat user
            $userInfo=UserInformation::where('user_type','8')->first();
            if(count($userInfo)>0)
            {
                 $support_chat_id=$userInfo->user_id;
            }
            if(count($user_all_SupportTicket)>0)
            {
                $arr_to_return = array("support_id"=>$support_chat_id,"error_code" => 0,  "msg" =>Lang::choice('messages.user_ticket_list',$locale),"data"=>$user_all_SupportTicket);
            }else{ 
                 $arr_to_return = array("support_id"=>$support_chat_id,"error_code" => 1, "msg" =>Lang::choice('messages.user_ticket_not_exist',$locale));
            }
        }else{
            $arr_to_return = array("support_id"=>$support_chat_id,"error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
              
    public function addUserSupportTicket(Request $request){
         
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $order_id =  isset($request['order_id'])?$request['order_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $subject =  isset($request['subject'])?$request['subject']:'';
        $description =  isset($request['description'])?$request['description']:'';
        $country_id =  isset($request['country_id'])?$request['country_id']:'0';
        \App::setLocale($locale);
        if($user_id>0 && $user_id !='' && $description!=''){
            $new_file_name="";
            $arr_userTicketInformation = array();
            $arr_userTicketInformation["added_by"] = $user_id;
            $arr_userTicketInformation["order_id"] = $order_id;
            $arr_userTicketInformation["support_subject"] = $subject;
            $arr_userTicketInformation["ticket_unique_id"] = rand();
            //$arr_userTicketInformation["is_read"] = '1';
            $arr_userTicketInformation = SupportTicket::create($arr_userTicketInformation);
            $last_ticket_id = $arr_userTicketInformation->id;   
            
            $arr_userTicketDescription =  array();
            $arr_userTicketDescription["ticket_id"] = $last_ticket_id;
            $arr_userTicketDescription["posted_by"] = $user_id;
            $arr_userTicketDescription["description"] = $description;
            $arr_userTicketDescription["is_read"] = '1';
            TicketDescription::create($arr_userTicketDescription);
            $attached_file = $request->file('attached_file');
            if($attached_file)
            {
                $extension = $attached_file->getClientOriginalExtension();
                if ($extension == '') {
                    $extension = "png";
                }
                $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                Storage::put('public/suport-files/' . $new_file_name, file_get_contents($attached_file->getRealPath()));
            }
            
            //sendimg emails to admin
            $siteAdmin = GlobalValues::get('site-email');
            $adminusers = UserInformation::where('user_type', 1)->get();
           if($country_id>0)
           {
             $adminusers = $adminusers->reject(function($user_details) use ($country_id) {
                $country = 0;
                if (isset($user_details->user->userAddress)) {

                    foreach ($user_details->user->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                if ($country && $country != 0) {
                    return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                }
                 if($user_details->user->hasRole('superadmin'))
                    {
                        return true;
                    }
            });
           }
            $user_info=  UserInformation::where('user_id',$user_id)->first();
            $site_email = GlobalValues::get('site-email');
            $site_title = GlobalValues::get('site-title');
            //Assign values to all macros
            $arr_keyword_values['FIRST_NAME'] = $user_info->first_name;
            $arr_keyword_values['LAST_NAME'] = $user_info->last_name;
            $arr_keyword_values['SUBJECT'] = $subject;
            $arr_keyword_values['DESCRIPTION'] = $description;
            $arr_keyword_values['ORDER_ID'] = $order_id;
            $arr_keyword_values['SITE_TITLE'] = $site_title;
            $email_template_title = "emailtemplate::new-ticket-added-" . $locale;
            $email_template_subject = Lang::choice('messages.new_ticket_opened', $locale);
            if (count($adminusers) > 0) {
                foreach ($adminusers as $admin) {

                    Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin, $site_email, $site_title) {
                        if (isset($admin->user->email)) {
                            $message->to($admin->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                        }
                    });
                }
            }
             //sending email to site admin
            Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title) {
                if (isset($site_email)) {
                    $message->to($site_email)->subject($email_template_subject)->from($site_email, $site_title);
                }
            });

            $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.user_ticket_aded',$locale));
           
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
            
    
      public function getTicketHistory(Request $request){
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $ticket_id =  isset($request['ticket_id'])?$request['ticket_id']:'';     
        \App::setLocale($locale);
        $is_closed=0;
        if($ticket_id !=''){
          $arrUserTicketDescription=TicketDescription::where('ticket_id',$ticket_id)->get();
          if(count($arrUserTicketDescription)>0)
          {
              foreach($arrUserTicketDescription as $userTicketDesc)
              {
                  $userTicketDesc->is_read=1;
                  $userTicketDesc->save();
              }
          }
           //cloase if $is_closed is 1
           $support_ticket=SupportTicket::where('id',$ticket_id)->first();
           if(count($support_ticket)>0)
           {
               if($support_ticket->status==2)
               {
                   $is_closed=1;
               }
           }
            $arr_to_return = array("error_code" => 0,"is_closed"=>$is_closed,"data"=>$arrUserTicketDescription, "msg" =>Lang::choice('messages.user_ticket_comment_aded',$locale));
           
        }else{
            $arr_to_return = array("error_code" => 1,"is_closed"=>$is_closed, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
    public function postCommentOnTicket(Request $request){
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $is_closed =  isset($request['is_closed'])?$request['is_closed']:'0';
        $description =  isset($request['description'])?$request['description']:'';
        $ticket_id =  isset($request['ticket_id'])?$request['ticket_id']:'';     
        \App::setLocale($locale);
        if($user_id>0 && $user_id !='' && $ticket_id !=''){
            $arr_userTicketDescription =  array();
            $arr_userTicketDescription["ticket_id"] = $ticket_id;
            $arr_userTicketDescription["posted_by"] = $user_id;
            $arr_userTicketDescription["description"] = $description;
            TicketDescription::create($arr_userTicketDescription);  
            //cloase if $is_closed is 1
            $support_ticket=SupportTicket::where('id',$ticket_id)->first();
            
            if($is_closed==1)
            {  
                
                $support_ticket->status=2;
                $support_ticket->save();
            }else{
                if($support_ticket->status==0)
                {
                    $support_ticket->status=1;
                     $support_ticket->save();
                }
                 
            }
            $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.user_ticket_comment_aded',$locale));
           
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }

    public function getUserSpokenLanguages(Request $request){
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
         \App::setLocale($locale);
        if($user_id>0 && $user_id !=''){
           
            $user_all_Spoken_laguages = UserSpokenlanguageinformation::where('user_id',$user_id)->with('languageDetails')->get();
            if(count($user_all_Spoken_laguages)>0 && isset($user_all_Spoken_laguages)){
                $arr_to_return = array("error_code" => 0, "data" =>$user_all_Spoken_laguages);
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.spoken_language_not_found',$locale));
            }
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }    
        return response()->json($arr_to_return);
    }
    
    public function getSpokenLanguages(Request $request){
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $user_all_Spoken_laguages = SpokenLanguage::translatedIn(\App::getLocale())->get();
        if(count($user_all_Spoken_laguages)>0 && isset($user_all_Spoken_laguages)){
            
            $arr_to_return = array("error_code" => 0, "data" =>$user_all_Spoken_laguages);
            
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.language_not_found',$locale));
        }    
        return response()->json($arr_to_return);
    }
    
    public function addUserSpokenLanguages(Request $request){
         
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $languages =  isset($request['languages'])?$request['languages']:'0';
        $language_ids = json_decode($languages);        
        \App::setLocale($locale);
        if($user_id>0 && $user_id !=''){
        UserSpokenlanguageinformation::where('user_id',$user_id)->delete();
            for($k=0;$k<count($language_ids);$k++){
                    $arr_spoken_languages = array();
                    $arr_spoken_languages["spoken_language_id"] = $language_ids[$k];
                    $arr_spoken_languages["user_id"] = $user_id;
                    UserSpokenlanguageinformation::create($arr_spoken_languages);
                }
            $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.preferred_language',$locale));    
                 
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
    }
    
    public function sendSosSMS(Request $request){
        $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $current_lat =  isset($request['current_lat'])?$request['current_lat']:'';
        $current_long =  isset($request['current_long'])?$request['current_long']:'';
        \App::setLocale($locale);
        if($mate_id>0 && $mate_id !=''){
        $arr_user_data=  User::find($mate_id);
        $userEmergencyContacts=UserEmergencyContactInformation::where('user_id',$mate_id)->get();
        if(count($userEmergencyContacts)>0){
            foreach($userEmergencyContacts as $contact) {
               {
                    $location_link="http://www.google.com/maps/place/".$current_lat.",".$current_long;
                    $msg_emergeny=Lang::choice('messages.emergency_msg',$locale);
                    $message=$arr_user_data->userInformation->first_name."  ".$arr_user_data->userInformation->last_name." ".$msg_emergeny."     ".$location_link;
                    $mobile_code=str_replace("+","",$contact->mobile_code);                   
                    $mobile_no=$contact->mobile_no;                   
                    $mobile_no="+".$mobile_code."".$mobile_no;
                    $obj_sms=new SendSms();
                    $res=$obj_sms->sendMessage($mobile_no,$message); 
               
                    if($res){
                        $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.send_sos_success',$locale));
                    }else{
                        $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.sms_sent_fail',$locale));
                    }
                }
                 
            }}
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.send_sos_not_exist',$locale));
            }
        return response()->json($arr_to_return);
    }
     public function getCustomerUserActiveOrder(Request $request){
        $user_id = isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
       
        $arrOrderDetails=array();  
        if($user_id>0 && $user_id !=''){
            $arr_orders = Order::where('mate_id',$user_id)->where('status',1)->orderBy('id','desc')->first();
            $city_id=isset($arr_orders->city_id)?$arr_orders->city_id:'0';
            $cityInfo=City::where('id',$arr_orders->city_id)->first();
          if(isset($arr_orders) && count($arr_orders)>0){
            //storing all order details
            $star_user_details=UserInformation::where('user_id',$arr_orders->driver_id)->first();
            $staruserDetails['order_id']=$arr_orders->id;
            $staruserDetails['status_by_star']=$arr_orders->status_by_star;
            $staruserDetails['order_details']=$arr_orders->getOrderTransInformation;
            $staruserDetails['star_first_name']=$star_user_details->first_name;
            $staruserDetails['star_last_name']=$star_user_details->last_name;
            $staruserDetails['star_mobile']="+".str_replace("+","",$star_user_details->mobile_code)."".$star_user_details->user_mobile;
            if (isset($star_user_details->profile_picture)) {                         
             $staruserDetails['star_image']=asset("/storageasset/user-images/" . $star_user_details->profile_picture);
            }else{
                $staruserDetails['star_image']="";
            }
            if(isset($cityInfo->support_number))
            {
                $staruserDetails['support_number']=$cityInfo->support_number;
            }else{
                 $staruserDetails['support_number']='';
            }
           $userAddress = UserAddress::where('user_id',$arr_orders->driver_id)->first();  
           if(count($userAddress)>0)
           {
             $staruserDetails['star_current_lat']=$userAddress->user_current_latitude;
             $staruserDetails['star_current_long']=$userAddress->user_current_longtitude;
           }else{
             $staruserDetails['star_current_lat']=0;
             $staruserDetails['star_current_long']=0;  
           }
            //getting avarage rating
            $userRating=UserRatingInformation::where('to_id',$arr_orders->driver_id)->where('status','1')->avg('rating');
            $staruserDetails['star_rating']=$userRating;
            
            //getting vehicle inforamtion
            $userDriverDetails=DriverAssignedDetail::where('user_id',$arr_orders->driver_id)->first();
            $vehicleDetails="";
            if(count($userDriverDetails)>0)
            {
                $vehcile_make=isset($userDriverDetails->vehicleInformation->vehicle_name)?$userDriverDetails->vehicleInformation->vehicle_name:'';
                $vehicleDetails=$vehcile_make."- ".isset($userDriverDetails->vehicleInformation->plate_number)?$userDriverDetails->vehicleInformation->plate_number:'';
            }
            $staruserDetails['star_vehicle']=$vehicleDetails;
            $staruserDetails['vehicle_name']=isset($userDriverDetails->vehicleInformation->vehicle_desc)?$userDriverDetails->vehicleInformation->vehicle_desc:'';
            if (isset($userDriverDetails->vehicleInformation->vehicle_image) && $userDriverDetails->vehicleInformation->vehicle_image!='') {                         
             $staruserDetails['vehicle_image']=asset("/storageasset/vehicle-images/" . $userDriverDetails->vehicleInformation->vehicle_image);
            }else{
                $staruserDetails['vehicle_image']="";
            }
            $staruserDetails['fare_amount']=isset($arr_orders->fare_amount)?$arr_orders->fare_amount:'';
            $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_listing_success',$locale),"order_details"=>$staruserDetails);
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.current_orders_not_exist',$locale));
            }
        }else{
                $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.mate_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
              
    public function getCustomerUserCurrentOrder(Request $request){
        $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $arrOrderDetails=array();  
        if($mate_id>0 && $mate_id !=''){
            $arr_orders = Order::where('mate_id',$mate_id)->whereIn('status',[1,0])->get();
            $arr_orders=$arr_orders->sortByDesc('created_at');
            if(isset($arr_orders) && count($arr_orders)>0){
                //storing all order details
                $i=0;
                foreach($arr_orders as $order)
                {
                    $arrOrderDetails[$i]['order']=$order;
                    // getting order details.
                    $arrOrderDetails[$i]['order_details']=$order->getOrderTransInformation;
                    $arrOrderDetails[$i]['service']=$order->getServicesDetails->name;
                    if(isset($order->getServicesDetails))
                    {
                        $service=$order->getServicesDetails;
                        $arrOrderDetails[$i]['category']=$service->categoryInfo->name;
                        //get status by star text
                        $catgeoryMsgDetails=CategoryStatusMsg::where('category_id',$service->categoryInfo->id)->where('status_value',$order->status_by_star)->first();
                        $arrOrderDetails[$i]['status_by_star_text']=isset($catgeoryMsgDetails->status_description)?$catgeoryMsgDetails->status_description:'';
                    }
                    $arrOrderDetails[$i]['star_first_name']="";
                    $arrOrderDetails[$i]['star_last_name']="";
                    if(isset($order->driver_id))
                    {
                        $star_user_details=UserInformation::where('user_id',$order->driver_id)->first();
                        $arrOrderDetails[$i]['star_first_name']=$star_user_details->first_name;
                        $arrOrderDetails[$i]['star_last_name']=$star_user_details->last_name;
                        $arrOrderDetails[$i]['star_mobile']="+".str_replace("+","",$star_user_details->mobile_code)."".$star_user_details->user_mobile;
                        if (isset($star_user_details->profile_picture)) {                         
                         $arrOrderDetails[$i]['star_image']=asset("/storageasset/user-images/" . $star_user_details->profile_picture);
                        }else{
                            $arrOrderDetails[$i]['star_image']="";
                        }
                       // $arrOrderDetails[$i]['star_image']=$star_user_details->user_mobile;
                    }
                   $i++;
                }
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_listing_success',$locale),"order_details"=>$arrOrderDetails);
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.current_orders_not_exist',$locale));
            }
        }else{
                $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.mate_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
    public function getChatUsers(Request $request){
        $user_id =  isset($request['user_id'])?$request['user_id']:'0';
        $type =  isset($request['type'])?$request['type']:'0';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $chat_default_time = GlobalValues::get('chat_default_time');
        \App::setLocale($locale);
        $chatUsers=array();  
        $chatUsersIds=array();  
        
        if($user_id>0){
           $total_count=0;
           if($type==0)
           {
              
                 $current_orders = Order::where('mate_id',$user_id)->whereIn('status',[1,2])->get();
                 if(count($current_orders)>0)
                 {
                     $i=0;
                     foreach($current_orders as $orders)
                     {
                        if($orders->status==1)
                        {
                           // $chatUsers[$i]['user_info']=$orders->driver_id;
                            
                            //get User Information
                            
                            $userInfo=UserInformation::where('user_id',$orders->driver_id)->first(['user_id', 'first_name','last_name','profile_picture']);
                         if(!(in_array($orders->driver_id,$chatUsersIds)))
                         {
                            $chatUsers[$i]['user_info']=$userInfo;
                            $i++;
                            $chatUsersIds[]=$orders->driver_id;
                         }
                        }else if($orders->status==2 && $orders->order_complete_date_time!='0000-00-00 00:00:00')
                        {
                             $dt = new DateTime(date('Y-m-d H:i:s'));
          
                                //get timezone as per country
                              $countryInfo=Country::where('id',$orders->country_id)->first();
                             if(count($countryInfo)>0)
                             {
                                 $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                 $dt->setTimezone($tz);
                             }
                             $date2_val= $dt->format('Y-m-d H:i:s'); 
                            $date2=new DateTime($date2_val);
                              $date1=new DateTime($orders->order_complete_date_time);
                             $diffdate=date_diff($date1,$date2);
                             
                            if(($diffdate->i<$chat_default_time) && (($diffdate->h)==0) )
                            {
                                if(!(in_array($orders->driver_id,$chatUsersIds)))
                                {
                                    
                                   $userInfo=UserInformation::where('user_id',$orders->driver_id)->first(['user_id', 'first_name','last_name','profile_picture']);
                                    $chatUsers[$i]['user_info']=$userInfo;
                                    $i++;
                                     $chatUsersIds[]=$orders->driver_id;
                                }

                            }
                        }
                     }
                 }
                 $arr_to_return=array("error_code"=>0,"count"=>count($chatUsers),"users"=>$chatUsers);
            } else{
                 $current_orders = Order::where('driver_id',$user_id)->whereIn('status',[1,2])->get();
                 if(count($current_orders)>0)
                 {
                      $i=0;
                     foreach($current_orders as $orders)
                     {
                        if($orders->status==1)
                        {
                            if(!(in_array($orders->mate_id,$chatUsersIds)))
                          {
                            $userInfo=UserInformation::where('user_id',$orders->mate_id)->first();
                            $chatUsers[$i]['user_info']=$userInfo;
                            $i++;
                            $chatUsersIds[]=$orders->mate_id;
                          }
                           
                        }else if($orders->status==2 && $orders->order_complete_date_time!='0000-00-00 00:00:00')
                        {
                              $dt = new DateTime(date('Y-m-d H:i:s'));
          
                                //get timezone as per country
                              $countryInfo=Country::where('id',$orders->country_id)->first();
                             if(count($countryInfo)>0)
                             {
                                 $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                 $dt->setTimezone($tz);
                             }

                            $date2_val= $dt->format('Y-m-d H:i:s'); 
                            $date2=new DateTime($date2_val);
                            $date1=new DateTime($orders->order_complete_date_time);
                            $diffdate=date_diff($date1,$date2);
                            if(($diffdate->i<$chat_default_time) && (($diffdate->h)<=0) )
                            {
                                  if(!(in_array($orders->mate_id,$chatUsersIds)))
                                {
                                  $userInfo=UserInformation::where('user_id',$orders->mate_id)->first();
                                  $chatUsers[$i]['user_info']=$userInfo;
                                  $i++;
                                   $chatUsersIds[]=$orders->mate_id;
                                }
                            }
                        }
                     }
                 }
                $arr_to_return=array("error_code"=>0,"count"=>count($chatUsers),"users"=>$chatUsers);
            }
         }else{
           $arr_to_return=array("error_code"=>1,"count"=>'0');
        }
        
        return response()->json($arr_to_return);
    }

    public function getCustomerUserOrderHistory(Request $request){
        $mate_id =  isset($request['mate_id'])?$request['mate_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
          \App::setLocale($locale);
          
        if($mate_id>0 && $mate_id !=''){
            $arr_orders = Order::where('mate_id',$mate_id)->whereIn('status',[2,3,4])->get();
            $arr_orders=$arr_orders->sortByDesc('created_at');
            if(isset($arr_orders) && count($arr_orders)>0){
                //storing all order details
                $i=0;
                foreach($arr_orders as $order)
                {
                   $arrOrderDetails[$i]['order']=$order;
                    // getting order details.
                    $arrOrderDetails[$i]['order_details']=$order->getOrderTransInformation;
                    $arrOrderDetails[$i]['service']=$order->getServicesDetails->name;
                    if(isset($order->getServicesDetails))
                    {
                        $service=$order->getServicesDetails;
                        $arrOrderDetails[$i]['category']=$service->categoryInfo->name;
                        //get status by star text
                        $catgeoryMsgDetails=CategoryStatusMsg::where('category_id',$service->categoryInfo->id)->where('status_value',$order->status_by_star)->first();
                        $arrOrderDetails[$i]['status_by_star_text']=isset($catgeoryMsgDetails->status_description)?$catgeoryMsgDetails->status_description:'';
                    }
                    $mateRating=UserRatingInformation::where('to_id',$order->mate_id)->where('order_id',$order->id)->first();
                    $arrOrderDetails[$i]['order_rating']=isset($mateRating->rating)?$mateRating->rating:'0';
                   $i++;
                }
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_listing_success',$locale),"order_details"=>$arrOrderDetails);
         
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.history_orders_not_exist',$locale));
            }
        }else{
                $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.mate_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    public function getdeliveryuserUserOrderHistory(Request $request){
        $driver_id =  isset($request['driver_id'])?$request['driver_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
         \App::setLocale($locale);
        if($driver_id>0 && $driver_id !=''){
            $arr_orders = Order::where('driver_id',$driver_id)->whereIn('status',[2,3])->get();
            $arr_orders=$arr_orders->sortByDesc('created_at');
            $arr_orders=$arr_orders->take(10);
            if(isset($arr_orders) && count($arr_orders)>0){
                //storing all order details
                $i=0;
                foreach($arr_orders as $order)
                {
                     $arrOrderDetails[$i]['order']=$order;
                    // getting order details.
                    $arrOrderDetails[$i]['order_details']=$order->getOrderTransInformation;    
                    $arrOrderDetails[$i]['service']=$order->getServicesDetails->name;
                    if(isset($order->getServicesDetails))
                    {
                        $service=$order->getServicesDetails;
                        $arrOrderDetails[$i]['category']=$service->categoryInfo->name;
                         //get status by star text
                        $catgeoryMsgDetails=CategoryStatusMsg::where('category_id',$service->categoryInfo->id)->where('status_value',$order->status_by_star)->first();
                        $arrOrderDetails[$i]['status_by_star_text']=isset($catgeoryMsgDetails->status_description)?$catgeoryMsgDetails->status_description:'';
                    }
                   $starRating=UserRatingInformation::where('to_id',$order->driver_id)->where('order_id',$order->id)->first();
                  
                   $arrOrderDetails[$i]['order_rating']=isset($starRating->rating)?$starRating->rating:'0';
                   $i++;
                }
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_listing_success',$locale),"order_details"=>$arrOrderDetails);
         
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.history_orders_not_exist',$locale));
            }
        }else{
                $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.star_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    public function getdeliveryuserUserCurrentOrder(Request $request){
        $user_id =  isset($request['driver_id'])?$request['driver_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $arrOrderDetails=array();  
        $arrOrderDetailsAssigned=array();  
        if($user_id>0 && $user_id !=''){
            $arr_orders = Order::where('driver_id',$user_id)->where('status','1')->get();
            $arr_assigned_orders =  OrderNotification::where('user_id',$user_id)->get();
          
            $arr_orders=$arr_orders->sortByDesc('created_at');
            if((count($arr_assigned_orders)>0) || count($arr_orders)>0){
                //storing all order details
                $i=0;
               if(count($arr_orders))
               {
                foreach($arr_orders as $order)
                {
                    $arrOrderDetails[$i]['order']=$order;
                    // getting order details.
                    $arrOrderDetails[$i]['order_details']=$order->getOrderTransInformation;
                    $arrOrderDetails[$i]['service']=$order->getServicesDetails->name;
                    
                   if(isset($order->getServicesDetails))
                    {
                        $service=$order->getServicesDetails;
                        $arrOrderDetails[$i]['category']=$service->categoryInfo->name;
                         //get status by star text
                        $catgeoryMsgDetails=CategoryStatusMsg::where('category_id',$service->categoryInfo->id)->where('status_value',$order->status_by_star)->first();
                        $arrOrderDetails[$i]['status_by_star_text']=isset($catgeoryMsgDetails->status_description)?$catgeoryMsgDetails->status_description:'';
                    }
                   $i++;
                }
               }
                
               //getting star assigned order
                    $arr_assigned_orders =  OrderNotification::where('user_id',$user_id)->get();
                    $arr_assigned_orders=$arr_assigned_orders->reject(function($order_info)
                    { 
                      if(isset($order_info->orderDetailsInfo))
                        {
                            return ($order_info->orderDetailsInfo->status!=0);
                        }
                    })->values();
                    
                     $arr_assigned_orders=$arr_assigned_orders->sortByDesc('created_at');
                   
                    if(isset($arr_assigned_orders) && count($arr_assigned_orders)>0){
                       {
                         $j=0;
                         foreach($arr_assigned_orders as $order1)
                            {
                             
                             if(isset($order1->orderDetailsInfo->getOrderTransInformation))
                             {
                                $arrOrderDetailsAssigned[$j]['order']=$order1->orderDetailsInfo;
                                // getting order details.
                                $arrOrderDetailsAssigned[$j]['order_details']=$order1->orderDetailsInfo->getOrderTransInformation;
                                $arrOrderDetailsAssigned[$j]['service']=$order1->orderDetailsInfo->getServicesDetails->name;
                            
                                 if(isset($order1->orderDetailsInfo->getServicesDetails->categoryInfo->name))
                                {
                                    $service=$order1->orderDetailsInfo->getServicesDetails;
                                    $arrOrderDetailsAssigned[$j]['category']=$order1->orderDetailsInfo->getServicesDetails->categoryInfo->name;
                                }
                               
                                 //check if user mate any quote in assign order                                
                                 $checkalreadyQuote=UserServiceQuotation::where('order_id',$order1->order_id)->where('user_id',$user_id)->first();
                                
                                 if(count($checkalreadyQuote)>0)
                                 {
                                      $arrOrderDetailsAssigned[$j]['quote_amt']=$checkalreadyQuote->qutation_amount;
                                 }else{
                                    $arrOrderDetailsAssigned[$j]['quote_amt']="0";
                                 }
                               $j++;
                             }
                            }
                           
                     }
                   
               }
              
                $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_listing_success',$locale),"order_details"=>$arrOrderDetails,"assigned_orders"=>$arrOrderDetailsAssigned);
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.current_orders_not_exist',$locale));
            }
        }else{
                $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.star_invalid',$locale));
        }
        return response()->json($arr_to_return);
    }
       
    public function listUserAddresses(Request $request){        
        $arr_to_return=array();
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';  
        \App::setLocale($locale);
        if(isset($user_id) && $user_id>0){             
              $arrUserAddress=UserAddress::where("user_id",$user_id)->get();                
              $arrUserAddress=$arrUserAddress->reject(function($address)
              {
                  return ($address->address_type=='1');
              })->values();
              $arr_to_return = array("error_code" => 0, "data" =>$arrUserAddress);
        }
        else{
          $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
        }
        return response()->json($arr_to_return);
    }
    public function getAllCustomerNotifications(Request $request){        
        $arr_to_return=array();
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';  
        \App::setLocale($locale);
        if(isset($user_id) && $user_id>0){             
            $date_campare=date("Y-m-d 00:00:00",strtotime('-15 Days',strtotime(date('Y-m-d'))));
            $arrAllNotifications=Notification::where("user_id",$user_id)->whereDate('notification_date','>=',$date_campare)->get();                
            if(count($arrAllNotifications)>0)
            {
                foreach($arrAllNotifications as $notification)
                {
                    $notification->read_status=1;
                   $notification->save();
                }
            }
             $arrAllNotifications=$arrAllNotifications->sortByDesc('id');
           
            $arrAppNotification=$arrAllNotifications->reject(function($notification)
            {
                return ($notification->type!='0');
            })->values();
            $arrSystemUpdatesNotification=$arrAllNotifications->reject(function($notification)
            {
                return ($notification->type!='1');
            })->values();
            $arrOffersNotification=$arrAllNotifications->reject(function($notification)
            {
                return ($notification->type!='2');
            })->values();
            $arr_to_return = array("error_code" => 0, "app_notifications" =>$arrAppNotification,"app_system_notifications"=>$arrSystemUpdatesNotification,"app_offers_notifications"=>$arrOffersNotification);
        }
        else{
          $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_profile_not_found',$locale));
        }
         
        return response()->json($arr_to_return);
    }
   public function getOrderDetails(Request $request){
        $order_id =  isset($request['order_id'])?$request['order_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $user_id =  isset($request['user_id'])?$request['user_id']:'en';
        \App::setLocale($locale);          
        if($order_id>0 && $order_id !=''){
            $order = Order::find($order_id);
            if(isset($order)){
                    //storing all order details
                    $arrOrderDetails['order']=$order;
                     // getting order details.
                    $arrOrderDetails['order_details']=$order->getOrderTransInformation;
                    $arrOrderDetails['service']=$order->getServicesDetails->name;
                    if(isset($order->getServicesDetails))
                    {
                        $service=$order->getServicesDetails;
                        $arrOrderDetails['category']=$service->categoryInfo->name;
                    }
                    $arrOrderDetails['image_path']=asset("storageasset/item-images/");
                    if(isset($order->getOrderImages))
                    {
                         $k=0;
                         foreach($order->getOrderImages as $images)
                         {
                               $arrOrderDetails['order_images'][$k]=$images;
                               $k++;
                         }
                     }
                     //getting star rating
                     $starRating=UserRatingInformation::where('to_id',$order->driver_id)->where('order_id',$order->id)->first();
                     $arrOrderDetails['star_rating']=isset($starRating->rating)?$starRating->rating:'0';
                     
                      //getting mate rating
                     $mateRating=UserRatingInformation::where('to_id',$order->mate_id)->where('order_id',$order->id)->first();
                     $arrOrderDetails['mate_rating']=isset($mateRating->rating)?$mateRating->rating:'0';
                    
                     $user_order_notification=  OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
                     //check is user has notifcaton of order
                    if(count($user_order_notification)>0)
                    {
                     $arrOrderDetails['has_notification']="1";
                    }else{
                     $arrOrderDetails['has_notification']="0";
                    }
                     $coutnryServices=CountryServices::where('city_id',$order->city_id)->where('service_id',$order->service_id)->first();
                    $arrOrderDetails['service_fare_details']=$coutnryServices;
                    //get star details
                     if(isset($order->driver_id))
                    {
                        $star_user_details=UserInformation::where('user_id',$order->driver_id)->first();
                        $arrOrderDetails['star_first_name']=$star_user_details->first_name;
                        $arrOrderDetails['star_last_name']=$star_user_details->last_name;
                        $arrOrderDetails['star_mobile']="+".str_replace("+","",$star_user_details->mobile_code)."".$star_user_details->user_mobile;
                        if (isset($star_user_details->profile_picture)) {                         
                         $arrOrderDetails ['star_image']=asset("/storageasset/user-images/" . $star_user_details->profile_picture);
                        }else{
                            $arrOrderDetails ['star_image']="";
                        }
                       // $arrOrderDetails[$i]['star_image']=$star_user_details->user_mobile;
                    }else{
                         $arrOrderDetails['star_first_name']="";
                         $arrOrderDetails['star_last_name']="";
                         $arrOrderDetails['star_mobile']="";
                         $arrOrderDetails['star_image']="";
                    }
                    $paymentMethod =PaymentMethod::where('id',$order->payment_type)->first();
                    $arrOrderDetails['payment_method']=isset($paymentMethod->title)?$paymentMethod->title:'';
                    $arr_to_return = array("error_code" => 0,"order_details"=>$arrOrderDetails);
                }
              else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.invalid_order_link',$locale));
            }
        }else{
                $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.invalid_order_link',$locale));
        }
        return response()->json($arr_to_return);
    }
    
    public function addUserAddress(Request $request){   
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $address =  isset($request['address'])?$request['address']:'';
        $zipcode =  isset($request['zipcode'])?$request['zipcode']:'';
        $latitude =  isset($request['latitude'])?$request['latitude']:'';
        $longitude =  isset($request['longitude'])?$request['longitude']:'';
        $address_type = isset($request['address_type'])?$request['address_type']:'1';     
        $address_name = isset($request['address_name'])?$request['address_name']:'';     
        \App::setLocale($locale);
        if($user_id>0 && $user_id !=''){
        $user_address=array("user_id"=>$user_id,"address_name"=>$address_name,"address"=>$address,"zipcode"=>$zipcode,"latitude"=>$latitude,"longitude"=>$longitude,"address_type"=>$address_type);
        $address= UserAddress::create($user_address);
        $arr_to_return = array("error_code" => 0,"data"=>$address,"msg" =>Lang::choice('messages.address_created',$locale));    
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
         return response()->json($arr_to_return);
    }
            
    public function updateUserAddress(Request $request){
        //
        $address_id =  isset($request['address_id'])?$request['address_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $address =  isset($request['address'])?$request['address']:'';
        $zipcode =  isset($request['zipcode'])?$request['zipcode']:'';
        $latitude =  isset($request['latitude'])?$request['latitude']:'';
        $longitude =  isset($request['longitude'])?$request['longitude']:'';
        $address_type = isset($request['address_type'])?$request['address_type']:'';
        $address_name = isset($request['address_name'])?$request['address_name']:'';     
       
        \App::setLocale($locale);
        if($address_id>0 && $address_id !=''){
        $user_address=UserAddress::find($address_id);  
        $user_address->address=$address;
        $user_address->zipcode=$zipcode;
        $user_address->latitude=$latitude;
        $user_address->longitude=$longitude;
        $user_address->address_type=$address_type;
        $user_address->address_name=$address_name;
        $user_address->save();
        $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.address_updated',$locale));    
                 
        }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
         return response()->json($arr_to_return);
    }
    
    public function deleteUserAddress(Request $request){
        //
        $address_id =  isset($request['address_id'])?$request['address_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($address_id>0 && $address_id !=''){
        $user_address=UserAddress::where('id',$address_id);  
        $user_address->delete();
        $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.address_deleted',$locale));    
       }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
      return response()->json($arr_to_return);
    }

    public function deleteOrder(Request $request){
        //
        $order_id =  isset($request['order_id'])?$request['order_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $cancel_flag =  isset($request['cancel_flag'])?$request['cancel_flag']:'0';
        \App::setLocale($locale);
        $cancellation_time=GlobalValues::get('cancelation_time');
        $cancelation_limit=GlobalValues::get('cancelation_limit');
        $star_cancel_percentage=GlobalValues::get('star_cancel_percentage');
        $fare =0;
      if($order_id>0 && $order_id !=''){
          
            $order=Order::where('id',$order_id)->first(); 
            $user_id=$order->mate_id;
            
            $country_details=Country::where('id',$order->country_id)->first();
            $cancellation_charge=isset($country_details->cancellation_charge)?$country_details->cancellation_charge:'0';
            $currency_code=isset($country_details->currency_code)?$country_details->currency_code:'';
            
            //get cancellintion count
           $cancelled_orders= Order::where('cancelled_by',$user_id)->get(); 
         
            /*Calculate distance and duration of return */
            $duration_in_minutes = 0;
            $distance_in_km=0;
            $distance=0;
           
        if($order->status_by_star>=2 && (count($cancelation_limit)>3)){
           if($order->service_id!=20 && $order->service_id!=28)
            {
//            $order->getOrderTransInformation->pickup_lat;
//            $order->getOrderTransInformation->pickup_long;
                $path = realpath(dirname(__FILE__) . '/../../../public');
                $file_name = $path . "/order_tracking/order_distance_" . $order->id;
                $path = $file_name . ".txt";
                $i = 0;
                if(file_exists($path))
                {
                    $location_resource = fopen($path, "r");
                     while (!feof($location_resource)) 
                    {
                         $line = fgets($location_resource);
                         if($line!='NaN' && $line!='nan' && $line!=0)
                         {
                             $distance =$distance+(double)$line;
                         }
                     }
                }
                $distance=(float)$distance;
                if($distance==0 ||$distance=='0.00')
                     {
                            $lat1=$order->getOrderTransInformation->pickup_lat;
                            $lon1=$order->getOrderTransInformation->pickup_long;
                            $path = realpath(dirname(__FILE__) . '/../../../public');
                            $file_name = $path . "/order_tracking/order_" . $order->id;
                            $file_path = $file_name . ".txt";
                            $lat2=0;
                            $lon2=0;
                            $file_last_lat_long = escapeshellarg($file_path); // for the security concious (should be everyone!)
                            $last_lat_long = `tail -n 1 $file_last_lat_long`;
                            if(isset($last_lat_long) && $last_lat_long!='')
                            {
                                $arr_last_lat_long=explode(",",$last_lat_long);

                                if(count($arr_last_lat_long)>0)
                                {
                                    $lat2=$arr_last_lat_long[0];
                                    $lon2=$arr_last_lat_long[1];
                                   // $distance= $this->getDistanceBetweenPointsNew($current_lat,$current_long,$last_lat,$last_long,'Km');
                                }
                            }
                            $theta = $lon1 - $lon2;
                            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                            $dist = acos($dist);
                            $dist = rad2deg($dist);
                            $distance = ($dist * 60 * 1.1515)*(1.609344);
      
                  }
                  
                   $arrDataService=CountryServices::where('country_id',$order->country_id)->where('service_id',$order->service_id)->first();
                   if($arrDataService->price_type=='1')
                   {
                      $fare=(double)$arrDataService->base_price;
                    }else{
                         $check_point_distance=0;
                            if(isset($arrDataService->check_point_distance) && $arrDataService->check_point_distance>0)
                            {
                                $check_point_distance=$arrDataService->check_point_distance;
                            }
                            if($distance>=$check_point_distance && $check_point_distance>0)   
                            {
                                $fare = (double) $arrDataService->flat_price;
                            }else{
                                if($distance>$arrDataService->base_km)
                                {
                                    $fare= (double)$arrDataService->base_price;
                                    $fare=$fare + (double) ($distance-$arrDataService->base_km) * ($arrDataService->price_per_km);

          //                            $fare=(double)((($distance)*($arrDataService->price_per_km)));
          //                            $fare=$fare + (double)$arrDataService->base_price;

                                }else{
                                     $fare=(double)$arrDataService->base_price;
                                }
                            }
                    }
                    $cancellation_charge = $cancellation_charge + $fare;
                } 
          
        }
          /* end calculate distance and duration of return */
              
       if($cancel_flag==1)
       {
           //remove amount from wallet
           //get calcellation amount
           
           if($cancellation_charge>0)
           {      
                if($order->payment_type==2 || $order->payment_type==3){ 
                    $user_wallet_amt = UserWalletDetail::where('user_id',$user_id)->orderBy('id', 'desc')->first(['final_amout']);
                    $final_amt=isset($user_wallet_amt->final_amout)?$user_wallet_amt->final_amout:'0';
                    if($final_amt<$cancellation_charge ) 
                    {
                        $final_amt_update=(double)($final_amt-$cancellation_charge);
                        $arrWalletAmt=array();
                        $arrWalletAmt['user_id']=$order->mate_id;
                        $arrWalletAmt['transaction_amount']=$cancellation_charge;
                        $arrWalletAmt['final_amout']=$final_amt_update;
                        $arrWalletAmt['trans_desc']=Lang::choice('messages.cancel_order_msg',$locale);
                        $arrWalletAmt['transaction_type']='1';                
                        $mate_wallet_data = UserWalletDetail::create($arrWalletAmt);
                   
                    }else{
                     $final_amt_update=(double)($final_amt-$cancellation_charge);
                     $arrWalletAmt=array();
                     $arrWalletAmt['user_id']=$order->mate_id;
                     $arrWalletAmt['transaction_amount']=$cancellation_charge;
                     $arrWalletAmt['final_amout']=$final_amt_update;
                     $arrWalletAmt['trans_desc']=Lang::choice('messages.cancel_order_msg',$locale);
                     $arrWalletAmt['transaction_type']='1';                
                     $mate_wallet_data = UserWalletDetail::create($arrWalletAmt);
                    }
                
                }
                $order->cancelled_date=date('Y-m-d H:i:s');
                $order->cancellation_charge=$cancellation_charge;
                if($order->status_by_star==3){
                    $order->status_by_star=7;
                }else{
                    $order->status=3;
                }
                if($cancellation_charge>0)
                 {
                     $order->cancelled_by=$order->mate_id;
                 }
                $order->save();
                //if any star is assigned to this user so need 
                 $driver_id=isset($order->driver_id)?$order->driver_id:'0';
                
                 if($driver_id>0 && ($order->payment_type==2  || $order->payment_type==3) ) // if COD and Wallet percent amount will credit to star wallet 
                 {
                    $star_wallet_data = UserWalletDetail::where('user_id',$driver_id)->orderBy('id', 'desc')->first(['final_amout']);
                    $prev_file_amount=isset($star_wallet_data->final_amout)?$star_wallet_data->final_amout:'0';
                    $commision_star=(($cancellation_charge)*($star_cancel_percentage/100));
                    $walletAmount=array();
                    $walletAmount['user_id']=$driver_id;
                    $walletAmount['transaction_amount']=(($cancellation_charge)*($star_cancel_percentage/100));
                    $walletAmount['final_amout']=(double)($prev_file_amount+$commision_star);
                    $walletAmount['trans_desc']=Lang::choice('messages.trans_desc',$locale);
                    $walletAmount['transaction_type']=0;
                    $walletAmount['payment_type']=2;
                    UserWalletDetail::create($walletAmount);
                     //check for same order
                    $walletAmountDetails=array();
                    $walletAmount['user_id']=$driver_id;
                    $walletAmount['star_amount']=(($cancellation_charge)*($star_cancel_percentage/100));
                    $walletAmount['total_amount']=(double)($cancellation_charge);
                    $walletAmount['pay_type']='1';
                    $walletAmount['type']='1';
                    $walletAmount['order_id']=$order->id;
                    DeliveryuserBalanceDetail::create($walletAmountDetails);
                 }
                 
                 /* send notification to star if mate has cancel the order in-between travling status */
                 if($order->status_by_star==3){
                      $star_details=User::where('id',$order->driver_id)->first();
                    //sending push notification to star
                    $cancel_message=Lang::choice('messages.order_cancel',$locale);
                    $cancel_message=$cancel_message."".$order->order_unique_id;
                    $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>$cancel_message,"flag"=>'order_cancel','message'=>$cancel_message,'order_id'=>$order->id);
                    $arr_push_message_ios=array();
                    if(isset($star_details->userInformation->device_id) && $star_details->userInformation->device_id!='')
                    {
                        $obj_send_push_notification=new SendPushNotification();  
                        if($star_details->userInformation->device_type=='0')
                        {
                            
                         //sending push notification star user.
                            $arr_push_message_android=array();
                            $arr_push_message_android['to']=$star_details->userInformation->device_id;
                            $arr_push_message_android['priority']="high";
                            $arr_push_message_android['sound']="default";
                            $arr_push_message_android['notification']=$arr_push_message;
                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                            
                        }else{
                             $arr_push_message_ios['to']=$star_details->userInformation->device_id;
                             $arr_push_message_ios['priority']="high";
                             $arr_push_message_ios['sound']="default";
                             $arr_push_message_ios['notification']=$arr_push_message;
                             $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                             
                        }
                    }
                  if(count($star_details)>0)
                  {
                     $mateDetails=  UserInformation::where('user_id',$user_id)->first();
                    //saving
                    $notiMsg=Lang::choice('messages.order_has_been_canceled_msg',$locale);
                    $notiMsg=str_replace("%%CUSTOMER_NAME%%", $mateDetails->first_name." ". $mateDetails->last_name,$notiMsg);
                    $notiMsg=str_replace("%%ORDER_NUMBER%%", $order->order_unique_id,$notiMsg);
                    $saveNotification=new AppNotification();
                    $saveNotification->saveNotification($star_details->id,$order->id,Lang::choice('messages.order_has_been_cancelled',$locale),$notiMsg,date("Y-m-d"),0,'order');
                  }
                 }
                 
                 /* send notification to star if mate has cancel the order in-between travling status */
                 $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_has_been_deleted',$locale));    
           }
           
       }else{
          
            $created_date_time=$order->picked_up_time;
           
            $dt = new DateTime(date('Y-m-d H:i:s'));
          
            //get timezone as per country
            $countryInfo=Country::where('id',$order->country_id)->first();
            if(count($countryInfo)>0)
            {
                 $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                 $dt->setTimezone($tz);
            }
          
            $date1_val= $dt->format('Y-m-d H:i:s'); 
            $date1=new DateTime($date1_val);
            $date2=new DateTime($created_date_time);
            $diffdate=date_diff($date1,$date2);
            $total_minutes=$diffdate->i;
      
            if(($total_minutes>=$cancellation_time) && ($order->status>0) && (count($cancelled_orders)>3))
            {
              $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.time_overhead1',$locale)."-".$currency_code." ".$cancellation_charge." ".Lang::choice('messages.time_overhead2',$locale));  
            }else{
              
                 $order->cancelled_date=date('Y-m-d H:i:s');
//                 $order->cancellation_charge=$cancellation_charge;
                if($order->status_by_star==3){                  
                    $order->status_by_star=7;
                }else{
                    
                  $order->status=3;
                }
                 $order->is_mate_canceled=1;
                 $order->cancellation_charge=0;
                 if($cancellation_charge>0)
                 {
                     $order->cancelled_by=$order->mate_id;
                 }
                
                 //$order->status=3;
                 $order->save();
                 $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_has_been_deleted',$locale));    
            }
         }
       } else{
                 $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_invalid',$locale));
             }
       
      return response()->json($arr_to_return);
    }
    public function setUserAvailability(Request $request){
        //
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $availability =  isset($request['availability'])?$request['availability']:'';
        \App::setLocale($locale);
        if($user_id>0 && $user_id !='' && $availability!=''){
            $flag=0;
            if($availability=='0')
            {
                $order_check=Order::where('driver_id',$user_id)->where('status','1')->first();
                if(count($order_check)>0)
                {
                    $flag=1;
                }
            }
            if($flag==0)
            {
                $user_star=DriverUserInformation::where('user_id',$user_id)->first();  
                $user_star->availability=$availability;        
                $user_star->save();
                 if($availability=='0')
                {
                    $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.user_availability_updated_offline',$locale));    
                }else{
                    $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.user_availability_updated',$locale));    
                }
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_assigned_can_set_availibily',$locale));
            }
       }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
        }
      return response()->json($arr_to_return);
    }
    
    public function deliveryuserAcceptOrder(Request $request){
        
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $order_id =  isset($request['order_id'])?$request['order_id']:'0';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $current_lat =  isset($request['current_lat'])?$request['current_lat']:'0';
        $current_lng =  isset($request['current_lng'])?$request['current_lng']:'0';
        \App::setLocale($locale);
        $user_details=UserInformation::where('user_id',$user_id)->first();
        if($order_id>0 && $user_id>0)
         {
             $order_details=Order::where('id',$order_id)->first();
             $order_locale=isset($order_details->locale)?$order_details->locale:'en';
             \App::setLocale($order_locale);
             $country_id=$order_details->country_id;
             $order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
           if(count($order_notification)>0)
           {
             if($order_details->status==0)
              {
                 if(!($order_details->driver_id>0))
                 {
                     $order_details->driver_id=$user_id;
                     $order_details->status=1;
                     $order_details->status_by_star=1;
                     $order_details->save();
                     
                     //re calculate the fare estimate if drop are was not there
                  if($current_lat!='' && $current_lat!='0' && $current_lng!='0'  && $current_lng!='')
                  {
                    if($order_details->getOrderTransInformation->drop_area==''){ 
                      $distance= $this->getDistanceBetweenPointsNew($current_lat,$current_lng,$order_details->getOrderTransInformation->selected_pickup_lat,$order_details->getOrderTransInformation->selected_pickup_long,'Km');
                      $distance=(float)$distance;
                      $fare = 0;
                      if($distance>0)
                      {
                           $arrDataService = CountryServices::where('country_id', $order_details->country_id)->where('service_id', $order_details->service_id)->first();
                           
                            if (count($arrDataService) > 0) {
                                if ($arrDataService->price_type == '1') {
                                    $fare = (double) $arrDataService->base_price;
                                } else {
                                        $check_point_distance=0;
                                        if(isset($arrDataService->check_point_distance) && $arrDataService->check_point_distance>0)
                                        {
                                            $check_point_distance=$arrDataService->check_point_distance;
                                        }
                                        if($distance>=$check_point_distance && $check_point_distance>0)   
                                        {
                                            $fare = (double) $arrDataService->flat_price;
                                        }else{
                                            if ($distance > $arrDataService->base_km) {
                                                $fare= (double)$arrDataService->base_price;
                                                $fare=$fare + (double) ($distance-$arrDataService->base_km) * ($arrDataService->price_per_km);

        //                                        $fare = (double) ($distance) * ($arrDataService->price_per_km);
        //                                        $fare=$fare + (double)$arrDataService->base_price;
                                            } else {
                                                $fare = (double) $arrDataService->base_price;
                                            }
                                        }
                                }

                            }
                      }
                      if($fare>0)
                      {
                          $order_details->fare_amount=$fare;
                          $order_details->save();
                      }
                  }}
                     //saving this information to status transaction
                     $statusTransaction=array();
                     $statusTransaction['user_id']=$user_id;
                     $statusTransaction['order_id']=$order_id;
                     $statusTransaction['transaction_content']="Order has been accepted";
                     OrdersTransactionStatus::create($statusTransaction);
                     //removing order notification of that user
                     $order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
                     if(isset($order_notification->id))
                     {
                        $order_notification->delete();
                     }
                     //sending email to mate about order accepted andetails of star
                     
                     $user_details=UserInformation::where('user_id',$user_id)->first();
                     $mate_details=User::where('id',$order_details->mate_id)->first();
                     $site_email=GlobalValues::get('site-email');
                     $site_title=GlobalValues::get('site-title');
                     $arr_keyword_values = array();
                    //Assign values to all macros
                     $arr_keyword_values['MATE_FIRST_NAME'] = $mate_details->userInformation->first_name;
                     $arr_keyword_values['STAR_FIRST_NAME'] = $user_details->first_name;
                     $arr_keyword_values['STAR_LAST_NAME'] = $user_details->last_name;
                     $arr_keyword_values['SITE_TITLE'] = $site_title;
                     $arr_keyword_values['ORDER_ID'] = $order_id;
                     
                     $mobile_code=str_replace("+","",$user_details->mobile_code);
                     $arr_keyword_values['STAR_MOBILE'] = "+".$mobile_code."".$user_details->user_mobile; 
                     $email_subject=Lang::choice('messages.star_order_accepted_notify_to_mate',$order_locale);
                     $tempate_name="emailtemplate::order-accepted-notify-to-mate-star-details-".$order_locale;
                     if(isset($mate_details->email))
                     {
                         $mate_email=$mate_details->email;
                         Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($mate_email,$email_subject,$site_email,$site_title)  {

                           $message->to($mate_email)->subject($email_subject)->from($site_email,$site_title);

                       });
                     }
                      if(isset($mate_details->userInformation->user_mobile) && (isset($user_details->mobile_code)))
                     {
                        $mobile_code=str_replace("+","",$mate_details->userInformation->mobile_code);  
                        $mobile_number_to_send="+".$mobile_code."".$mate_details->userInformation->user_mobile;
                        $message= "";
                        $order_accept_msg_ar=Lang::choice('messages.mate_sms_order_accepted',$locale);
                        $order_star_number_msg_ar=Lang::choice('messages.star_number',$locale);
                    
                        //sending sms to customer
                        $message=Lang::choice('messages.mate_sms_order_accepted',$order_locale)." ".$user_details->first_name." ".$user_details->last_name;
                        $message.="\n";
                        $message.=Lang::choice('messages.star_number_msg',$order_locale)." +".str_replace("+","",trim($user_details->mobile_code))."".$user_details->user_mobile."";  
                        $obj_sms=new SendSms();
                        $obj_sms->sendMessage($mobile_number_to_send,$message); 
                        $notiMsg=Lang::choice('messages.order_has_been_accepted_msg',$order_locale);
                        $notiMsg=str_replace("%%DRIVER_NAME%%", $user_details->first_name." ". $user_details->last_name,$notiMsg);
                        $notiMsg=str_replace("%%ORDER_NUMBER%%", $order_details->order_unique_id,$notiMsg);
                        $notiMsg=str_replace("%%DATE_TIME%%",date("Y-m-d H:i:s"),$notiMsg);
                        $saveNotification=new AppNotification();
                        $saveNotification->saveNotification($mate_details->id,$order_details->id,Lang::choice('messages.order_has_been_accepted',$order_locale),$notiMsg,date("Y-m-d"),0,'order');
                
                        \App::setLocale($locale);
                     }
                     //sending push notification to mate
                       $arr_push_message=array("sound"=>"default",'title' =>'BAGGI',"text"=>Lang::choice('messages.order_accepted',$order_locale),"flag"=>'order_accepted','message'=>Lang::choice('messages.order_accepted',$order_locale),'order_id'=>$order_details->id);
                  
                       $arr_push_message_ios=array();
                       $obj_send_push_notification=new SendPushNotification();  
                       if(isset($mate_details->userInformation->device_id) && $mate_details->userInformation->device_id!='')
                       {
                        if($mate_details->userInformation->device_type=='0')
                        {
                         //sending push notification mate user.
                            $arr_push_message_android=array();
                            $arr_push_message_android['to']=$mate_details->userInformation->device_id;
                            $arr_push_message_android['priority']="high";
                            $arr_push_message_android['sound']="default";
                            $arr_push_message_android['notification']=$arr_push_message;
                            $obj_send_push_notification->androidPushNotificaton(json_encode($arr_push_message_android));

                        }else{
                            $arr_push_message_ios['to']=$mate_details->userInformation->device_id;
                            $arr_push_message_ios['priority']="high";
                            $arr_push_message_ios['sound']="default";
                            $arr_push_message_ios['notification']=$arr_push_message;
                            $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                            
                        }
                       }
                       //sending emails to user
                       //sending email
                   
                    $adminusers=UserInformation::where('user_type',1)->get();
                    $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                    {
                        $country=0;
                         if(isset($user_details->user->userAddress))
                         {

                             foreach($user_details->user->userAddress as $address)
                             {
                                   $country=$address->user_country;
                             }
                         }
                        if($country && $country!=0)
                        {
                           return (($country!=$country_id) ||($user_details->user->supervisor_id!=0));
                        }
                        if($user_details->user->hasRole('superadmin'))
                        {
                            return true;
                        }

                    });
                    $agentusers = UserInformation::where('user_type', 4)->get();
                    $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                        $country = 0;
                        if (isset($user_details->user->userAddress)) {

                            foreach ($user_details->user->userAddress as $address) {
                                $country = $address->user_country;
                            }
                        }
                        if ($country && $country != 0) {
                            return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                        }
                    });
                   
                    $site_email=GlobalValues::get('site-email');
                    $site_title=GlobalValues::get('site-title');
                    //Assign values to all macros
                     $arr_keyword_values['STAR_FIRST_NAME'] = $user_details->first_name;
                     $arr_keyword_values['STAR_LAST_NAME'] = $user_details->last_name;
                     $arr_keyword_values['STAR_ID'] =  $user_id;
                     $arr_keyword_values['ORDER_ID'] =  $order_id;
                     $arr_keyword_values['ORDER_NUMBER'] =  $order_details->order_unique_id;
                     $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $email_template_title="emailtemplate::order-accepted-to-admin";    
                    $email_template_subject="Order has been accepted by star";
//                   if(count($adminusers)>0)
//                   {
//                       foreach($adminusers as $admin)
//                       { 
//
//                           Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
//                             if(isset($admin->user->email))
//                             {
//                                 $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
//                             }
//
//                         });
//                      }
//                   }
                  if (count($agentusers) > 0) {
                        foreach ($agentusers as $agent) {

                            Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                if (isset($agent->user->email)) {
                                    $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                }
                            });
                        }
                    }
                  
                   $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.star_has_been_assigned',$locale));        
                 }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_already_accepted',$locale));    
             }
             }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_already_accepted',$locale));    
             }
             
         }else{
              //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
        }
         }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
         }
      return response()->json($arr_to_return);
    }
    
    
    public function deliveryuserRejectOrder(Request $request){        //
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $order_id =  isset($request['order_id'])?$request['order_id']:'0';
        $reason_text =  isset($request['reason'])?$request['reason']:'0';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $radious=GlobalValues::get('star-range-radious');
        $flag_available=0;
        $avalale_driver_id=0;
        $country_id=0;
        if($order_id>0 && $reason_text!='')
         {
           $order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
            if(isset($order_notification->id))
            { 
             // rejecting order by star
            $order_details=Order::where('id',$order_id)->first();
            $country_id=$order_details->country_id;
            $service_id_radious=$order_details->service_id;
            $service_details_radious=Service::where('id',$service_id_radious)->first();
            if(isset($service_details_radious->categoryInfo->request_range) && (($service_details_radious->categoryInfo->request_range)>0))
            {
                $radious=$service_details_radious->categoryInfo->request_range;
            }
            if($order_details->status=='0')
            {
                //$order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
                $order_notification->delete();
                $order_cancellation_status=array();
                $order_cancellation_status['order_id']=$order_id;
                $order_cancellation_status['user_id']=$user_id;
                $order_cancellation_status['reason_text']=$reason_text;
                //storing cancel reason
                OrderCancelationDetail::create($order_cancellation_status);
                
                //checking for other user star to send notification 
                $arrServiceUsers=UserServiceInformation::where('service_id',$order_details->service_id)->get();
                $arrServiceUsers=$arrServiceUsers->reject(function ($userInfo)
                {
                   if(isset($userInfo->user->driverUserInformation->availability))
                    return ($userInfo->user->driverUserInformation->availability==0);

                });
                  //get all user who has only 50 km range
                 if(count($arrServiceUsers)>0)
                  {
                      $user_ids="0";
                      $arrayUserIds=array();
                      foreach($arrServiceUsers as $users_ids)
                       {
                           if(isset($users_ids->user_id) && ($users_ids->user_id!=0) && ($users_ids->user_id!=$user_id) && ($order_details->getOrderTransInformation->distance<=$users_ids->goe_fence_area))
                           {
                               $user_ids.=",$users_ids->user_id";
                               $arrayUserIds[]=$users_ids->user_id;
                           }
                       }
                       $pick_up_lat=$order_details->getOrderTransInformation->selected_pickup_lat;
                       $pick_up_long=$order_details->getOrderTransInformation->selected_pickup_long;
                       //
                       $users=array();
                      if($pick_up_lat!='' && $pick_up_long!='') 
                      {
                        $users=  DB::select("call getUserByDistance(".$pick_up_lat.",".$pick_up_long.",'".$user_ids."',".$radious.")");
                      }

                      //check if a user is having any active orders
                      if(count($users)>0)
                      {
                          $j=0;
                          foreach($users as $user)
                          {
                            if(in_array($user->user_id,$arrayUserIds))
                              {  
                                $userDetailsStatus=UserInformation::where('user_id',$user->user_id)->first();
                                if($userDetailsStatus->user_status=='1' && $userDetailsStatus->user_type=='2') 
                               {
                                 $userData=Order::where('driver_id',$user->user_id)->where('status','1')->first();
                                 $order_cancel=OrderCancelationDetail::where('order_id',$order_id)->where('user_id',$user->user_id)->first();
                                 $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                 if(count($userData)<=0 && count($order_cancel)<=0 && count($order_notification_count)<=0)
                                 {
                                     $flag_available=1;
                                     $avalale_driver_id=$user->user_id;
                                     break;
                                 }
                               }
                           $j++; 
                          }}

                      }

               }
             if($flag_available==1 && $avalale_driver_id>0)
             {
                 //sending email about rejection only and assign star user an order.
                if($avalale_driver_id!='')
               {
                    $countryInfo=Country::where('id',$country_id)->first();
                    $dt = new DateTime(date('Y-m-d H:i:s'));
                    if(count($countryInfo)>0)
                    {
                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                         $dt->setTimezone($tz);
                    }

                    $date2_val= $dt->format('Y-m-d H:i:s'); 
                     $date2=new DateTime($date2_val);
                    //storing that user in notification table.
                    $arrOrderNotificationDetails['order_id']=$order_details->id;
                    $arrOrderNotificationDetails['user_id']=$avalale_driver_id;
                    $arrOrderNotificationDetails['message']=$order_details->order_unique_id. " ".Lang::choice('messages.order_assigned',$locale);
                    $arrOrderNotificationDetails['created_at'] = $date2;
                      $arrOrderNotificationDetails['updated_at'] = $date2;
                    OrderNotification::create($arrOrderNotificationDetails);

                    $avalale_star_details=UserInformation::where('user_id',$avalale_driver_id)->first();
                   if(isset($avalale_star_details->user_id))
                   {
                       $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star',$locale),"flag"=>'order_post','message'=>Lang::choice('messages.order_assign_star',$locale),'order_id'=>$order_details->id);
                       $arr_push_message_ios=array();
                      if(isset($avalale_star_details->device_id)&& $avalale_star_details->device_id!='')
                      {
                        $obj_send_push_notification=new SendPushNotification();    
                       if($avalale_star_details->device_type=='0')
                       {
                            //sending push notification star user.
                            $arr_push_message_android=array();
                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                            $arr_push_message_android['priority']="high";
                            $arr_push_message_android['sound']="default";
                            $arr_push_message_android['notification']=$arr_push_message;
                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));

                       }else{
                            $arr_push_message_ios['to']=$avalale_star_details->device_id;
                            $arr_push_message_ios['priority']="high";
                            $arr_push_message_ios['sound']="default";
                            $arr_push_message_ios['notification']=$arr_push_message;
                            $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                        }
                      }
                   }
                
              }
              
              //sending email
               $adminusers=UserInformation::where('user_type',1)->get();
               $adminusers=$adminusers->reject(function($user_details) use ($country_id)
               {
                   $country=0;
                    if(isset($user_details->user->userAddress))
                    {

                        foreach($user_details->user->userAddress as $address)
                        {
                              $country=$address->user_country;
                        }
                    }
                   if($country && $country!=0)
                   {
                      return (($country!=$country_id) ||($user_details->user->supervisor_id!=0));
                   }
                    if($user_details->user->hasRole('superadmin'))
                    {
                        return true;
                    }
                   
               });
                $agentusers = UserInformation::where('user_type', 4)->get();
                        $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                            $country = 0;
                            if (isset($user_details->user->userAddress)) {

                                foreach ($user_details->user->userAddress as $address) {
                                    $country = $address->user_country;
                                }
                            }
                            if ($country && $country != 0) {
                                return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                            }
                        });
                        $companyusers = UserInformation::where('user_type', 5)->get();
                        $companyusers = $companyusers->reject(function($user_details) use ($country_id) {
                            $country = 0;
                            if (isset($user_details->user->userAddress)) {

                                foreach ($user_details->user->userAddress as $address) {
                                    $country = $address->user_country;
                                }
                            }
                            if ($country && $country != 0) {
                                return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                            }
                        });
               $site_email=GlobalValues::get('site-email');
               $site_title=GlobalValues::get('site-title');
               //Assign values to all macros
                $arr_keyword_values['ORDER_NUMBER'] =  $order_details->order_unique_id;
                $arr_keyword_values['OLD_STAR'] =  $user_id;
                $arr_keyword_values['NEW_STAR'] =  $avalale_driver_id;
                $arr_keyword_values['ORDER_ID'] =  $order_details->id;
                $arr_keyword_values['SITE_TITLE'] = $site_title;
               $email_template_title="emailtemplate::reassign-order-to-a-star-previous-assigned-rejected-".$locale;    
               $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
              if(count($adminusers)>0)
              {
                  foreach($adminusers as $admin)
                  { 
                      
                      Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                        if(isset($admin->user->email))
                        {
                            $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                        }

                    });
                 }
              }
              if (count($agentusers) > 0) {
                        foreach ($agentusers as $agent) {

                            Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                if (isset($agent->user->email)) {
                                    $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                }
                            });
                        }
                    }
                    if (count($companyusers) > 0) {
                        foreach ($companyusers as $company) {

                            Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $company, $site_email, $site_title) {
                                if (isset($company->user->email)) {
                                    $message->to($company->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                }
                            });
                        }
                    }
              //sending email to site admin              
               Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                        if(isset($site_email))
                        {
                            $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                        }

               });
                 
                 
             }else{
                  //sending email
               $adminusers=UserInformation::where('user_type',1)->get();
               $adminusers=$adminusers->reject(function($user_details) use ($country_id)
               {
                   $country=0;
                    if(isset($user_details->user->userAddress))
                    {

                        foreach($user_details->user->userAddress as $address)
                        {
                              $country=$address->user_country;
                        }
                    }
                   if($country!='' && $country!=0)
                   {
                      return (($country!=$country_id));
                   }
                    if($user_details->user->hasRole('superadmin'))
                    {
                        return true;
                    }
                   
               });
                $agentusers = UserInformation::where('user_type', 4)->get();
                $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                    $country = 0;
                    if (isset($user_details->user->userAddress)) {

                        foreach ($user_details->user->userAddress as $address) {
                            $country = $address->user_country;
                        }
                    }
                    if ($country && $country != 0) {
                        return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                    }
                });
                       
                 //sending email about rejection and assign some one to this order.
                $site_email=GlobalValues::get('site-email');
                $site_title=GlobalValues::get('site-title');
               //Assign values to all macros
                $arr_keyword_values['ORDER_NUMBER'] =  $order_details->order_unique_id;
                $arr_keyword_values['OLD_STAR'] =  $user_id;
                $arr_keyword_values['ORDER_ID'] =  $order_details->id;
                $arr_keyword_values['SITE_TITLE'] = $site_title;
               $email_template_title="emailtemplate::star-rejected_an-order-no-star-available-".$locale;    
               $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
              if(count($adminusers)>0)
              {
                  foreach($adminusers as $admin)
                  { 
                      
                      Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                        if(isset($admin->user->email))
                        {
                            $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                        }

                    });
                 }
              }
              if (count($agentusers) > 0) {
                        foreach ($agentusers as $agent) {

                            Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                if (isset($agent->user->email)) {
                                    $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                }
                            });
                        }
                    }
              
             }
                
                
             $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_has_been_rejected',$locale));       
                
            }else{
                $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.reject_not_allowed',$locale));    
            }
            
             
           }else{
               $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.reject_not_allowed',$locale));
           }
         }else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.reject_not_allowed',$locale));
        }
      return response()->json($arr_to_return);
    }
    
    public function rejectDeliveryuserAndReassign(Request $request){        //
        $reason_text =  isset($request['reason'])?$request['reason']:'0';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $radious=GlobalValues::get('star-range-radious');
        $star_reject_time=GlobalValues::get('star-reject-time');
        $flag_available=0;
        $avalale_driver_id=0;
        $country_id=0;
        $user_id=0;
        //get all order which are pending
        $all_orders=Order::where('status','0')->where('order_type','1')->get();
    
        if(count($all_orders)>0)
        {
            foreach($all_orders as $order_detail)
            {
                $countryInfo=Country::where('id',$order_detail->country_id)->first();
                $country_id=$countryInfo->id;
                $service_id_radious=$order_detail->service_id;
                $service_details_radious=Service::where('id',$service_id_radious)->first();
                if(isset($service_details_radious->categoryInfo->request_range) && (($service_details_radious->categoryInfo->request_range)>0))
                {
                    $radious=$service_details_radious->categoryInfo->request_range;
                }
                if($order_detail->service_id!=20 && $order_detail->service_id!=28)
                {
                 $order_notification=OrderNotification::where('order_id',$order_detail->id)->first();
               
                 if(count($order_notification)>0)
                 {
                     
                    $user_id=$order_notification->user_id;
                   
                     $dt = new DateTime(date('Y-m-d H:i:s'));
          
                    //get timezone as per country
                    
                     if(count($countryInfo)>0)
                     {
                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                         $dt->setTimezone($tz);
                     }
                     
                     $date2_val= $dt->format('Y-m-d H:i:s'); 
                  
                     $date2=new DateTime($date2_val);
                     $date1=new DateTime($order_notification->created_at);
                     $diffdate=date_diff($date2,$date1);
                     $star_reject_time_sec=($star_reject_time*60)-10;
                   

                    if((($diffdate->h)>0) || (($diffdate->i)>=$star_reject_time))
                    {
                        
                        if(isset($order_notification->id))
                         { 
                           // rejecting order by star
                         
                          $country_id=$order_detail->country_id;
                          if($order_detail->status=='0')
                          {
                              //$order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
                             
                              $order_cancellation_status=array();
                              $order_cancellation_status['order_id']=$order_detail->id;
                              $order_cancellation_status['user_id']=$user_id;
                              $order_cancellation_status['reason_text']="No response-cron";
                              //storing cancel reason
                              OrderCancelationDetail::create($order_cancellation_status);
                             
                               $order_notification->delete();
                              //checking for other user star to send notification 
                              $arrServiceUsers=UserServiceInformation::where('service_id',$order_detail->service_id)->get();
                              $arrServiceUsers=$arrServiceUsers->reject(function ($userInfo)
                              {
                                 if(isset($userInfo->user->driverUserInformation->availability))
                                  return ($userInfo->user->driverUserInformation->availability==0);

                              });
                                //get all user who has only 50 km range
                               if(count($arrServiceUsers)>0)
                                {
                                    $user_ids="0";
                                    $arrayUserIds=array();
                                   
                                    foreach($arrServiceUsers as $users_ids)
                                     {
                                         if(isset($users_ids->user_id) && ($users_ids->user_id!=0) && ($users_ids->user_id!=$user_id) && ($order_detail->getOrderTransInformation->distance<=$users_ids->goe_fence_area))
                                         {
                                             $user_ids.=",$users_ids->user_id";
                                             $arrayUserIds[]=$users_ids->user_id;
                                         }
                                     }
                                   
                                     $pick_up_lat=$order_detail->getOrderTransInformation->selected_pickup_lat;
                                     $pick_up_long=$order_detail->getOrderTransInformation->selected_pickup_long;
                                     //
                                     $users=array();
                                    if($pick_up_lat!='' && $pick_up_long!='') 
                                    {
                                      $users=  DB::select("call getUserByDistance(".$pick_up_lat.",".$pick_up_long.",'".$user_ids."',".$radious.")");
                                    }
                                   
                                    //check if a user is having any active orders
                                    if(count($users)>0)
                                    {
                                        $j=0;
                                        foreach($users as $user)
                                        {
                                             if(in_array($user->user_id,$arrayUserIds))
                                          {
                                           $userDetailsStatus=UserInformation::where('user_id',$user->user_id)->first();
                                           if($userDetailsStatus->user_status=='1' && $userDetailsStatus->user_type=='2') 
                                          {
                                            $userData=Order::where('driver_id',$user->user_id)->where('status','1')->first();
                                            $order_cancel=OrderCancelationDetail::where('order_id',$order_detail->id)->where('user_id',$user->user_id)->first();
                                            $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                            if(count($userData)<=0 && count($order_cancel)<=0 && count($order_notification_count)<=0)
                                            {
                                              
                                                $order_assigend= OrderAssignedDetail::where('user_id',$user->user_id)->where('order_id',$order_detail->id)->first(); 
                                               if(count($order_assigend)<=0)
                                               {
                                                $flag_available=1;
                                                $avalale_driver_id=$user->user_id;
                                                break;
                                               }
                                            }
                                          }
                                         $j++; 
                                        }}
                                    }
                             }

                             if($flag_available==1 && $avalale_driver_id>0)
                           {
                               //sending email about rejection only and assign star user an order.
                              if($avalale_driver_id!='')
                             {
                                $order_notification_count_chk=OrderNotification::where('order_id',$order_detail->id)->first();
                                if(count($order_notification_count_chk)<=0)
                                {
                                //  $countryInfo=Country::where('id',$country_id)->first();
                                    $dt = new DateTime(date('Y-m-d H:i:s'));
                                    if(count($countryInfo)>0)
                                    {
                                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                         $dt->setTimezone($tz);
                                    }
                                    
                                    $date2_val= $dt->format('Y-m-d H:i:s'); 
                                     $date2=new DateTime($date2_val);
                                  //storing that user in notification table.
                                  $arrOrderNotificationDetails['order_id']=$order_detail->id;
                                  $arrOrderNotificationDetails['user_id']=$avalale_driver_id;
                                  $arrOrderNotificationDetails['created_at']=$date2;
                                  $arrOrderNotificationDetails['updated_at']=$date2;
                                  $arrOrderNotificationDetails['message']=$order_detail->order_unique_id. " ".Lang::choice('messages.order_assigned',$locale);
                                  OrderNotification::create($arrOrderNotificationDetails);

                                  $avalale_star_details=UserInformation::where('user_id',$avalale_driver_id)->first();
                                 if(isset($avalale_star_details->user_id))
                                 {
                                     $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star',$locale),"flag"=>'order_post','message'=>Lang::choice('messages.order_assign_star',$locale),'order_id'=>$order_detail->id);
                                     $arr_push_message_ios=array();
                                    if(isset($avalale_star_details->device_id)&& $avalale_star_details->device_id!='')
                                    {
                                      $obj_send_push_notification=new SendPushNotification();    
                                     if($avalale_star_details->device_type=='0')
                                     {
                                      //sending push notification star user.
                                            $arr_push_message_android=array();
                                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                                            $arr_push_message_android['priority']="high";
                                            $arr_push_message_android['sound']="default";
                                            $arr_push_message_android['notification']=$arr_push_message;
                                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));

                                     }else{
                                            $arr_push_message_ios['to']=$avalale_star_details->device_id;
                                            $arr_push_message_ios['priority']="high";
                                            $arr_push_message_ios['sound']="default";
                                            $arr_push_message_ios['notification']=$arr_push_message;
                                            $this->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                         }
                                    }
                                 }

                             }}

                            //sending email
                             $adminusers=UserInformation::where('user_type',1)->get();
                             $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                             {
                                 $country=0;
                                  if(isset($user_details->user->userAddress))
                                  {

                                      foreach($user_details->user->userAddress as $address)
                                      {
                                            $country=$address->user_country;
                                      }
                                  }
                                 if($country!='' && $country!=0)
                                 {
                                    return (($country!=$country_id));
                                 }
                                  if($user_details->user->hasRole('superadmin'))
                                {
                                    return true;
                                }

                             });
                              $agentusers = UserInformation::where('user_type', 4)->get();
                                      $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                                      $companyusers = UserInformation::where('user_type', 5)->get();
                                      $companyusers = $companyusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                             $site_email=GlobalValues::get('site-email');
                             $site_title=GlobalValues::get('site-title');
                             //Assign values to all macros
                              $arr_keyword_values['ORDER_NUMBER'] =  $order_detail->order_unique_id;
                              $arr_keyword_values['OLD_STAR'] =  $user_id;
                              $arr_keyword_values['NEW_STAR'] =  $avalale_driver_id;
                              $arr_keyword_values['ORDER_ID'] =  $order_detail->id;
                              $arr_keyword_values['SITE_TITLE'] = $site_title;
                             $email_template_title="emailtemplate::reassign-order-to-a-star-previous-assigned-expired-".$locale;    
                             $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
                            if(count($adminusers)>0)
                            {
                                foreach($adminusers as $admin)
                                { 

                                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                                      if(isset($admin->user->email))
                                      {
                                          $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                                  });
                               }
                            }
                            if (count($agentusers) > 0) {
                                      foreach ($agentusers as $agent) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                              if (isset($agent->user->email)) {
                                                  $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                                  if (count($companyusers) > 0) {
                                      foreach ($companyusers as $company) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $company, $site_email, $site_title) {
                                              if (isset($company->user->email)) {
                                                  $message->to($company->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                            //sending email to site admin              
                             Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                                      if(isset($site_email))
                                      {
                                          $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                             });


                           }else{
                           //sending email
                             $adminusers=UserInformation::where('user_type',1)->get();
                             $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                             {
                                 $country=0;
                                  if(isset($user_details->user->userAddress))
                                  {

                                      foreach($user_details->user->userAddress as $address)
                                      {
                                            $country=$address->user_country;
                                      }
                                  }
                                 if($country!='' && $country!=0)
                                 {
                                    return (($country!=$country_id));
                                 }
                                  if($user_details->user->hasRole('superadmin'))
                                {
                                    return true;
                                }

                             });
                              $agentusers = UserInformation::where('user_type', 4)->get();
                                      $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                                    
                               //sending email about rejection and assign some one to this order.
                              $site_email=GlobalValues::get('site-email');
                              $site_title=GlobalValues::get('site-title');
                             //Assign values to all macros
                              $arr_keyword_values['ORDER_NUMBER'] =  $order_detail->order_unique_id;
                              $arr_keyword_values['OLD_STAR'] =  $user_id;
                              $arr_keyword_values['ORDER_ID'] =  $order_detail->id;
                              $arr_keyword_values['SITE_TITLE'] = $site_title;
                             $email_template_title="emailtemplate::star-rejected_an-order-no-star-available-".$locale;    
                             $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
                            if(count($adminusers)>0)
                            {
                                foreach($adminusers as $admin)
                                { 

                                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                                      if(isset($admin->user->email))
                                      {
                                          $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                                  });
                               }
                            }
                            if (count($agentusers) > 0) {
                                      foreach ($agentusers as $agent) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                              if (isset($agent->user->email)) {
                                                  $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                                 
                            
                           }


                          // $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_has_been_rejected',$locale));       

                          }
                         }
                    }
                 }else{
                  if($order_detail->status=='0')
                     {
                              
                              //checking for other user star to send notification 
                              $arrServiceUsers=UserServiceInformation::where('service_id',$order_detail->service_id)->get();
                              $arrServiceUsers=$arrServiceUsers->reject(function ($userInfo)
                              {
                                 if(isset($userInfo->user->driverUserInformation->availability))
                                  return ($userInfo->user->driverUserInformation->availability==0);

                              });
                                //get all user who has only 50 km range
                               if(count($arrServiceUsers)>0)
                                {
                                    $user_ids="0";
                                    $arrayUserIds=array();
                                    foreach($arrServiceUsers as $users_ids)
                                     {
                                         if(isset($users_ids->user_id) && ($users_ids->user_id!=0) && ($users_ids->user_id!=$user_id) && ($order_detail->getOrderTransInformation->distance<=$users_ids->goe_fence_area))
                                         {
                                             $user_ids.=",$users_ids->user_id";
                                             $arrayUserIds[]=$users_ids->user_id;
                                         }
                                     }
                                   
                                     $pick_up_lat=$order_detail->getOrderTransInformation->selected_pickup_lat;
                                     $pick_up_long=$order_detail->getOrderTransInformation->selected_pickup_long;
                                     //
                                     $users=array();
                                    if($pick_up_lat!='' && $pick_up_long!='') 
                                    {
                                      $users=  DB::select("call getUserByDistance(".$pick_up_lat.",".$pick_up_long.",'".$user_ids."',".$radious.")");
                                    }
                                   
                                    //check if a user is having any active orders
                                    if(count($users)>0)
                                    {
                                        $j=0;
                                        foreach($users as $user)
                                        {
                                              if(in_array($user->user_id,$arrayUserIds))
                                          {
                                           $userDetailsStatus=UserInformation::where('user_id',$user->user_id)->first();
                                           if($userDetailsStatus->user_status=='1' && $userDetailsStatus->user_type=='2') 
                                          {
                                            $userData=Order::where('driver_id',$user->user_id)->where('status','1')->first();
                                            $order_cancel=OrderCancelationDetail::where('order_id',$order_detail->id)->where('user_id',$user->user_id)->first();
                                            $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                            if(count($userData)<=0 && count($order_cancel)<=0 && count($order_notification_count)<=0)
                                            {
                                            
                                               $order_assigend= OrderAssignedDetail::where('user_id',$user->user_id)->where('order_id',$order_detail->id)->first(); 
                                             
                                              if(count($order_assigend)<=0)
                                              {
                                                    $flag_available=1;
                                                    $avalale_driver_id=$user->user_id;
                                                    break;
                                              }else{
                                                  continue;
                                              }
                                            }
                                          }
                                         $j++; 
                                        }}
                                    }
                             }
                             
                             if($flag_available==1 && $avalale_driver_id>0)
                           {
                                 $order_notification_count_chk=OrderNotification::where('order_id',$order_detail->id)->first();
                               
                               //sending email about rejection only and assign star user an order.
                              if($avalale_driver_id!='' && count($order_notification_count_chk)<=0)
                             {
                                  
                                //  $countryInfo=Country::where('id',$country_id)->first();
                                    $dt = new DateTime(date('Y-m-d H:i:s'));
                                    if(count($countryInfo)>0)
                                    {
                                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                         $dt->setTimezone($tz);
                                    }

                                    $date2_val= $dt->format('Y-m-d H:i:s'); 
                                    $date2=new DateTime($date2_val);
                                  //storing that user in notification table.
                                  $arrOrderNotificationDetails['order_id']=$order_detail->id;
                                  $arrOrderNotificationDetails['user_id']=$avalale_driver_id;
                                  $arrOrderNotificationDetails['created_at']=$date2;
                                  $arrOrderNotificationDetails['updated_at']=$date2;
                                  $arrOrderNotificationDetails['message']=$order_detail->order_unique_id. " ".Lang::choice('messages.order_assigned',$locale);
                                  OrderNotification::create($arrOrderNotificationDetails);
                                  
                                  $order_assigned_status=array();
                                $order_assigned_status['order_id']=$order_detail->id;
                                $order_assigned_status['user_id']=$avalale_driver_id;
                                $order_assigned_status['reason_text']="Assigned to this user";
                                //storing cancel reason
                                 OrderAssignedDetail::create($order_assigned_status);
                                  $avalale_star_details=UserInformation::where('user_id',$avalale_driver_id)->first();
                                 if(isset($avalale_star_details->user_id))
                                 {
                                     $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star',$locale),"flag"=>'order_post','message'=>Lang::choice('messages.order_assign_star',$locale),'order_id'=>$order_detail->id);
                                     $arr_push_message_ios=array();
                                    if(isset($avalale_star_details->device_id)&& $avalale_star_details->device_id!='')
                                    {
                                      $obj_send_push_notification=new SendPushNotification();    
                                     if($avalale_star_details->device_type=='0')
                                     {
                                            $arr_push_message_android=array();
                                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                                            $arr_push_message_android['priority']="high";
                                            $arr_push_message_android['sound']="default";
                                            $arr_push_message_android['notification']=$arr_push_message;
                                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                                           
                                     }else{
                                            $arr_push_message_ios['to']=$avalale_star_details->device_id;
                                            $arr_push_message_ios['priority']="high";
                                            $arr_push_message_ios['sound']="default";
                                            $arr_push_message_ios['notification']=$arr_push_message;
                                            $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                     }
                                    }
                                 }

                            }

                            //sending email
                             $adminusers=UserInformation::where('user_type',1)->get();
                             $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                             {
                                 $country=0;
                                  if(isset($user_details->user->userAddress))
                                  {

                                      foreach($user_details->user->userAddress as $address)
                                      {
                                            $country=$address->user_country;
                                      }
                                  }
                                 if($country!='' && $country!=0)
                                 {
                                    return (($country!=$country_id));
                                 }
                                  if($user_details->user->hasRole('superadmin'))
                                {
                                    return true;
                                }

                             });
                              $agentusers = UserInformation::where('user_type', 4)->get();
                                      $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                                      $companyusers = UserInformation::where('user_type', 5)->get();
                                      $companyusers = $companyusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                             $site_email=GlobalValues::get('site-email');
                             $site_title=GlobalValues::get('site-title');
                             //Assign values to all macros
                              $arr_keyword_values['ORDER_NUMBER'] =  $order_detail->order_unique_id;
                              $arr_keyword_values['OLD_STAR'] =  $user_id;
                              $arr_keyword_values['NEW_STAR'] =  $avalale_driver_id;
                              $arr_keyword_values['ORDER_ID'] =  $order_detail->id;
                              $arr_keyword_values['SITE_TITLE'] = $site_title;
                             $email_template_title="emailtemplate::reassign-order-to-a-star-previous-assigned-expired-".$locale;    
                             $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
                            if(count($adminusers)>0)
                            {
                                foreach($adminusers as $admin)
                                { 

                                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                                      if(isset($admin->user->email))
                                      {
                                          $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                                  });
                               }
                            }
                            if (count($agentusers) > 0) {
                                      foreach ($agentusers as $agent) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                              if (isset($agent->user->email)) {
                                                  $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                                  if (count($companyusers) > 0) {
                                      foreach ($companyusers as $company) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $company, $site_email, $site_title) {
                                              if (isset($company->user->email)) {
                                                  $message->to($company->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                            //sending email to site admin              
                             Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                                      if(isset($site_email))
                                      {
                                          $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                             });


                           }


                          // $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_has_been_rejected',$locale));       

                          }
                     
                 }
                }
            }
     
    }}
    
    public function rejectDeliveryuserAndReassignScheduled(Request $request){        //
        $reason_text =  isset($request['reason'])?$request['reason']:'0';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        $radious=GlobalValues::get('star-range-radious');
        $star_reject_time=GlobalValues::get('star-reject-time');
        $flag_available=0;
        $avalale_driver_id=0;
        $country_id=0;
        $user_id=0;
        //get all order which are pending
        $all_orders=Order::where('status','0')->where('order_type','2')->where('is_cron_execute','1')->get();
    
        if(count($all_orders)>0)
        {
            foreach($all_orders as $order_detail)
            {
                $countryInfo=Country::where('id',$order_detail->country_id)->first();
                $country_id=$countryInfo->id;
                $service_id_radious=$order_detail->service_id;
                $service_details_radious=Service::where('id',$service_id_radious)->first();
                if(isset($service_details_radious->categoryInfo->request_range) && (($service_details_radious->categoryInfo->request_range)>0))
                {
                    $radious=$service_details_radious->categoryInfo->request_range;
                }
                if($order_detail->service_id!=20 && $order_detail->service_id!=28)
                {
                 $order_notification=OrderNotification::where('order_id',$order_detail->id)->first();
               
                 if(count($order_notification)>0)
                 {
                     
                    $user_id=$order_notification->user_id;
                   
                     $dt = new DateTime(date('Y-m-d H:i:s'));
          
                    //get timezone as per country
                    
                     if(count($countryInfo)>0)
                     {
                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                         $dt->setTimezone($tz);
                     }
                     
                     $date2_val= $dt->format('Y-m-d H:i:s'); 
                  
                     $date2=new DateTime($date2_val);
                     $date1=new DateTime($order_notification->created_at);
                     $diffdate=date_diff($date2,$date1);
                     $star_reject_time_sec=($star_reject_time*60)-10;
                   

                    if((($diffdate->h)>0) || (($diffdate->i)>=$star_reject_time))
                    {
                        
                        if(isset($order_notification->id))
                         { 
                           // rejecting order by star
                         
                          $country_id=$order_detail->country_id;
                          if($order_detail->status=='0')
                          {
                              //$order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
                             
                              $order_cancellation_status=array();
                              $order_cancellation_status['order_id']=$order_detail->id;
                              $order_cancellation_status['user_id']=$user_id;
                              $order_cancellation_status['reason_text']="No response-cron";
                              //storing cancel reason
                              OrderCancelationDetail::create($order_cancellation_status);
                             
                               $order_notification->delete();
                              //checking for other user star to send notification 
                              $arrServiceUsers=UserServiceInformation::where('service_id',$order_detail->service_id)->get();
                              $arrServiceUsers=$arrServiceUsers->reject(function ($userInfo)
                              {
                                 if(isset($userInfo->user->driverUserInformation->availability))
                                  return ($userInfo->user->driverUserInformation->availability==0);

                              });
                                //get all user who has only 50 km range
                               if(count($arrServiceUsers)>0)
                                {
                                    $user_ids="0";
                                    $arrayUserIds=array();
                                   
                                    foreach($arrServiceUsers as $users_ids)
                                     {
                                         if(isset($users_ids->user_id) && ($users_ids->user_id!=0) && ($users_ids->user_id!=$user_id) && ($order_detail->getOrderTransInformation->distance<=$users_ids->goe_fence_area))
                                         {
                                             $user_ids.=",$users_ids->user_id";
                                             $arrayUserIds[]=$users_ids->user_id;
                                         }
                                     }
                                   
                                     $pick_up_lat=$order_detail->getOrderTransInformation->selected_pickup_lat;
                                     $pick_up_long=$order_detail->getOrderTransInformation->selected_pickup_long;
                                     //
                                     $users=array();
                                    if($pick_up_lat!='' && $pick_up_long!='') 
                                    {
                                      $users=  DB::select("call getUserByDistance(".$pick_up_lat.",".$pick_up_long.",'".$user_ids."',".$radious.")");
                                    }
                                   
                                    //check if a user is having any active orders
                                    if(count($users)>0)
                                    {
                                        $j=0;
                                        foreach($users as $user)
                                        {
                                             if(in_array($user->user_id,$arrayUserIds))
                                          {
                                           $userDetailsStatus=UserInformation::where('user_id',$user->user_id)->first();
                                           if($userDetailsStatus->user_status=='1' && $userDetailsStatus->user_type=='2') 
                                          {
                                            $userData=Order::where('driver_id',$user->user_id)->where('status','1')->first();
                                            $order_cancel=OrderCancelationDetail::where('order_id',$order_detail->id)->where('user_id',$user->user_id)->first();
                                            $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                            if(count($userData)<=0 && count($order_cancel)<=0 && count($order_notification_count)<=0)
                                            {
                                              
                                                $order_assigend= OrderAssignedDetail::where('user_id',$user->user_id)->where('order_id',$order_detail->id)->first(); 
                                               if(count($order_assigend)<=0)
                                               {
                                                $flag_available=1;
                                                $avalale_driver_id=$user->user_id;
                                                break;
                                               }
                                            }
                                          }
                                         $j++; 
                                        }}
                                    }
                             }

                             if($flag_available==1 && $avalale_driver_id>0)
                           {
                               //sending email about rejection only and assign star user an order.
                              if($avalale_driver_id!='')
                             {
                                $order_notification_count_chk=OrderNotification::where('order_id',$order_detail->id)->first();
                                if(count($order_notification_count_chk)<=0)
                                {
                                //  $countryInfo=Country::where('id',$country_id)->first();
                                    $dt = new DateTime(date('Y-m-d H:i:s'));
                                    if(count($countryInfo)>0)
                                    {
                                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                         $dt->setTimezone($tz);
                                    }
                                    
                                    $date2_val= $dt->format('Y-m-d H:i:s'); 
                                     $date2=new DateTime($date2_val);
                                  //storing that user in notification table.
                                  $arrOrderNotificationDetails['order_id']=$order_detail->id;
                                  $arrOrderNotificationDetails['user_id']=$avalale_driver_id;
                                  $arrOrderNotificationDetails['created_at']=$date2;
                                  $arrOrderNotificationDetails['updated_at']=$date2;
                                  $arrOrderNotificationDetails['message']=$order_detail->order_unique_id. " ".Lang::choice('messages.order_assigned',$locale);
                                  OrderNotification::create($arrOrderNotificationDetails);

                                  $avalale_star_details=UserInformation::where('user_id',$avalale_driver_id)->first();
                                 if(isset($avalale_star_details->user_id))
                                 {
                                     $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star',$locale),"flag"=>'order_post','message'=>Lang::choice('messages.order_assign_star',$locale),'order_id'=>$order_detail->id);
                                     $arr_push_message_ios=array();
                                    if(isset($avalale_star_details->device_id)&& $avalale_star_details->device_id!='')
                                    {
                                      $obj_send_push_notification=new SendPushNotification();    
                                     if($avalale_star_details->device_type=='0')
                                     {
                                      //sending push notification star user.
                                            $arr_push_message_android=array();
                                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                                            $arr_push_message_android['priority']="high";
                                            $arr_push_message_android['sound']="default";
                                            $arr_push_message_android['notification']=$arr_push_message;
                                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));

                                     }else{
                                            $arr_push_message_ios['to']=$avalale_star_details->device_id;
                                            $arr_push_message_ios['priority']="high";
                                            $arr_push_message_ios['sound']="default";
                                            $arr_push_message_ios['notification']=$arr_push_message;
                                            $this->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                         }
                                    }
                                 }

                             }}

                            //sending email
                             $adminusers=UserInformation::where('user_type',1)->get();
                             $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                             {
                                 $country=0;
                                  if(isset($user_details->user->userAddress))
                                  {

                                      foreach($user_details->user->userAddress as $address)
                                      {
                                            $country=$address->user_country;
                                      }
                                  }
                                 if($country!='' && $country!=0)
                                 {
                                    return (($country!=$country_id));
                                 }
                                  if($user_details->user->hasRole('superadmin'))
                                {
                                    return true;
                                }

                             });
                              $agentusers = UserInformation::where('user_type', 4)->get();
                                      $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                                      $companyusers = UserInformation::where('user_type', 5)->get();
                                      $companyusers = $companyusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                             $site_email=GlobalValues::get('site-email');
                             $site_title=GlobalValues::get('site-title');
                             //Assign values to all macros
                              $arr_keyword_values['ORDER_NUMBER'] =  $order_detail->order_unique_id;
                              $arr_keyword_values['OLD_STAR'] =  $user_id;
                              $arr_keyword_values['NEW_STAR'] =  $avalale_driver_id;
                              $arr_keyword_values['ORDER_ID'] =  $order_detail->id;
                              $arr_keyword_values['SITE_TITLE'] = $site_title;
                             $email_template_title="emailtemplate::reassign-order-to-a-star-previous-assigned-expired-".$locale;    
                             $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
                            if(count($adminusers)>0)
                            {
                                foreach($adminusers as $admin)
                                { 

                                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                                      if(isset($admin->user->email))
                                      {
                                          $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                                  });
                               }
                            }
                            if (count($agentusers) > 0) {
                                      foreach ($agentusers as $agent) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                              if (isset($agent->user->email)) {
                                                  $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                                  if (count($companyusers) > 0) {
                                      foreach ($companyusers as $company) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $company, $site_email, $site_title) {
                                              if (isset($company->user->email)) {
                                                  $message->to($company->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                            //sending email to site admin              
                             Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                                      if(isset($site_email))
                                      {
                                          $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                             });


                           }else{
                           //sending email
                             $adminusers=UserInformation::where('user_type',1)->get();
                             $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                             {
                                 $country=0;
                                  if(isset($user_details->user->userAddress))
                                  {

                                      foreach($user_details->user->userAddress as $address)
                                      {
                                            $country=$address->user_country;
                                      }
                                  }
                                 if($country!='' && $country!=0)
                                 {
                                    return (($country!=$country_id));
                                 }
                                  if($user_details->user->hasRole('superadmin'))
                                {
                                    return true;
                                }

                             });
                              $agentusers = UserInformation::where('user_type', 4)->get();
                                      $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                                    
                               //sending email about rejection and assign some one to this order.
                              $site_email=GlobalValues::get('site-email');
                              $site_title=GlobalValues::get('site-title');
                             //Assign values to all macros
                              $arr_keyword_values['ORDER_NUMBER'] =  $order_detail->order_unique_id;
                              $arr_keyword_values['OLD_STAR'] =  $user_id;
                              $arr_keyword_values['ORDER_ID'] =  $order_detail->id;
                              $arr_keyword_values['SITE_TITLE'] = $site_title;
                             $email_template_title="emailtemplate::star-rejected_an-order-no-star-available-".$locale;    
                             $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
                            if(count($adminusers)>0)
                            {
                                foreach($adminusers as $admin)
                                { 

                                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                                      if(isset($admin->user->email))
                                      {
                                          $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                                  });
                               }
                            }
                            if (count($agentusers) > 0) {
                                      foreach ($agentusers as $agent) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                              if (isset($agent->user->email)) {
                                                  $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                                 
                            
                           }


                          // $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_has_been_rejected',$locale));       

                          }
                         }
                    }
                 }else{
                  if($order_detail->status=='0')
                     {
                              
                              //checking for other user star to send notification 
                              $arrServiceUsers=UserServiceInformation::where('service_id',$order_detail->service_id)->get();
                              $arrServiceUsers=$arrServiceUsers->reject(function ($userInfo)
                              {
                                 if(isset($userInfo->user->driverUserInformation->availability))
                                  return ($userInfo->user->driverUserInformation->availability==0);

                              });
                                //get all user who has only 50 km range
                               if(count($arrServiceUsers)>0)
                                {
                                    $user_ids="0";
                                    $arrayUserIds=array();
                                    foreach($arrServiceUsers as $users_ids)
                                     {
                                         if(isset($users_ids->user_id) && ($users_ids->user_id!=0) && ($users_ids->user_id!=$user_id) && ($order_detail->getOrderTransInformation->distance<=$users_ids->goe_fence_area))
                                         {
                                             $user_ids.=",$users_ids->user_id";
                                             $arrayUserIds[]=$users_ids->user_id;
                                         }
                                     }
                                   
                                     $pick_up_lat=$order_detail->getOrderTransInformation->selected_pickup_lat;
                                     $pick_up_long=$order_detail->getOrderTransInformation->selected_pickup_long;
                                     //
                                     $users=array();
                                    if($pick_up_lat!='' && $pick_up_long!='') 
                                    {
                                      $users=  DB::select("call getUserByDistance(".$pick_up_lat.",".$pick_up_long.",'".$user_ids."',".$radious.")");
                                    }
                                   
                                    //check if a user is having any active orders
                                    if(count($users)>0)
                                    {
                                        $j=0;
                                        foreach($users as $user)
                                        {
                                              if(in_array($user->user_id,$arrayUserIds))
                                          {
                                           $userDetailsStatus=UserInformation::where('user_id',$user->user_id)->first();
                                           if($userDetailsStatus->user_status=='1' && $userDetailsStatus->user_type=='2') 
                                          {
                                            $userData=Order::where('driver_id',$user->user_id)->where('status','1')->first();
                                            $order_cancel=OrderCancelationDetail::where('order_id',$order_detail->id)->where('user_id',$user->user_id)->first();
                                            $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                            if(count($userData)<=0 && count($order_cancel)<=0 && count($order_notification_count)<=0)
                                            {
                                            
                                               $order_assigend= OrderAssignedDetail::where('user_id',$user->user_id)->where('order_id',$order_detail->id)->first(); 
                                             
                                              if(count($order_assigend)<=0)
                                              {
                                                    $flag_available=1;
                                                    $avalale_driver_id=$user->user_id;
                                                    break;
                                              }else{
                                                  continue;
                                              }
                                            }
                                          }
                                         $j++; 
                                        }}
                                    }
                             }
                             
                             if($flag_available==1 && $avalale_driver_id>0)
                           {
                                 $order_notification_count_chk=OrderNotification::where('order_id',$order_detail->id)->first();
                               
                               //sending email about rejection only and assign star user an order.
                              if($avalale_driver_id!='' && count($order_notification_count_chk)<=0)
                             {
                                  
                                //  $countryInfo=Country::where('id',$country_id)->first();
                                    $dt = new DateTime(date('Y-m-d H:i:s'));
                                    if(count($countryInfo)>0)
                                    {
                                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                         $dt->setTimezone($tz);
                                    }

                                    $date2_val= $dt->format('Y-m-d H:i:s'); 
                                    $date2=new DateTime($date2_val);
                                  //storing that user in notification table.
                                  $arrOrderNotificationDetails['order_id']=$order_detail->id;
                                  $arrOrderNotificationDetails['user_id']=$avalale_driver_id;
                                  $arrOrderNotificationDetails['created_at']=$date2;
                                  $arrOrderNotificationDetails['updated_at']=$date2;
                                  $arrOrderNotificationDetails['message']=$order_detail->order_unique_id. " ".Lang::choice('messages.order_assigned',$locale);
                                  OrderNotification::create($arrOrderNotificationDetails);
                                  
                                  $order_assigned_status=array();
                                $order_assigned_status['order_id']=$order_detail->id;
                                $order_assigned_status['user_id']=$avalale_driver_id;
                                $order_assigned_status['reason_text']="Assigned to this user";
                                //storing cancel reason
                                 OrderAssignedDetail::create($order_assigned_status);
                                  $avalale_star_details=UserInformation::where('user_id',$avalale_driver_id)->first();
                                 if(isset($avalale_star_details->user_id))
                                 {
                                     $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star',$locale),"flag"=>'order_post','message'=>Lang::choice('messages.order_assign_star',$locale),'order_id'=>$order_detail->id);
                                     $arr_push_message_ios=array();
                                    if(isset($avalale_star_details->device_id)&& $avalale_star_details->device_id!='')
                                    {
                                      $obj_send_push_notification=new SendPushNotification();    
                                     if($avalale_star_details->device_type=='0')
                                     {
                                            $arr_push_message_android=array();
                                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                                            $arr_push_message_android['priority']="high";
                                            $arr_push_message_android['sound']="default";
                                            $arr_push_message_android['notification']=$arr_push_message;
                                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                                           
                                     }else{
                                            $arr_push_message_ios['to']=$avalale_star_details->device_id;
                                            $arr_push_message_ios['priority']="high";
                                            $arr_push_message_ios['sound']="default";
                                            $arr_push_message_ios['notification']=$arr_push_message;
                                            $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                     }
                                    }
                                 }

                            }

                            //sending email
                             $adminusers=UserInformation::where('user_type',1)->get();
                             $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                             {
                                 $country=0;
                                  if(isset($user_details->user->userAddress))
                                  {

                                      foreach($user_details->user->userAddress as $address)
                                      {
                                            $country=$address->user_country;
                                      }
                                  }
                                 if($country!='' && $country!=0)
                                 {
                                    return (($country!=$country_id));
                                 }
                                  if($user_details->user->hasRole('superadmin'))
                                {
                                    return true;
                                }

                             });
                              $agentusers = UserInformation::where('user_type', 4)->get();
                                      $agentusers = $agentusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                                      $companyusers = UserInformation::where('user_type', 5)->get();
                                      $companyusers = $companyusers->reject(function($user_details) use ($country_id) {
                                          $country = 0;
                                          if (isset($user_details->user->userAddress)) {

                                              foreach ($user_details->user->userAddress as $address) {
                                                  $country = $address->user_country;
                                              }
                                          }
                                          if ($country!='' && $country != 0) {
                                              return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
                                          }
                                      });
                             $site_email=GlobalValues::get('site-email');
                             $site_title=GlobalValues::get('site-title');
                             //Assign values to all macros
                              $arr_keyword_values['ORDER_NUMBER'] =  $order_detail->order_unique_id;
                              $arr_keyword_values['OLD_STAR'] =  $user_id;
                              $arr_keyword_values['NEW_STAR'] =  $avalale_driver_id;
                              $arr_keyword_values['ORDER_ID'] =  $order_detail->id;
                              $arr_keyword_values['SITE_TITLE'] = $site_title;
                             $email_template_title="emailtemplate::reassign-order-to-a-star-previous-assigned-expired-".$locale;    
                             $email_template_subject=Lang::choice('messages.order_reassigned',$locale);
                            if(count($adminusers)>0)
                            {
                                foreach($adminusers as $admin)
                                { 

                                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                                      if(isset($admin->user->email))
                                      {
                                          $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                                  });
                               }
                            }
                            if (count($agentusers) > 0) {
                                      foreach ($agentusers as $agent) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
                                              if (isset($agent->user->email)) {
                                                  $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                                  if (count($companyusers) > 0) {
                                      foreach ($companyusers as $company) {

                                          Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $company, $site_email, $site_title) {
                                              if (isset($company->user->email)) {
                                                  $message->to($company->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                              }
                                          });
                                      }
                                  }
                            //sending email to site admin              
                             Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                                      if(isset($site_email))
                                      {
                                          $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                                      }

                             });


                           }


                          // $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.order_has_been_rejected',$locale));       

                          }
                     
                 }
                }
            }
     
    }}
    public function updateDeliveryuserStatus(Request $request){       
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $order_id =  isset($request['order_id'])?$request['order_id']:'0';
        $status =  isset($request['status'])?$request['status']:'';
        $current_latitude =  isset($request['current_latitude'])?$request['current_latitude']:'';
        $current_address =  isset($request['current_address'])?$request['current_address']:'';
        $current_longtitude =  isset($request['current_longtitude'])?$request['current_longtitude']:'';
        $payment_completed_by =  isset($request['payment_completed_by'])?$request['payment_completed_by']:'';
        $distance =  isset($request['distance'])?$request['distance']:'0';
        $additional_amount =  isset($request['additional_amount'])?$request['additional_amount']:'0';
        $additional_amount_desc =  isset($request['additional_amount_desc'])?$request['additional_amount_desc']:'';
         $locale =  isset($request['locale'])?$request['locale']:'en';
        $agent_percentage=GlobalValues::get('agent-percentage');
        $star_percentage=GlobalValues::get('star-percentage');
        $incentive_percentage=GlobalValues::get('incentive-percentage');
        $company_percentage=(100-($agent_percentage+$star_percentage));
        $admin_percentage=(100-($agent_percentage+$star_percentage));
        \App::setLocale($locale);
    
        if($order_id!='' && $status!='')
        {
            $order_details=Order::where('id',$order_id)->where('status','1')->first();
            $order_locale=isset($order_details->locale)?$order_details->locale:'en';
            $category_id_main=isset($order_details->getServicesDetails->category_id)?$order_details->getServicesDetails->category_id:'0';
            //Get category status details
            \App::setLocale($order_locale);
        
            $categorystatus=CategoryStatusMsg::where('category_id',$category_id_main)->where('status_value',$status)->where('locale',$order_locale)->first();
            $calculate_flag=isset($categorystatus->calculate_flag)?$categorystatus->calculate_flag:'';
            $status_text=isset($categorystatus->status_msg)?$categorystatus->status_msg:'';
           
           if((count($order_details)>0) && (count($categorystatus)>0))
           {
            //order end address updatation
               
            if($current_address!='')
            {
                $order_details->getOrderTransInformation->ride_end_address=$current_address;
                $order_details->getOrderTransInformation->ride_end_latitude=$current_latitude;
                $order_details->getOrderTransInformation->ride_end_longitude=$current_longtitude;
                $order_details->getOrderTransInformation->save();
            }
               
            //update status by star
            $order_details->status_by_star=$status;
            $order_details->save();
            if($calculate_flag=='1')
            {
                ///calculate the fare here
                $fare=0;
                $order_details->getOrderTransInformation->drop_lat=$current_latitude;
                $order_details->getOrderTransInformation->drop_long=$current_longtitude;
                $distance=(float)($distance);                
                $arrDataService=CountryServices::where('city_id',$order_details->city_id)->where('service_id',$order_details->service_id)->first();
                $distance_to_store=($distance);
                $distance=ceil($distance);
                $distance_value=(float)str_replace(" km","",$distance);
                
                if($distance_value>0)
                { 
                    $order_details->getOrderTransInformation->ride_final_distance=$distance_to_store;
                    $order_details->getOrderTransInformation->save();
                }
                if($distance_value!='')  
                {                   
                    if($arrDataService->price_type=='1')
                   {
                      $fare=(double)$arrDataService->base_price;
                   }else{
                         if ($distance_value > $arrDataService->base_km) {
                                 $fare= (double)$arrDataService->base_price;
                                 $extra_meter=(double)(($distance_value-$arrDataService->base_km)*10);
                                 $per_meter_price=($arrDataService->price_per_km)*($extra_meter);
                                 $fare=$fare + $per_meter_price;
                               
                             } else {
                                 $fare = (double) $arrDataService->base_price;
                               
                             }
                             //night charges                            
                       }
                       $current_time=date('H');
                       $night_time_from=isset($arrDataService->night_time_from)?$arrDataService->night_time_from:'12';
                       $night_time_to=isset($arrDataService->night_time_to)?$arrDataService->night_time_to:'5';
                       $fare_night=0;

                 if(((($current_time<$night_time_to) && ($current_time>=$night_time_from))||($current_time=='24')) && (($arrDataService->night_percentage)>0))
                       {
                         $fare_night=(double)((($fare)*$arrDataService->night_percentage)/100);
                       }

                       if($fare_night>0)
                       {
                           $fare+=$fare_night;
                       }

                       $fare=round($fare,0); 
                       
                }
            
                $order_details->total_amount=$fare;
                $order_details->save();
                $total_amount_additional1=0;
                 $total_amount_additional=$order_details->total_amount;
                 $total_amount_additional1=$total_amount_additional+$additional_amount;
                 $order_details->additional_amount=$additional_amount;
                 $order_details->additional_amount_desc=$additional_amount_desc;
                 $order_details->total_amount=$total_amount_additional1;
                 $order_details->save();
                $order_details->getOrderTransInformation->save();
            } 
            if($calculate_flag=='3')
            {
                $order_details->getOrderTransInformation->pickup_lat=$current_latitude;
                $order_details->getOrderTransInformation->pickup_long=$current_longtitude;
                $order_details->getOrderTransInformation->save();
            }
            else if($calculate_flag=='2' || $calculate_flag=='4')
            {
                
                $fare=0;
                $distance_to_store=$distance=(float)($distance);                
                $arrDataService=CountryServices::where('city_id',$order_details->city_id)->where('service_id',$order_details->service_id)->first();
                $distance=ceil($distance);
                
                $distance_value=(float)str_replace(" km","",$distance);
                if($distance_value>0)
                {
                    $order_details->getOrderTransInformation->ride_final_distance=$distance_to_store;
                    $order_details->getOrderTransInformation->save();
                }
				
                if($distance_value!='')  
                {
                                     
                    if($arrDataService->price_type=='1')
                   {
                      $fare=(double)$arrDataService->base_price;
                   }else{
                         if ($distance_value > $arrDataService->base_km) {
                                 $fare= (double)$arrDataService->base_price;
                                 $extra_meter=(double)(($distance_value-$arrDataService->base_km)*10);
                                 $per_meter_price=($arrDataService->price_per_km)*($extra_meter);
                                 $fare=$fare + $per_meter_price;
                               
                             } else {
                                 $fare = (double) $arrDataService->base_price;
                               
                             }
                             //night charges                            
                       }
                       $current_time=date('H');
                       $night_time_from=isset($arrDataService->night_time_from)?$arrDataService->night_time_from:'12';
                       $night_time_to=isset($arrDataService->night_time_to)?$arrDataService->night_time_to:'5';
                       $fare_night=0;

                 if(((($current_time<$night_time_to) && ($current_time>=$night_time_from))||($current_time=='24')) && (($arrDataService->night_percentage)>0))
                       {
                         $fare_night=(double)((($fare)*$arrDataService->night_percentage)/100);
                       }

                    if($fare_night>0)
                    {
                        $fare+=$fare_night;
                    }

                    $fare=round($fare,0);

                    
                }
                $order_details->total_amount=$fare;
                $order_details->save();
                if($order_details->payment_type==2 || $order_details->payment_type==3)
                {
                    $fare_amount=isset($order_details->total_amount)?$order_details->total_amount:$order_details->fare_amount;
                    $order_details->status=2;
                    $dt = new DateTime(date('Y-m-d H:i:s'));
          
                    //get timezone as per country
                    $countryInfo=Country::where('id',$order_details->country_id)->first();
                    if(count($countryInfo)>0)
                    {
                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                         $dt->setTimezone($tz);
                    }

                    $date1_val= $dt->format('Y-m-d H:i:s'); 
                    $date1=new DateTime($date1_val);
                    $order_details->order_complete_date_time=$date1;
                    $order_details->is_payment_done=1;
                    $order_details->save();
                    if($order_details->payment_type==3)
                    {
                       //ading pending amount for star to db;
                        $pendingAmount['user_id']=$user_id;
                        $pendingAmount['amount']=$fare_amount;
                        DriverPendingAmount::create($pendingAmount);
                        $commision_star=(($fare_amount)*($star_percentage/100));
                        $pending_amount=($fare_amount-$commision_star);
                        $walletAmountDetails=array();
                        $walletAmountDetails['user_id']=$user_id;
                        $walletAmountDetails['star_amount']=(($fare_amount)*($star_percentage/100));
                        $walletAmountDetails['total_amount']=(double)($fare_amount);
                        $walletAmountDetails['pay_type']='0';
                        $walletAmountDetails['star_payable_amt']=$pending_amount;
                        $walletAmountDetails['order_id']=$order_details->id;
                        DeliveryuserBalanceDetail::create($walletAmountDetails);
                        
                        //adding incentive 
                        if($incentive_percentage>0)
                        {
                            $commision_star1=(($fare_amount)*($incentive_percentage/100));
                            $pending_amount=($fare_amount-$commision_star1);
                            $walletAmountDetails=array();
                            $walletAmountDetails['user_id']=$user_id;
                            $walletAmountDetails['star_amount']=(($fare_amount)*($incentive_percentage/100));
                            $walletAmountDetails['total_amount']=0;
                            $walletAmountDetails['pay_type']='0';
                            $walletAmountDetails['is_incentive']='1';
                            $walletAmountDetails['star_payable_amt']=$pending_amount;
                            $walletAmountDetails['order_id']=$order_details->id;
                            DeliveryuserBalanceDetail::create($walletAmountDetails);
                            
                        }
                    }else if($order_details->payment_type==2)
                    {
                        //deducting order amount from mate account.
                        $mate_wallet_data = UserWalletDetail::where('user_id',$order_details->mate_id)->orderBy('id', 'desc')->first(['final_amout']);
                        $prev_mate_amount=isset($mate_wallet_data->final_amout)?$mate_wallet_data->final_amout:'0';
                        $order_information=isset($order_details->getOrderTransInformation)?$order_details->getOrderTransInformation:'';
                        $mate_amt_deduct=$fare_amount;
                        if(isset($order_information->fuel_amt) && ($order_information->fuel_amt!=''))
                        {
                          $mate_amt_deduct=$mate_amt_deduct+(float)($order_information->fuel_amt);
                        } 
                         if($prev_mate_amount>=$mate_amt_deduct)
                        {
                            $deducted_mate_amt=($prev_mate_amount-$fare_amount);
                            $walletAmountMate['user_id']=$order_details->mate_id;
                            $walletAmountMate['transaction_amount']=($fare_amount);
                            $walletAmountMate['final_amout']=(double)($deducted_mate_amt);
                            $walletAmountMate['trans_desc']=Lang::choice('messages.trans_desc_mate',$locale);
                            $walletAmountMate['transaction_type']=1;
                            $walletAmountMate['payment_type']=2;
                            UserWalletDetail::create($walletAmountMate);
                        }else{
                            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.mate_has_to_pay_wallet_insufficient',$locale));
                            return response()->json($arr_to_return);
                        }
                    }
                    //adding amount to user walltes 80% to star
                    //get user final amount
                    $star_wallet_data = UserWalletDetail::where('user_id',$user_id)->orderBy('id', 'desc')->first(['final_amout']);
                    $prev_file_amount=isset($star_wallet_data->final_amout)?$star_wallet_data->final_amout:'0';
                    $commision_star=(($fare_amount)*($star_percentage/100));
                    $walletAmount['user_id']=$user_id;
                    $walletAmount['transaction_amount']=(($fare_amount)*($star_percentage/100));
                    $walletAmount['final_amout']=(double)($prev_file_amount+$commision_star);
                    $walletAmount['trans_desc']=Lang::choice('messages.trans_desc',$locale);
                    $walletAmount['transaction_type']=0;
                    $walletAmount['payment_type']=2;
                    $walletAmount['order_id']=$order_details->id;
                    $star_wallet_data_check = UserWalletDetail::where('user_id',$user_id)->where('order_id',$order_details->id)->first();
                   // UserWalletDetail::create($walletAmount);
                    if(count($star_wallet_data_check)<=0)
                    {
                       UserWalletDetail::create($walletAmount);
                    }
                    $order_details->star_commission=$commision_star;
                    $order_details->admin_commission=(($fare_amount)*($admin_percentage/100));
                    $order_details->save();
                    $is_agent_flag=0;                    
                    $star_country=0;
                    $star_state=0;
                    $star_city=0;
                    $agentDetails=array();
                    $agent_id=0;
                    $userAddress=UserAddress::where('user_id',$user_id)->first();
                    if(($admin_percentage+$star_percentage)<100)
                 {
                    if(isset($userAddress->user_country))
                    {
                        $star_country=$userAddress->user_country;
                        $star_state=$userAddress->user_state;
                        $star_city=$userAddress->user_city;
                        $agent_country=0;
                        $agent_state=0;
                        $agent_city=0;
                        //check if any agenct in this city
                        $agentDetails=UserInformation::where('user_type','4')->get();
                         $userInformationData=  UserInformation::where('user_id',$user_id)->first();
                        $agentDetails=$agentDetails->reject(function($userDetails) use ($userInformationData)
                        {
                            $mobile_code=str_replace("+","",$userInformationData->mobile_code);
                            $mobile_code_agent=str_replace("+","",$userDetails->user->userInformation->mobile_code);
                            
                                return ($userDetails->user->userInformation->user_type!=4 || ($mobile_code_agent!=$mobile_code));
                        })->values();
                        //check for city if any
                        $agentDetailsCities=$agentDetails->reject(function($userDetails) use ($star_city)
                        {
                            if ($userDetails->user->userAddress) {

                                foreach ($userDetails->user->userAddress as $address) {
                                    $agent_city = $address->user_city;
                                }
                            }
                            if($agent_city!='22')
                            return ($agent_city!=$star_city);
                        })->values();
                        
                        if(count($agentDetailsCities)>0)
                        {
                            
                            foreach($agentDetailsCities as $agentCity)
                            {
                                $agent_id=$agentCity->user_id;
                            }
                        }else
                        {
                            $agentDetailsstate=$agentDetails->reject(function($userDetails) use ($star_state)
                            {
                                if ($userDetails->user->userAddress) {

                                    foreach ($userDetails->user->userAddress as $address) {
                                        $agent_state = $address->user_state;
                                    }
                                }
                                 if($agent_state!='32')
                                return ($agent_state!=$star_state);
                            })->values();
                            if(count($agentDetailsstate)>0)
                            {
                                
                                foreach($agentDetailsstate as $agentState)
                                {
                                    $agent_id=$agentState->user_id;
                                }
                            }else{
                                    $agentDetailsCountry=$agentDetails->reject(function($userDetails) use ($star_country)
                                    {
                                        if ($userDetails->user->userAddress) {

                                            foreach ($userDetails->user->userAddress as $address) {
                                                $agent_country = $address->user_country;
                                            }
                                        }
                                        if($agent_country!='17')
                                        return ($agent_country!=$star_country);
                                    })->values();
                                    if(count($agentDetailsCountry)>0)
                                    {
                                        foreach($agentDetailsCountry as $agentcountry)
                                        {
                                            $agent_id=$agentcountry->user_id;
                                        }
                                    }
                                
                            }
                            
                        }
                        
                    }
//                   if($agent_id!=0)
//                   {
//                       //adding 10% to agent
//                       $agent_wallet_data = UserWalletDetail::where('user_id',$agent_id)->orderBy('id', 'desc')->first(['final_amout']);
//                       $prev_file_amount_agent=isset($agent_wallet_data->final_amout)?$agent_wallet_data->final_amout:'0';
//                       $commision_agent=(($fare_amount)*($agent_percentage/100));
//                       $walletAmountAgent['user_id']=$agent_id;
//                       $walletAmountAgent['transaction_amount']=(($fare_amount)*($agent_percentage/100));
//                       $walletAmountAgent['final_amout']=(double)($prev_file_amount_agent+$commision_agent);
//                       $walletAmountAgent['trans_desc']=Lang::choice('messages.trans_desc',$locale);
//                       $walletAmountAgent['transaction_type']=0;
//                    if($order_details->payment_type==2)
//                     {
//                       $walletAmountAgent['payment_type']=2; 
//                     }else{
//                       $walletAmountAgent['payment_type']=0;  
//                     }
//                       
//                       $walletAmountAgent['order_id']=$order_details->id;
//                      $star_wallet_data_check = UserWalletDetail::where('user_id',$agent_id)->where('order_id',$order_details->id)->first();
//                     if(count($star_wallet_data_check)<=0)
//                     {
//                        UserWalletDetail::create($walletAmountAgent);
//                     }   
//                       
//                      
//                       //get agent company and remaining commision to company
//                      if(isset($agent_wallet_data->userMainInformation->supervisor_id) && $agent_wallet_data->userMainInformation->supervisor_id!=0)
//                      {
//                        
//                        $company_user=User::where('id',$agent_wallet_data->userMainInformation->supervisor_id)->first();
//                        $company_wallet_data = UserWalletDetail::where('user_id',$company_user->id)->orderBy('id', 'desc')->first(['final_amout']);
//                        $prev_file_amount_company=isset($company_wallet_data->final_amout)?$company_wallet_data->final_amout:'0';
//                        $walletAmountCompany['user_id']=$company_user->id;
//                        $walletAmountCompany['transaction_amount']=(($fare_amount)*($company_percentage/100));
//                        $walletAmountCompany['final_amout']=(double)($prev_file_amount_company+$commision_agent);
//                        $walletAmountCompany['trans_desc']=Lang::choice('messages.trans_desc',$locale);
//                        $walletAmountCompany['transaction_type']=0;
//                         if($order_details->payment_type==2)
//                        {
//                          $walletAmountCompany['payment_type']=2; 
//                        }else{
//                          $walletAmountCompany['payment_type']=0;  
//                        }
//                        $walletAmountCompany['order_id']=$order_details->id;
//                       // UserWalletDetail::create($walletAmountCompany);
//                      }
//                   }
                 } 
                    
                    
                   
                  //  sending push notification to mate and star for rating
                   //get mate details.
                   $starDetailsPush=UserInformation::where('user_id',$order_details->driver_id)->first();
                   $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_complete_rating',$locale),"flag"=>'order_complete','message'=>Lang::choice('messages.order_complete_rating',$locale),'order_id'=>$order_details->id);
                   $arr_push_message_ios=array();
                   if(isset($starDetailsPush->device_id) && $starDetailsPush->device_id)
                   {
                    $obj_send_push_notification=new SendPushNotification();     
                    if($starDetailsPush->device_type=='0')
                     {
                      //sending push notification star user.
                        $arr_push_message_android=array();
                        $arr_push_message_android['to']=$starDetailsPush->device_id;
                        $arr_push_message_android['priority']="high";
                        $arr_push_message_android['sound']="default";
                        $arr_push_message_android['notification']=$arr_push_message;
                        $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));

                     }else{
                            
                          $arr_push_message_ios['to']=$starDetailsPush->device_id;
                          $arr_push_message_ios['priority']="high";
                          $arr_push_message_ios['sound']="default";
                          $arr_push_message_ios['notification']=$arr_push_message;
                          $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                         
                     }
                   }
                    
                    //seding to mate
                    
                  $mateDetailsPush=UserInformation::where('user_id',$order_details->mate_id)->first();
                  $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_complete_rating',$locale),"flag"=>'order_complete','message'=>Lang::choice('messages.order_complete_rating',$locale),'order_id'=>$order_details->id);
                  $arr_push_message_ios=array();
                  if(isset($mateDetailsPush->device_id) && $mateDetailsPush->device_id!='')
                  {
                      $obj_send_push_notification=new SendPushNotification();     
                    if($mateDetailsPush->device_type=='0')
                    {
                        $arr_push_message_android=array();
                        $arr_push_message_android['to']=$mateDetailsPush->device_id;
                        $arr_push_message_android['priority']="high";
                        $arr_push_message_android['sound']="default";
                        $arr_push_message_android['notification']=$arr_push_message;
                        $obj_send_push_notification->androidPushNotificaton(json_encode($arr_push_message_android));
                        
                    }else{
                          $arr_push_message_ios['to']=$mateDetailsPush->device_id;
                          $arr_push_message_ios['priority']="high";
                          $arr_push_message_ios['sound']="default";
                          $arr_push_message_ios['notification']=$arr_push_message;
                          $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                        
                    }
                  }
                    
                }
                
                
            }
//            else if($status=='9')
//            {
//                if($order_details->id!='')
//                {
//                    $order_details->status=3;
//                    $dt = new DateTime(date('Y-m-d H:i:s'));
//          
//                    //get timezone as per country
//                    $countryInfo=Country::where('id',$order_details->country_id)->first();
//                    if(count($countryInfo)>0)
//                    {
//                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
//                         $dt->setTimezone($tz);
//                    }
//
//                    $date1_val= $dt->format('Y-m-d H:i:s'); 
//                    $date1=new DateTime($date1_val);
//                    $order_details->order_complete_date_time=$date1;
//                    $order_details->is_payment_done=1;
//                    $order_details->save();
//                     
//                  //  sending push notification to mate and star for rating
//                   //get mate details.
//                   $starDetailsPush=UserInformation::where('user_id',$order_details->driver_id)->first();
//                   $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_return_on_canceled',$locale),"flag"=>'order_complete','message'=>Lang::choice('messages.order_return_on_canceled',$locale),'order_id'=>$order_details->id);
//                   $arr_push_message_ios=array();
//                   if(isset($starDetailsPush->device_id) && $starDetailsPush->device_id)
//                   {
//                      $obj_send_push_notification=new SendPushNotification();   
//                    if($starDetailsPush->device_type=='0')
//                     {
//                        $arr_push_message_android=array();
//                        $arr_push_message_android['to']=$starDetailsPush->device_id;
//                        $arr_push_message_android['priority']="high";
//                        $arr_push_message_android['sound']="default";
//                        $arr_push_message_android['notification']=$arr_push_message;
//                        $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
//                      }else{
//                          $arr_push_message_ios['to']=$starDetailsPush->device_id;
//                          $arr_push_message_ios['priority']="high";
//                          $arr_push_message_ios['sound']="default";
//                          $arr_push_message_ios['notification']=$arr_push_message;
//                          $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
//                         
//                     }
//                   }
//                    
//                    //seding to mate
//                    
//                    $mateDetailsPush=UserInformation::where('user_id',$order_details->mate_id)->first();
//                    $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_return_on_canceled_star',$locale),"flag"=>'order_complete','message'=>Lang::choice('messages.order_return_on_canceled_star',$locale),'order_id'=>$order_details->id);
//                    $arr_push_message_ios=array();
//                  if(isset($mateDetailsPush->device_id) && $mateDetailsPush->device_id!='')
//                  {
//                     $obj_send_push_notification=new SendPushNotification();   
//                    if($mateDetailsPush->device_type=='0')
//                    {
//                        $arr_push_message_android=array();
//                        $arr_push_message_android['to']=$mateDetailsPush->device_id;
//                        $arr_push_message_android['priority']="high";
//                        $arr_push_message_android['sound']="default";
//                        $arr_push_message_android['notification']=$arr_push_message;
//                        $obj_send_push_notification->androidPushNotificaton(json_encode($arr_push_message_android));
//
//                    }else{
//                         $arr_push_message_ios['to']=$mateDetailsPush->device_id;
//                         $arr_push_message_ios['priority']="high";
//                         $arr_push_message_ios['sound']="default";
//                         $arr_push_message_ios['notification']=$arr_push_message;
//                         $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
//                         
//                  }}
//                    
//                }
//                //
//                
//            }
           
             //update transaction status for order
             //saving this information to status transaction
            $statusTransaction=array();
            $statusTransaction['user_id']=$user_id;
            $statusTransaction['order_id']=$order_id;
            $mate_details=User::where('id',$order_details->mate_id)->first();
            $star_details=User::where('id',$order_details->driver_id)->first();
            $order_status_contents=$status_text;
            $status_changed_text=$status_text;
            if($calculate_flag=='3')
            {
                 $dt = new DateTime(date('Y-m-d H:i:s'));
          
                //get timezone as per country
                $countryInfo=Country::where('id',$order_details->country_id)->first();
                if(count($countryInfo)>0)
                {
                     $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                     $dt->setTimezone($tz);
                }
          
                $date1_val= $dt->format('Y-m-d H:i:s'); 
                $order_details->picked_up_time=$date1_val;
                $order_details->save();
                $order_status_contents=$status_text;
                $status_changed_text=$status_text;
            }
            else if($calculate_flag=='2' || $calculate_flag=='4')
            {
                 if(isset($mate_details->userInformation->mobile_code) && $mate_details->userInformation->mobile_code!='')
                {
                     $countryInfo=Country::where('id',$order_details->country_id)->first(); 
                     $currencyCode=isset($countryInfo->currency_code)?$countryInfo->currency_code:'KD';
                    $mobile_code=str_replace("+","",$mate_details->userInformation->mobile_code);
                    $mobile_code="+".$mobile_code;
                    $messagesToSend="";
                    // $messagesToSend1=Lang::choice('messages.deliveed_msg_total_amt1',$locale);

                    $messagesToSend=Lang::choice('messages.deliveed_msg_total_amt',$locale);
                    $messagesToSend.="\n";
                    $messagesToSend.=Lang::choice('messages.amount_due_delivered',$locale).": ".$currencyCode ." ".$order_details->total_amount."";
                    
                    $mobile_number_to_send=$mobile_code."".$mate_details->userInformation->user_mobile;
                    $obj_sms=new SendSms();
                    $obj_sms->sendMessage($mobile_number_to_send,$messagesToSend);
                }
                   \App::setLocale($order_locale);
                   $site_email=GlobalValues::get('site-email');
                    $site_title=GlobalValues::get('site-title');
                    $arr_keyword_values = array();
                   //Assign values to all macros
                    $total_order_amt=isset($order_details->total_amount)?$order_details->total_amount:'0';
                    $arr_keyword_values['CUSTOMER_FIRST_NAME'] = isset($mate_details->userInformation->first_name)?$mate_details->userInformation->first_name:'';
                    $arr_keyword_values['CUSTOMER_LAST_NAME'] =isset($mate_details->userInformation->last_name)?$mate_details->userInformation->last_name:'';
                    $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $arr_keyword_values['ORDER_ID'] = isset($order_details->id)?$order_details->id:'';
                    $arr_keyword_values['ORDER_NUMBER'] = isset($order_details->order_unique_id)?$order_details->order_unique_id:'';
                    $arr_keyword_values['TRIP_AMOUNT'] = "INR ".$total_order_amt;
                    $email_subject=Lang::choice('messages.trip_complete_fare_details_email',$order_locale);
                    $tempate_name="emailtemplate::trip-complete-fare-details-".$order_locale;
                    if(isset($mate_details->email))
                    {
                        $mate_email=$mate_details->email;
                        Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($mate_email,$email_subject,$site_email,$site_title)  {

                          $message->to($mate_email)->subject($email_subject)->from($site_email,$site_title);

                      });
                    }
                   if(count($mate_details)>0)
                  {
                    //saving
                    $notiMsg=Lang::choice('messages.order_has_been_completed_msg',$locale);
                    $notiMsg=str_replace("%%DRIVER_NAME%%", $star_details->userInformation->first_name." ". $star_details->userInformation->last_name,$notiMsg);
                    $notiMsg=str_replace("%%ORDER_NUMBER%%", $order_details->order_unique_id,$notiMsg);
                    $saveNotification=new AppNotification();
                    $saveNotification->saveNotification($mate_details->id,$order_details->id,Lang::choice('messages.order_has_been_completed',$locale),$notiMsg,date("Y-m-d"),0,'order');
                  }
                 \App::setLocale($locale);  
                 $order_status_contents=$status_text;
                 $status_changed_text=$status_text;
            } 
            $statusTransaction['transaction_content']=$status_changed_text;
            OrdersTransactionStatus::create($statusTransaction);
             $total_order_amt=isset($order_details->total_amount)?$order_details->total_amount:'0';
            //sending push notification to mate
            $arr_push_message=array("sound"=>"default","text"=>"BAGGI","text"=>$status_changed_text,"flag"=>'status_changed','message'=>$status_changed_text,'order_id'=>$order_details->id);
            $arr_push_message_ios=array();
     
            if(isset($mate_details->userInformation->device_id) && $mate_details->userInformation->device_id!='')
            {
                $obj_send_push_notification=new SendPushNotification();  
                if($mate_details->userInformation->device_type=='0')
                {
                    $arr_push_message_android=array();
                    $arr_push_message_android['to']=$mate_details->userInformation->device_id;
                    $arr_push_message_android['priority']="high";
                    $arr_push_message_android['sound']="default";
                    $arr_push_message_android['notification']=$arr_push_message;
                    $obj_send_push_notification->androidPushNotificaton(json_encode($arr_push_message_android));
          
                }else{
                     $arr_push_message_ios['to']=$mate_details->userInformation->device_id;
                     $arr_push_message_ios['priority']="high";
                     $arr_push_message_ios['notification']=$arr_push_message;
                     $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                }
            }
            
             \App::setLocale($locale);
            $arr_to_return = array("error_code" => 0,"order_amount"=>$total_order_amt, "msg" =>Lang::choice('messages.status_updated',$locale));
        }else{
             \App::setLocale($locale);
         $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_invalid',$locale));
           
        }
        }else{
             \App::setLocale($locale);
        $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_invalid',$locale));
        }
       return response()->json($arr_to_return);
     }
     
     public function getAllPaymentMethod(Request $request){
       
       //get all payment methods
       $locale =  isset($request['locale'])?$request['locale']:'en';
       \App::setLocale($locale);
       $paymentMethods=PaymentMethod::where('status','1')->translatedIn(\App::getLocale())->get();
       $arr_to_return = array("error_code" => 0, "data" =>$paymentMethods);
       return response()->json($arr_to_return);
    }  
     public function getAllUserPaymentMethod(Request $request){
       
       //get all payment methods
       $locale =  isset($request['locale'])?$request['locale']:'en';
       $user_id =  isset($request['user_id'])?$request['user_id']:'0';
       \App::setLocale($locale);
       $paymentMethods= UserPaymentMethod::where('user_id',$user_id)->get();
       $arr_to_return = array("error_code" => 0, "data" =>$paymentMethods);
       return response()->json($arr_to_return);
    }  
     public function updateDeliveryuserPaymentMethods(Request $request){
       
       //get all payment methods
       $locale =  isset($request['locale'])?$request['locale']:'en';
       $method_ids =  isset($request['method_ids'])?$request['method_ids']:'';
       $user_id =  isset($request['user_id'])?$request['user_id']:'0';
       \App::setLocale($locale);
       $method_ids=json_decode($method_ids);       
      if($user_id>0 && count($method_ids)>0)
      {
        UserPaymentMethod::where('user_id',$user_id)->delete();
        for($k=0;$k<count($method_ids);$k++){
                    $arr_methods = array();
                    $arr_methods["payment_method_id"] = $method_ids[$k];
                    $arr_methods["status"] = 1;
                    $arr_methods["user_id"] = $user_id;
                    UserPaymentMethod::create($arr_methods);
         }
          $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.method_updated',$locale));
      }else{
           $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.user_invalid',$locale));
      }
      
       return response()->json($arr_to_return);
   }  
    private function generateReferenceNumber()
    {
         return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0, 0xffff), mt_rand(0, 0xffff),mt_rand(0, 0xffff),mt_rand(0, 0x0fff) | 0x4000,mt_rand(0, 0x3fff) | 0x8000,mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) );

    }
  
    
    /* web-services added on 28-11-2016 */
    
     public function starQuoteOnOrder(Request $request){
        //
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $order_id =  isset($request['order_id'])?$request['order_id']:'0';
        $quotation_amount =  isset($request['quotation_amount'])?$request['quotation_amount']:'0';
        $pickup_location =  isset($request['pickup_location'])?$request['pickup_location']:'0';
        $description =  isset($request['description'])?$request['description']:'0';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($order_id>0 && $user_id>0 && $quotation_amount>0 )
         {
             $order_details=Order::where('id',$order_id)->first();
             $country_id=$order_details->country_id;
             $order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
           if(count($order_notification)>0)
           {
             if($order_details->status==0)
              {
                 if(!($order_details->driver_id>0))
                 {
                    $order_notification->delete();
                    $checkalreadyQuote=UserServiceQuotation::where('order_id',$order_id)->where('user_id',$user_id)->first();
                   if(count($checkalreadyQuote)<=0)
                   {
                     //saving user quotation
                     $userQuotation=array();
                     $userQuotation['user_id']=$user_id;
                     $userQuotation['order_id']=$order_id;
                     $userQuotation['qutation_amount']=$quotation_amount;
                     $userQuotation['pickup_location']=$pickup_location;
                     $userQuotation['description']=$description;
                     $userQuotation['status']='0';
                     UserServiceQuotation::create($userQuotation);
                     //removing order notification of that user
                     $order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
                     if(isset($order_notification->id))
                     {
                      //  $order_notification->delete();
                     }
                     //sending email to mate about a new quotation
                     
                     $user_details=UserInformation::where('user_id',$user_id)->first();
                     $mate_details=User::where('id',$order_details->mate_id)->first();
                     $site_email=GlobalValues::get('site-email');
                     $site_title=GlobalValues::get('site-title');
                     $arr_keyword_values = array();
                    //Assign values to all macros
                     $arr_keyword_values['MATE_FIRST_NAME'] = $mate_details->userInformation->first_name;
                     $arr_keyword_values['STAR_FIRST_NAME'] = $user_details->first_name;
                     $arr_keyword_values['STAR_LAST_NAME'] = $user_details->last_name;
                     $arr_keyword_values['MOBILE_NUMBER'] = "+".$user_details->mobile_code."".$user_details->user_mobile;
                     $arr_keyword_values['SITE_TITLE'] = $site_title;
                     $arr_keyword_values['ORDER_ID'] = $order_id;
                     $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;
                     
                     $mobile_code=str_replace("+","",$user_details->mobile_code);
                     $arr_keyword_values['STAR_MOBILE'] = "+".$mobile_code."".$user_details->user_mobile; 
                     $email_subject=Lang::choice('messages.star_order_quote_to_mate',$locale);
                     $tempate_name="emailtemplate::order-quote-notify-to-mate-star-details-".$locale;
                     if(isset($mate_details->email))
                     {
                         $mate_email=$mate_details->email;
                         Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($mate_email,$email_subject,$site_email,$site_title)  {

                           $message->to($mate_email)->subject($email_subject)->from($site_email,$site_title);

                       });
                     }
                  
                     //sending push notification to mate
                       $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.star_order_quote_to_mate',$locale),"flag"=>'order_new_quotation_placed','message'=>Lang::choice('messages.star_order_quote_to_mate',$locale),'order_id'=>$order_details->id);
                       $arr_push_message_ios=array();
                       if(isset($mate_details->userInformation->device_id) && $mate_details->userInformation->device_id!='')
                       {
                        $obj_send_push_notification=new SendPushNotification();     
                        if($mate_details->userInformation->device_type=='0')
                        {
                         //sending push notification mate user.
                          $arr_push_message_android=array();
                          $arr_push_message_android['to']=$mate_details->userInformation->device_id;
                          $arr_push_message_android['priority']="high";
                          $arr_push_message_android['sound']="default";
                          $arr_push_message_android['notification']=$arr_push_message;
                          $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                        }else{
                              $arr_push_message_ios['to']=$mate_details->userInformation->device_id;
                              $arr_push_message_ios['priority']="high";
                              $arr_push_message_ios['notification']=$arr_push_message;
                              $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                          
                        }
                       }
                       //sending emails to user
                     //sending email to admin users
                    $adminusers=UserInformation::where('user_type',1)->get();
                    $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                    {
                        $country=0;
                         if(isset($user_details->user->userAddress))
                         {

                             foreach($user_details->user->userAddress as $address)
                             {
                                   $country=$address->user_country;
                             }
                         }
                        if($country && $country!=0)
                        {
                           return (($country!=$country_id) ||($user_details->user->supervisor_id!=0));
                        }
                         if($user_details->user->hasRole('superadmin'))
                        {
                            return true;
                        }

                    });
                    $site_email=GlobalValues::get('site-email');
                    $site_title=GlobalValues::get('site-title');
                    //Assign values to all macros
                     $arr_keyword_values['STAR_FIRST_NAME'] = $user_details->first_name;
                     $arr_keyword_values['STAR_LAST_NAME'] = $user_details->last_name;
                     $arr_keyword_values['STAR_ID'] =  $user_id;
                     $arr_keyword_values['ORDER_ID'] =  $order_id;
                     $arr_keyword_values['ORDER_NUMBER'] =  $order_details->order_unique_id;
                     $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $email_template_title="emailtemplate::order-quation-by-star-to-admin";    
                    $email_template_subject="A new quote posted by a star";
                   if(count($adminusers)>0)
                   {
                       foreach($adminusers as $admin)
                       { 

                           Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                             if(isset($admin->user->email))
                             {
                                 $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                             }

                         });
                      }
                   }
                   //sending email to site admin              
                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                             if(isset($site_email))
                             {
                                 $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                             }

                    });
                   $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.quote_posted_successfully',$locale));        
                   }else{
                       $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.already_quote',$locale));    
                   }
                 }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_already_accepted',$locale));    
             }
             }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_already_accepted',$locale));    
             }
             
         }else{
              //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
        }
         }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
         }
      return response()->json($arr_to_return);
    }
     public function mateAcceptQuotation(Request $request){
        //
        $quotation_id =  isset($request['quotation_id'])?$request['quotation_id']:'0';
        $order_id =  isset($request['order_id'])?$request['order_id']:'0';
        $user_id =  isset($request['user_id'])?$request['user_id']:'0';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($order_id>0 && $quotation_id>0)
         {
             $checkalreadyQuote=UserServiceQuotation::where('id',$quotation_id)->first();
             if(count($checkalreadyQuote)>0)
             {
                 //get all quotation on this order
                  $allOrderQuotes=UserServiceQuotation::where('order_id',$order_id)->get();
                 if(count($allOrderQuotes)>0)
                 {
                     foreach($allOrderQuotes as $quote)
                     {
                        //update each quote as rejected
                         $quote->status=2;
                         $quote->save();
                         
                        $order_notification=OrderNotification::where('order_id',$quote->order_id)->first();
                        if(count($order_notification)>0)
                        {
                            $order_notification->delete();
                        }
                        
                        //sending push notification to other stars about their rejection.
                         //sending push notification to star
                       if($checkalreadyQuote->user_id!=$quote->user_id)
                       {
                        $user_details_rejected=UserInformation::where('user_id',$quote->user_id)->first();
                        $arr_push_message_reject=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.quote_has_been_rejected',$locale),"flag"=>'order_rejected','message'=>Lang::choice('messages.quote_has_been_rejected',$locale),'order_id'=>$quote->order_id);
                        $arr_push_message_ios=array();
                        if(isset($user_details_rejected->device_type) && $user_details_rejected->device_type!='')
                        {
                           $obj_send_push_notification=new SendPushNotification();   
                         if($user_details_rejected->device_type=='0' && $user_details_rejected->device_id!='')
                         {
                          //sending push notification mate user.
                                $arr_push_message_android=array();
                                $arr_push_message_android['to']=$user_details_rejected->device_id;
                                $arr_push_message_android['priority']="high";
                                $arr_push_message_android['sound']="default";
                                $arr_push_message_android['notification']=$arr_push_message_reject;
                                $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));

                            }else{
                              $arr_push_message_ios['to']=$user_details_rejected->device_id;
                              $arr_push_message_ios['priority']="high";
                              $arr_push_message_ios['notification']=$arr_push_message_reject;
                              $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                            
                         }
                        }
                       }
                     }
                     //update select quote a accepted
                    $checkalreadyQuote->status=1;
                    $checkalreadyQuote->save();
                   //update order status as active and driver_id of select user
                     $order_details=Order::where('id',$checkalreadyQuote->order_id)->first();
                     $country_id=$order_details->country_id;
                     $order_details->driver_id=$checkalreadyQuote->user_id;
                     $order_details->total_amount=$checkalreadyQuote->qutation_amount;
                     $order_details->status=1;
                     $order_details->status_by_star=1;
                     $order_details->save();
                     //saving this information to status transaction
                     $statusTransaction=array();
                     $statusTransaction['user_id']=$user_id;
                     $statusTransaction['order_id']=$order_details->id;
                     $statusTransaction['transaction_content']="Quote has been accepted by mate user";
                     OrdersTransactionStatus::create($statusTransaction);
                    //sending push notification to star user about his quote acceptance
                                         
                     $user_details=UserInformation::where('user_id',$checkalreadyQuote->user_id)->first();
                     $mate_details=UserInformation::where('user_id',$order_details->mate_id)->first();
                     $site_email=GlobalValues::get('site-email');
                     $site_title=GlobalValues::get('site-title');
                     $arr_keyword_values = array();
                    //Assign values to all macros
                     $arr_keyword_values['MATE_FIRST_NAME'] = $mate_details->first_name;
                     $arr_keyword_values['MATE_LAST_NAME'] = $mate_details->first_name;
                     $arr_keyword_values['STAR_FIRST_NAME'] = $user_details->first_name;
                     $arr_keyword_values['STAR_LAST_NAME'] = $user_details->last_name;
                     $arr_keyword_values['MOBILE_NUMBER'] = "+".$user_details->mobile_code."".$user_details->user_mobile;
                     $arr_keyword_values['SITE_TITLE'] = $site_title;
                     $arr_keyword_values['ORDER_ID'] = $order_id;
                     $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;
                     
                    
                     $email_subject=Lang::choice('messages.quote_has_been_accepted',$locale);
                     $tempate_name="emailtemplate::quote_has_been-accepted-".$locale;
                     if(isset($mate_details->user->email))
                     {
                         $mate_email=$mate_details->user->email;
                         Mail::send($tempate_name,$arr_keyword_values, function ($message) use ($mate_email,$email_subject,$site_email,$site_title)  {

                           $message->to($mate_email)->subject($email_subject)->from($site_email,$site_title);

                       });
                     }
                      //sending push notification to star
                       $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.quote_has_been_accepted',$locale),"flag"=>'order_quote_accepted','message'=>Lang::choice('messages.quote_has_been_accepted',$locale),'order_id'=>$order_id);
                       $arr_push_message_ios=array();
                       if(isset($user_details->device_type))
                       {
                         $obj_send_push_notification=new SendPushNotification();    
                        if($user_details->device_type=='0')
                        {
                         //sending push notification star user.
                            $arr_push_message_android=array();
                            $arr_push_message_android['to']=$user_details->device_id;
                            $arr_push_message_android['priority']="high";
                            $arr_push_message_android['sound']="default";
                            $arr_push_message_android['notification']=$arr_push_message;
                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                          
                        }else{
                             $arr_push_message_ios['to']=$user_details->device_id;
                             $arr_push_message_ios['priority']="high";
                             $arr_push_message_ios['notification']=$arr_push_message;
                             $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                            
                        }
                       }
                    
                    //sending emails to user
                    //sending email to admin users
                    $adminusers=UserInformation::where('user_type',1)->get();
                    $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                    {
                        $country=0;
                         if(isset($user_details->user->userAddress))
                         {

                             foreach($user_details->user->userAddress as $address)
                             {
                                   $country=$address->user_country;
                             }
                         }
                        if($country && $country!=0)
                        {
                           return (($country!=$country_id) ||($user_details->user->supervisor_id!=0));
                        }
                         if($user_details->user->hasRole('superadmin'))
                        {
                            return true;
                        }

                    });
                    $site_email=GlobalValues::get('site-email');
                    $site_title=GlobalValues::get('site-title');
                    //Assign values to all macros
                     $arr_keyword_values['STAR_FIRST_NAME'] = $user_details->first_name;
                     $arr_keyword_values['STAR_LAST_NAME'] = $user_details->last_name;
                     $arr_keyword_values['STAR_ID'] =  $user_id;
                     $arr_keyword_values['ORDER_ID'] =  $order_id;
                     $arr_keyword_values['ORDER_NUMBER'] =  $order_details->order_unique_id;
                     $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $email_template_title="emailtemplate::order-quation-by-star-to-admin";    
                    $email_template_subject="A new quote posted by a star";
                   if(count($adminusers)>0)
                   {
                       foreach($adminusers as $admin)
                       { 

                           Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                             if(isset($admin->user->email))
                             {
                                 $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                             }

                         });
                      }
                   }
                   //sending email to site admin              
                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                             if(isset($site_email))
                             {
                                 $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                             }

                    });   
                 }  
                    
                   $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.quote_accepted_succesffully',$locale));        
              }else{
                       $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.quotation_accpet_invalid',$locale));    
             }
             
         }else{
              //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.quotation_accpet_invalid',$locale));
        }
       return response()->json($arr_to_return);
    }
    
     public function starRejectQuotation(Request $request){
        //
        $user_id =  isset($request['user_id'])?$request['user_id']:'';
        $order_id =  isset($request['order_id'])?$request['order_id']:'0';   
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $reason_text =  isset($request['reason_text'])?$request['reason_text']:'en';
        \App::setLocale($locale);
        if($order_id>0 && $user_id>0 && $reason_text!='')
         {
             $order_details=Order::where('id',$order_id)->first();
             $country_id=$order_details->country_id;
             $user_details=UserInformation::where('user_id',$user_id)->first();
             $order_notification=OrderNotification::where('order_id',$order_id)->where('user_id',$user_id)->first();
           if(count($order_notification)>0)
           {
             if($order_details->status==0)
              {
                 if(!($order_details->driver_id>0))
                 {
                     
                     $order_notification->delete();
                    $order_cancellation_status=array();
                    $order_cancellation_status['order_id']=$order_id;
                    $order_cancellation_status['user_id']=$user_id;
                    $order_cancellation_status['reason_text']=$reason_text;
                    //storing cancel reason
                    OrderCancelationDetail::create($order_cancellation_status);
                     
                    //sending emails to user
                    //sending email to admin users
                    $adminusers=UserInformation::where('user_type',1)->get();
                    $adminusers=$adminusers->reject(function($user_details) use ($country_id)
                    { 
                        $country=0;
                         if(isset($user_details->user->userAddress))
                         {

                             foreach($user_details->user->userAddress as $address)
                             {
                                   $country=$address->user_country;
                             }
                         }
                        if($country && $country!=0)
                        {
                           return (($country!=$country_id) ||($user_details->user->supervisor_id!=0));
                        }
                         if($user_details->user->hasRole('superadmin'))
                        {
                            return true;
                        }

                    });
                    $site_email=GlobalValues::get('site-email');
                    $site_title=GlobalValues::get('site-title');
                    //Assign values to all macros
                     $arr_keyword_values['STAR_FIRST_NAME'] = $user_details->first_name;
                     $arr_keyword_values['STAR_LAST_NAME'] = $user_details->last_name;
                     $arr_keyword_values['STAR_ID'] =  $user_id;
                     $arr_keyword_values['ORDER_ID'] =  $order_id;
                     $arr_keyword_values['ORDER_NUMBER'] =  $order_details->order_unique_id;
                     $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $email_template_title="emailtemplate::star-reject-quotation-request-admin";    
                    $email_template_subject="A star has reject the quotation request";
                   if(count($adminusers)>0)
                   {
                       foreach($adminusers as $admin)
                       { 

                           Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$admin,$site_email,$site_title)  {
                             if(isset($admin->user->email))
                             {
                                 $message->to( $admin->user->email)->subject($email_template_subject)->from($site_email,$site_title);
                             }

                         });
                      }
                   }
                   //sending email to site admin              
                    Mail::send($email_template_title,$arr_keyword_values, function ($message) use ($email_template_subject,$site_email,$site_title)  {
                             if(isset($site_email))
                             {
                                 $message->to( $site_email)->subject($email_template_subject)->from($site_email,$site_title);
                             }

                    });
                   $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.quote_rejected_successfully',$locale));        
                 }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_already_accepted',$locale));    
             }
             }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_already_accepted',$locale));    
             }
             
         }else{
              //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
        }
         }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
         }
      return response()->json($arr_to_return);
    }
     public function mateRejectQuotation(Request $request){
        //
        $quotation_id =  isset($request['quotation_id'])?$request['quotation_id']:'';
        $locale =  isset($request['locale'])?$request['locale']:'en';
        \App::setLocale($locale);
        if($quotation_id>0)
        {
             
            // 
             $quotation_details=UserServiceQuotation::where('id',$quotation_id)->first();
           
            if(count($quotation_details)>0)
           {
                $quotation_details->status=2;
                $quotation_details->save();
                $order_details=Order::where('id',$quotation_details->order_id)->first();
                $country_id=$order_details->country_id;
                $user_details=UserInformation::where('user_id',$quotation_details->user_id)->first();
                if(isset($user_details->user_id))
                   {
                      $reject_message=Lang::choice('messages.order_quote_reject',$locale);
                      $reject_message=$reject_message."".$order_details->order_unique_id;
                      $arr_push_message=array("sound"=>"default","title"=>"BAGGI","text"=>$reject_message,"flag"=>'quotation_reject_mate','message'=>$reject_message,'order_id'=>$order_details->id);
                      $arr_push_message_ios=array();
                      if(isset($user_details->device_id)&& $user_details->device_id!='')
                      {
                        $obj_send_push_notification=new SendPushNotification();    
                       if($user_details->device_type=='0')
                       {
                        //sending push notification star user.
                            $arr_push_message_android=array();
                            $arr_push_message_android['to']=$user_details->device_id;
                            $arr_push_message_android['priority']="high";
                            $arr_push_message_android['sound']="default";
                            $arr_push_message_android['notification']=$arr_push_message;
                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                       }else{
                             $arr_push_message_ios['to']=$user_details->device_id;
                             $arr_push_message_ios['priority']="high";
                             $arr_push_message_ios['notification']=$arr_push_message;
                             $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                            
                       }
                      }
                   }
                if($order_details->status==0)
                 {

                      $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.quote_rejected_successfully',$locale));        
                    }else{

                      $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.invalid_operation',$locale));    
                }
             }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.invalid_operation',$locale));    
             }
             
         }else{
              //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.invalid_operation',$locale));
        }
         
      return response()->json($arr_to_return);
    }
    
    public function orderAllQuotation(Request $request){
        //
       
        $order_id =  isset($request['order_id'])?$request['order_id']:'0';   
        $locale =  isset($request['locale'])?$request['locale']:'en';
        $arrAllQuotaion=array();
        \App::setLocale($locale);
        if($order_id>0)
         {
             $order_details=Order::where('id',$order_id)->first();
            
           if(count($order_details)>0)
           {
             if($order_details->status==0)
              {
                  
                      $userAllQuotations=UserServiceQuotation::where('order_id',$order_id)->where('status','0')->get();
                
                   if(count($userAllQuotations)>0)
                   {
                       $i=0;
                       foreach($userAllQuotations as $quotation)
                       {
                           $arrAllQuotaion[$i]=$quotation;
                           $arrAllQuotaion[$i]['order_unique_id']=$order_details->order_unique_id;
                           
                          if(isset($quotation->user_id))
                          {
                              //get user information
                               $driverUserInformations=UserInformation::where('user_id',$quotation->user_id)->first();
                               if(isset($driverUserInformations->first_name))
                               {
                                  $arrAllQuotaion[$i]['star_first_name']=$driverUserInformations->first_name;
                                  $arrAllQuotaion[$i]['star_last_name']=$driverUserInformations->last_name;
                                  $arrAllQuotaion[$i]['star_mobile']="+".$driverUserInformations->mobile_code."".$driverUserInformations->user_mobile;
                               }
                          }
                       }
                   }
                   $arr_to_return = array("error_code" => 0, "data"=>$userAllQuotations);        
                 
             }else{
                 
                   $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_already_accepted',$locale));    
             }
             
         }else{
              //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
        }
         }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_accpet_invalid',$locale));
         }
      return response()->json($arr_to_return);
    }
    
    
    /* get distance and traval time by google API */
    function GetDrivingDistance($lat1, $lat2, $long1, $long2)
    {
//        echo " source lat ".$lat1." source long ". $lat2." dis lat ". $long1." dis long ". $long2; exit;
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=pl-PL";
//        echo $url; exit;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $dist = isset($response_a['rows'][0]['elements'][0]['distance']['text'])?$response_a['rows'][0]['elements'][0]['distance']['text']:'0,0';
        $time = isset($response_a['rows'][0]['elements'][0]['duration']['text'])?$response_a['rows'][0]['elements'][0]['duration']['text']:'0 min';
//        $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
//        $time = $response_a['rows'][0]['elements'][0]['duration']['text'];

//        return $response_a;
        return array('distance' => $dist, 'time' => $time);
    }
    
   /* get distance and traval time by google API */
    protected function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Km') {
         $theta = $longitude1 - $longitude2;
         $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
         $distance = acos($distance);
         $distance = rad2deg($distance);
         $distance = $distance * 60 * 1.1515; switch($unit) {
              case 'Mi': break; 
              case 'Km' : $distance = $distance * 1.609344;
         }
         return ((float)($distance));
    }
    
}
