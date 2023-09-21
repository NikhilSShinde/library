<?php
namespace App\Http\Controllers;
use App\User;
use App\UserInformation;
use App\UserAddress;
use App\UserOtpCodes;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\admin\Models\CountryServices;
use App\PiplModules\admin\Models\State;
use App\PiplModules\admin\Models\City;
use App\PiplModules\roles\Models\Role;
use App\PiplModules\slider\Models\SliderImage;
use App\PiplModules\contentpage\Models\ContentPage;
use App\PiplModules\contentpage\Models\ContentPageTranslation;
use Validator;
use Auth;
use Mail;
use Hash;
use Lang;
use App;
use App\PiplModules\contactrequest\Models\ContactRequestCategory;
use App\PiplModules\contactrequest\Models\ContactRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use GlobalValues;
use App\DriverUserInformation;
use League\Geotools\Coordinate\Ellipsoid;
use Toin0u\Geotools\Facade\Geotools;
use App\PiplModules\category\Models\Category;
use App\PiplModules\service\Models\Service;
use App\UserServiceInformation;
use DB;
use App\PiplModules\orderdetails\Models\Order;
use App\PiplModules\orderdetails\Models\OrdersInformation;
use App\PiplModules\ratingreview\Models\RatingQuestion;
use App\PiplModules\ratingreview\Models\UserRatingInformation;
use App\PiplModules\ratingreview\Models\RatingQuestionTranslation;
use App\PiplModules\orderdetails\Models\OrderItemImage;
use App\PiplModules\orderdetails\Models\OrderNotification;
use App\PiplModules\orderdetails\Models\OrderCancelationDetail;
use App\UserCreditCard;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use Storage;
use App\UserPaymentMethod;
use DateTime;
use DateTimeZone;
use App\PiplModules\wallethistory\Models\UserWalletDetail;
use App\UserEmergencyContactInformation;
use Config;
use App\PanaceaClasses\SendSms;
use App\PanaceaClasses\SendPushNotification;
class DeliveryOrderController extends Controller {
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

    use AuthenticatesAndRegistersUsers,   ThrottlesLogins;

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
    public function __construct() {
        //  $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    protected function validator(Request $request) {
        //only common files if we have multiple registration
        return Validator::make($request, [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'suburb' => 'required',
                    'zipcode' => 'required',
        ]);
    }

    protected function makeAnOrder(Request $request) {
          
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $service_id = isset($request['service_id']) ? $request['service_id'] : '';
        $distance = isset($request['distance']) ? $request['distance'] : '';
        $number_of_person = isset($request['number_of_person']) ? $request['number_of_person'] : '';
        $marine_duration = isset($request['marine_duration']) ? $request['marine_duration'] : '';
        $duration = isset($request['estimated_time']) ? $request['estimated_time'] : '';
        $pick_up_lat = isset($request['pick_up_lat']) ? $request['pick_up_lat'] : '';
        $pick_up_long = isset($request['pick_up_long']) ? $request['pick_up_long'] : '';
        $drop_up_lat = isset($request['drop_up_lat']) ? $request['drop_up_lat'] : '';
        $drop_up_long = isset($request['drop_up_long']) ? $request['drop_up_long'] : '';
        $pick_up_area = isset($request['pick_up_area']) ? $request['pick_up_area'] : '';
        $drop_up_area = isset($request['drop_up_area']) ? $request['drop_up_area'] : '';
        $pick_up_person_name = isset($request['pick_up_person_name']) ? $request['pick_up_person_name'] : '';
        $pick_up_person_mobile_number = isset($request['pick_up_person_mobile_number']) ? $request['pick_up_person_mobile_number'] : '';
        $drop_up_person_name = isset($request['drop_up_person_name']) ? $request['drop_up_person_name'] : '';
        $drop_up_person_mobile_number = isset($request['drop_up_person_mobile_number']) ? $request['drop_up_person_mobile_number'] : '';
        $coupon_code = isset($request['coupon_code']) ? $request['coupon_code'] : '';
        $order_date_time = isset($request['order_date_time']) ? $request['order_date_time'] : '';
        $fare_amount = isset($request['fare_amount']) ? $request['fare_amount'] : '';
        $schedule_type = isset($request['service_type']) ? $request['service_type'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $item_description = isset($request['item_description']) ? $request['item_description'] : '';
        $payment_type = isset($request['payment_type']) ? $request['payment_type'] : '';
        $distance_value=(float)str_replace(" km","",$distance);
        
        $flag_available = 0;
        $avalale_driver_id = 0;
        $available_driver_ids=array();
        $radious = GlobalValues::get('star-range-radious');
       
        $instant_order_minutes = GlobalValues::get('instant-order-minutes');
        $service_type=1;
        
       
        //check for user details
        $userInformationCheck=UserInformation::where('user_id',$user_id)->first();
        
        if(count($userInformationCheck)<=0)
        {
           $arr_to_return = array("error_code" => 4, "msg" => Lang::choice('messages.account_has_deleted_invalid_user', $locale));  
        }else if(count($userInformationCheck)>0 && $userInformationCheck->user_status=='2')
        {
           $arr_to_return = array("error_code" => 5, "msg" => Lang::choice('messages.account_has_blocked_invalid_user', $locale));   
        }else{
        //
         //get service type
        $service_details=Service::where('id',$service_id)->first();
        $category_id=$service_details->category_id;
        if($service_details->service_type=='0' || $service_details->service_type=='3' )
        {
             $service_type=1;
        }else  if($service_details->service_type=='1' )
        {
              $service_type=2;
        }
        else  if($service_details->service_type=='2' )
        {
             if($schedule_type==0)
             {
                 $service_type=1;
             }else{
                 $service_type=2;
             }
        }
        
        //adding time
       if($service_type==1)
       {
         $order_date_time = new DateTime($order_date_time);
         $order_date_time->modify("+{$instant_order_minutes} minutes");
      
       }
        if ($pick_up_lat != '' && $pick_up_long != '') {
            if ($user_id > 0 && $user_id != '') {
                
                $arr_user_data = User::find($user_id);
               //check for payment type and then if user has added any card
                
                //getting getting credit cards.
                if($user_id!='' && $payment_type=='1')
                {
                    $arrUserCards=UserCreditCard::where("user_id",$user_id)->get();
                    if(count($arrUserCards)<=0)
                    {
                        
                        $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.need_to_add_card', $locale));
                        return response()->json($arr_to_return);
                    }
                }
                 
                if (count($arr_user_data) > 0) {

                    // first checking and get first available star to inform
                    if ($service_type == 1 || $service_type == 3) {
                        $arrServiceUsers = UserServiceInformation::where('service_id', $service_id)->get();
                        $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                            if (isset($userInfo->user->driverUserInformation->availability))
                                return ($userInfo->user->driverUserInformation->availability == 0);
                        });

                        //get all user who has only 50 km range

                        if (count($arrServiceUsers) > 0) {
                            $user_ids = "0";
                            $arrayUserIds=array();
                            foreach ($arrServiceUsers as $users_ids) {
                                if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                    $user_ids.=",$users_ids->user_id";
                                    $arrayUserIds[]=$users_ids->user_id;
                                }
                            }
                            //
                            $users = DB::select("call getUserByDistance(" . $pick_up_lat . "," . $pick_up_long . ",'" . $user_ids . "'," . $radious . ")");

                            //check if a user is having any active orders
                            if (count($users) > 0) {
                                $j = 0;
                                foreach ($users as $user) {
                                 if(in_array($user->user_id,$arrayUserIds))
                                    {
                                    $userDetailsStatus =UserInformation::where('user_id', $user->user_id)->first();
                                   
                                    if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                        $arrServiceUsersInfo = UserServiceInformation::where('service_id', $service_id)->where('user_id',$user->user_id)->first();  
                                        if(isset($arrServiceUsersInfo->goe_fence_area) && ($arrServiceUsersInfo->goe_fence_area>=$user->distance))
                                        {
                                           $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                           //check if user has notification of any orders
                                           $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                           $payment_method_id=isset($request['payment_type'])?$request['payment_type']:'0';
                                           $paymentMethods= UserPaymentMethod::where('user_id',$user->user_id)->where('payment_method_id',$payment_method_id)->first();
                                           if (count($userData) <= 0 && count($order_notification_count)<=0 && count($paymentMethods)>0) {                                            
                                              // $available_driver_ids[]= $user->user_id;
                                               $flag_available = 1;
                                               $avalale_driver_id = $user->user_id;
                                                break;
                                              
                                           }
                                        }
                                    }
                                    $j++;
                                }}
                            }
                        }
                    } else {
                        $flag_available = 1;
                    }
                    
                    
                    if ($flag_available == 0) {
                        //storing details of order for which star was not available

                        $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
                    } else {
                            $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_id)->first();
                        
                            if($fare_amount!='0' && $fare_amount!='')
                            {
                                 $fare = $fare_amount;
                            }else {
                             $fare = 0;
                             if (count($arrDataService) > 0) {
                                 if ($arrDataService->price_type == '1') {
                                     $fare = (double) $arrDataService->base_price;
                                 } else {
                                     if ($distance_value > $arrDataService->base_km) {
                                         $fare= (double)$arrDataService->base_price;
                                         $fare=$fare + (double) ($distance_value-$arrDataService->base_km) * ($arrDataService->price_per_km);
                         
//                                         $fare = (double) ($distance_value) * ($arrDataService->price_per_km);
//                                         $fare=$fare + (double)$arrDataService->base_price;
                                         
                                     } else {
                                         $fare = (double) $arrDataService->base_price;
                                     }
                                 }

                             }
                            }
                       
                        if($payment_type==2)
                        {
                            //check wallter amount
                            $userWalletDetails=UserWalletDetail::where('user_id',$user_id)->orderBy('id', 'desc')->first(['final_amout']);
                           if(isset($userWalletDetails->final_amout))
                           {
                                 $wallet_amount=$userWalletDetails->final_amout;
                           }else{
                                 $wallet_amount=0;
                           }
                          
                            $final_remaining_wallter_amount=0;
                            //get all actve pending order of this user by using wallte payment
                            $arrStatus=array("0","1");
                            $order_amt_total=Order::where('mate_id',$user_id)->whereIn('status',$arrStatus)->sum('fare_amount');
                            $final_remaining_wallter_amount=$wallet_amount-$order_amt_total;
                            if($fare>$final_remaining_wallter_amount)
                            {
                                 $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.wallet_insufficient', $locale));
                                 return response()->json($arr_to_return);
                            }
                        }
                       

                        //creating user cards
                        $order_number = rand(10000, 99999999);
                        $arrOrder = array();
                        $arrOrder['order_unique_id'] = $order_number;
                        $arrOrder['driver_id'] = NULL;
                        $arrOrder['mate_id'] = $user_id;
                        $arrOrder['service_id'] = $service_id;
                        $arrOrder['order_place_date_time'] = $order_date_time;
                        $arrOrder['fare_amount'] = $fare;
                        $arrOrder['order_type'] = $service_type;
                        $arrOrder['country_id'] = $country_id;
                        $arrOrder['status'] = 0;
                        $arrOrder['payment_type'] = $payment_type;
                        $order = Order::create($arrOrder);

                        $arrOrderDetails = array();
                        $arrOrderDetails['order_id'] = $order->id;
                        $arrOrderDetails['selected_pickup_lat'] = $pick_up_lat;
                        $arrOrderDetails['selected_pickup_long'] = $pick_up_long;
                        $arrOrderDetails['pickup_area'] = $pick_up_area;
                        $arrOrderDetails['selected_drop_lat'] = $drop_up_lat;
                        $arrOrderDetails['selected_drop_long'] = $drop_up_long;
                        $arrOrderDetails['drop_area'] = $drop_up_area;
                        $arrOrderDetails['contact_person_for_pickup'] = $pick_up_person_name;
                        $arrOrderDetails['contact_person_for_destination'] = $drop_up_person_name;
                        $arrOrderDetails['pickup_person_contact_no'] = $pick_up_person_mobile_number;
                        $arrOrderDetails['destination_person_contact_no'] = $drop_up_person_mobile_number;
                        $arrOrderDetails['distance'] = $distance;
                        $arrOrderDetails['distance_value'] =$distance_value;
                        $arrOrderDetails['marine_duration'] =$marine_duration;
                        $arrOrderDetails['number_of_person'] =$number_of_person;
                        $arrOrderDetails['coupon_code'] = $coupon_code;
                        $arrOrderDetails['duration'] = $duration;
                        $arrOrderDetails['item_description'] = $item_description;
                        //adding values to Db
                        OrdersInformation::create($arrOrderDetails);

                      
                        //uplaoding images
                        $uploaded_files = $request->file('item_images');
                        $path = realpath(dirname(__FILE__) . '/../../../');
                        if (count($uploaded_files) > 0) {
                            foreach ($uploaded_files as $uploaded_file) {
                                $extension = $uploaded_file->getClientOriginalExtension();
                                if ($extension == '') {
                                    $extension = "png";
                                }
                                $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                                Storage::put('public/item-images/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));

                                //storing items images
                                
                                $old_file = $path . '/storage/app/public/item-images/' . $new_file_name;
                                $new_file = $path . '/storage/app/public/item-images/thumbs/' . $new_file_name;
                                $command = "convert " . $old_file . " -resize 200x150^ " . $new_file;
                                exec($command);
                                $arrOrderImages['order_id'] = $order->id;
                                $arrOrderImages['item_image'] = $new_file_name;
                                OrderItemImage::create($arrOrderImages);
                            }
                        }
                        
                      if($service_type!=2)
                      {
                        
                         //sending push notification to star user which is nearest available
                         if ($avalale_driver_id != '') {
                             //storing that user in notification table.
                             $arrOrderNotificationDetails['order_id'] = $order->id;
                             $arrOrderNotificationDetails['user_id'] = $avalale_driver_id;
                             $arrOrderNotificationDetails['message'] = $order_number . " " . Lang::choice('messages.order_assigned', $locale);
                             OrderNotification::create($arrOrderNotificationDetails);

                             $avalale_star_details = UserInformation::where('user_id', $avalale_driver_id)->first();
                             $arr_push_message_ios=array();
                             if (isset($avalale_star_details->device_id) && $avalale_star_details->device_id!='') {
                                 if($service_id==20 || $service_id==28)
                                 {
                                    $arr_push_message = array("sound"=>"default","title"=>"BAGGI","text"=> Lang::choice('messages.new_request_to_bid', $locale),"flag" => 'order_quotation_request', 'message' => Lang::choice('messages.new_request_to_bid', $locale), 'order_id' => $order->id);
                                 }else{
                                 $arr_push_message = array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star', $locale),"flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_star', $locale), 'order_id' => $order->id);
                                 }
                                 $obj_send_push_notification=new SendPushNotification();  
                                 if ($avalale_star_details->device_type == '0') {
                                             //sending push notification star user.
                                            $arr_push_message_android=array();
                                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                                            $arr_push_message_android['priority']="high";
                                            $arr_push_message_android['sound']="default";
                                            $arr_push_message_android['notification']=$arr_push_message;
                                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                          
                                     
                                 } else {
                                            $arr_push_message_ios['to']=$avalale_star_details->device_id;
                                             $arr_push_message_ios['priority']="high";
                                             $arr_push_message_ios['sound']="default";
                                             $arr_push_message_ios['notification']=$arr_push_message;
                                             $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                            
                                     
                                 }
                             }
                         }
                        
                        //soring item images.
                      }
                      
                        //
                        //sending notification email to super global setting admin for it  and country specific admin
                        $siteAdmin = GlobalValues::get('site-email');
                        $adminusers = UserInformation::where('user_type', 1)->get();
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

                        $site_email = GlobalValues::get('site-email');
                        $site_title = GlobalValues::get('site-title');
                        //Assign values to all macros
                        $arr_keyword_values['ORDER_NUMBER'] = $order_number;
                        $arr_keyword_values['ORDER_ID'] = $order->id;
                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        $email_template_title = "emailtemplate::new-order-placed-admin-" . $locale;
                        $email_template_subject = Lang::choice('messages.order_success_admin', $locale);
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

                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_success', $locale));
                    }
                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
                }
               
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.sorry_not_found_your_location', $locale));
        }
      }
        return response()->json($arr_to_return);
    }
    
    //cron job to send notification for all schedule orders
    protected function makeAnScheduleOrder(Request $request) {
        
      //get all scheduled orders
      $allOrders=Order::where('order_type',2)->where('status','0')->get();
      $radious = GlobalValues::get('star-range-radious');
      $schedule_exec_time = GlobalValues::get('schedule_order_exec_time');
      $allOrders=$allOrders->reject(function($schedule_order)
      {
         return ($schedule_order->getServicesDetails->category_id==5);
      })->values();
    
      $locale='en';
      if(count($allOrders)>0)
      {
        foreach($allOrders as $schedule_order)
        {
           
          
            $dt = new DateTime(date('Y-m-d H:i:s'));
          
            //get timezone as per country
            $countryInfo=Country::where('id',$schedule_order->country_id)->first();
            if(count($countryInfo)>0)
            {
                 $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                 $dt->setTimezone($tz);
            }
              
            $date2_val= $dt->format('Y-m-d H:i:s'); 
            $date2=new DateTime($date2_val);
            $date1=new DateTime($schedule_order->order_place_date_time);
            
            $diffdate=date_diff($date1,$date2);
           
        if((($diffdate->y)==0)  && ($diffdate->i<=$schedule_exec_time))
           {
         
           if($schedule_order->order_place_date_time!='')
           {
                $schedule_order->is_cron_execute=1;
                $schedule_order->save();
                $flag_available = 0;
                $avalale_driver_id = 0;
                $user_id = isset($schedule_order->mate_id) ? $schedule_order->mate_id : '';
                $payment_type = isset($schedule_order->payment_type) ? $schedule_order->payment_type : '';
                $order_number = isset($schedule_order->order_unique_id) ? $schedule_order->order_unique_id : '';
                $service_id = isset($schedule_order->service_id) ? $schedule_order->service_id : '0';
                $country_id = isset($schedule_order->country_id) ? $schedule_order->country_id : '0';
                $pick_up_lat = isset($schedule_order->getOrderTransInformation->selected_pickup_lat) ? $schedule_order->getOrderTransInformation->selected_pickup_lat : '';
                $pick_up_long = isset($schedule_order->getOrderTransInformation->selected_pickup_long) ? $schedule_order->getOrderTransInformation->selected_pickup_long : '';
                $distance = isset($schedule_order->getOrderTransInformation->distance_value) ? $schedule_order->getOrderTransInformation->distance_value : '';
                if ($pick_up_lat != '' && $pick_up_long != '') {
                    if ($user_id > 0 && $user_id != '') {

                        $arr_user_data = User::find($user_id);
                        //get user wallter amount

                        if (count($arr_user_data) > 0) {

                            // first checking and get first available star to inform
                            $arrServiceUsers = UserServiceInformation::where('service_id', $service_id)->get();
                            $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                                if (isset($userInfo->user->driverUserInformation->availability))
                                    return ($userInfo->user->driverUserInformation->availability == 0);
                            });

                            //get all user who has only 50 km range

                            if (count($arrServiceUsers) > 0) {
                                $user_ids = "0";
                                $arrayUserIds=array();
                                foreach ($arrServiceUsers as $users_ids) {
                                    if (isset($users_ids->user_id) && $users_ids->user_id != 0 && $distance <= $users_ids->goe_fence_area) {
                                        $user_ids.=",$users_ids->user_id";
                                        $arrayUserIds[]=$users_ids->user_id;
                                    }
                                }
                                //
                                $users = DB::select("call getUserByDistance(" . $pick_up_lat . "," . $pick_up_long . ",'" . $user_ids . "'," . $radious . ")");
                              
                                //check if a user is having any active orders
                                if (count($users) > 0) {
                                    $j = 0;
                                    foreach ($users as $user) {
                                        if(in_array($user->user_id,$arrayUserIds))
                                      {
                                        $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                                        if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                            $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                            //check if user has notification of any orders
                                            $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                            $payment_method_id=isset($schedule_order->payment_type)?$schedule_order->payment_type:'0';
                                            $paymentMethods= UserPaymentMethod::where('user_id',$user->user_id)->where('payment_method_id',$payment_method_id)->first();
                                            $order_cancel=OrderCancelationDetail::where('order_id',$schedule_order->id)->where('user_id',$user->user_id)->first();
                                            if (count($userData) <= 0 && count($order_notification_count)<=0 && count($order_cancel)<=0 && count($paymentMethods)>0) {                                            
                                                $flag_available = 1;
                                                $avalale_driver_id = $user->user_id;
                                                break;
                                            }
                                        }
                                        $j++;
                                    }}
                                }
                            }

                            if ($flag_available == 0) {
                                //storing details of order for which star was not available

                                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
                            } else {
                                $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_id)->first();
                                $fare = 0;
                                if (count($arrDataService) > 0) {
                                    if ($arrDataService->price_type == '1') {
                                        $fare = (double) $arrDataService->base_price;
                                    } else {
                                        if ($distance > $arrDataService->base_km) {
                                            $fare= (double)$arrDataService->base_price;
                                            $fare=$fare + (double) ($distance-$arrDataService->base_km) * ($arrDataService->price_per_km);
                         
//                                            $fare = (double) ($distance) * ($arrDataService->price_per_km);
//                                             $fare=$fare + (double)$arrDataService->base_price;
                                        } else {
                                            $fare = (double) $arrDataService->base_price;
                                        }
                                    }
                                    $arr_to_return = array("error_code" => 0, "fare" => number_format($fare,3));
                                }

                                if($payment_type==2)
                                {
                                    //check wallter amount
                                    $userWalletDetails=UserWalletDetail::where('user_id',$user_id)->orderBy('id', 'desc')->first(['final_amout']);
                                    $wallet_amount=$userWalletDetails->final_amout;
                                    $final_remaining_wallter_amount=0;
                                    //get all actve pending order of this user by using wallte payment
                                    $arrStatus=array("0","1");
                                    $order_amt_total=Order::where('mate_id',$user_id)->whereIn('status',$arrStatus)->sum('fare_amount');
                                    $final_remaining_wallter_amount=$wallet_amount-$order_amt_total;
                                    if($fare>$final_remaining_wallter_amount)
                                    {
                                         $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.wallet_insufficient', $locale));
                                         return response()->json($arr_to_return);
                                    }
                                }



                                //sending push notification to star user which is nearest available
                                if ($avalale_driver_id != '') {
                                    //storing that user in notification table.
                                    $arrOrderNotificationDetails['order_id'] = $schedule_order->id;
                                    $arrOrderNotificationDetails['user_id'] = $avalale_driver_id;
                                    $arrOrderNotificationDetails['message'] = $order_number . " " . Lang::choice('messages.order_assigned', $locale);
                                    OrderNotification::create($arrOrderNotificationDetails);

                                    $avalale_star_details = UserInformation::where('user_id', $avalale_driver_id)->first();
                                    if (isset($avalale_star_details->device_id) && $avalale_star_details->device_id!='') {
                                        $arr_push_message = array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star', $locale),"flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_star', $locale), 'order_id' => $schedule_order->id);
                                        $arr_push_message_ios=array();
                                        $obj_send_push_notification=new SendPushNotification();  
                                        if ($avalale_star_details->device_type == '0') {
                                            //sending push notification star user.
                                            $arr_push_message_android=array();
                                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                                            $arr_push_message_android['priority']="high";
                                            $arr_push_message_android['sound']="default";
                                            $arr_push_message_android['notification']=$arr_push_message;
                                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                          
                                        } else {
                                            
                                             $arr_push_message_ios['to']=$avalale_star_details->device_id;
                                             $arr_push_message_ios['priority']="high";
                                             $arr_push_message_ios['sound']="default";
                                             $arr_push_message_ios['notification']=$arr_push_message;
                                             $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                            
                                           
                                        }
                                    }
                                }

                                

                                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_success', $locale));
                            }
                        } else {
                            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
                        }

                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.sorry_not_found_your_location', $locale));
               }
           }
        }}
        }
        
    }
    
    protected function makeAnScheduleOrderMarine(Request $request) {
        
      //get all scheduled orders
      $allOrders=Order::where('order_type','2')->where('status','0')->where('is_cron_execute','0')->get();
      
      $radious = GlobalValues::get('star-range-radious');
      $schedule_exec_time = GlobalValues::get('schedule_order_exec_time_marine');
      $locale='en';
      $allOrders=$allOrders->reject(function($schedule_order)
      {
         return ($schedule_order->getServicesDetails->category_id!=5);
      })->values();
    
   
      if(count($allOrders)>0)
      {
         
        foreach($allOrders as $schedule_order)
        {
         
            $dt = new DateTime(date('Y-m-d H:i:s'));
          
            //get timezone as per country
            $countryInfo=Country::where('id',$schedule_order->country_id)->first();
            if(count($countryInfo)>0)
            {
                 $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                 $dt->setTimezone($tz);
            }
           
          
             $date2_val= $dt->format('Y-m-d H:i:s'); 
           
             $date2=new DateTime($date2_val);
          
             $date1=new DateTime($schedule_order->order_place_date_time);
            $diffdate=date_diff($date2,$date1);

        if((($diffdate->y)==0) && (($diffdate->i)<=$schedule_exec_time))
        {
    
           if(isset($schedule_order->order_place_date_time))
           {

                $schedule_order->is_cron_execute=1;
                $schedule_order->save();
               
                $flag_available = 0;
                $avalale_driver_ids = array();
                $user_id = isset($schedule_order->mate_id) ? $schedule_order->mate_id : '';
                $payment_type = isset($schedule_order->payment_type) ? $schedule_order->payment_type : '';
                $order_number = isset($schedule_order->order_unique_id) ? $schedule_order->order_unique_id : '';
                $service_id = isset($schedule_order->service_id) ? $schedule_order->service_id : '0';
                $country_id = isset($schedule_order->country_id) ? $schedule_order->country_id : '0';
                $pick_up_lat = ($schedule_order->getOrderTransInformation->selected_pickup_lat) ? $schedule_order->getOrderTransInformation->selected_pickup_lat : '';
                 $pick_up_long = ($schedule_order->getOrderTransInformation->selected_pickup_long) ? $schedule_order->getOrderTransInformation->selected_pickup_long : '';
                $distance = isset($schedule_order->getOrderTransInformation->distance_value) ? $schedule_order->getOrderTransInformation->distance_value : '';
                if ($pick_up_lat != '' && $pick_up_long != '') {
                   
                    if ($user_id > 0 && $user_id != '') {
                       
                        $arr_user_data = User::find($user_id);
                        //get user wallter amount
                        if (count($arr_user_data) > 0) {
                        
                            // first checking and get first available star to inform
                            $arrServiceUsers = UserServiceInformation::where('service_id', $service_id)->get();
                           
                            $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                                if (isset($userInfo->user->driverUserInformation->availability))
                                    return ($userInfo->user->driverUserInformation->availability == '0');
                            });

                            //get all user who has only 50 km range
                            
                            if (count($arrServiceUsers) > 0) {
                                $user_ids = "0";
                                $arrayUserIds=array();
                                foreach ($arrServiceUsers as $users_ids) {
                                    if (isset($users_ids->user_id) && $users_ids->user_id != 0 && $distance <= $users_ids->goe_fence_area) {
                                        $user_ids.=",$users_ids->user_id";
                                        $arrayUserIds[]=$users_ids->user_id;
                                    }
                                }
                                                     //
                                $users = DB::select("call getUserByDistance(" . $pick_up_lat . "," . $pick_up_long . ",'" . $user_ids . "'," . $radious . ")");
                              
                                //check if a user is having any active orders
                                if (count($users) > 0) {
                                    $j = 0;
                                    foreach ($users as $user) {
                                        if(in_array($user->user_id,$arrayUserIds))
                                      {
                                        $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                                        if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                            $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                            //check if user has notification of any orders
                                          
                                            $order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                        
                                            $payment_method_id=isset($schedule_order->payment_type)?$schedule_order->payment_type:'0';
                                            $paymentMethods= UserPaymentMethod::where('user_id',$user->user_id)->where('payment_method_id',$payment_method_id)->first();
                                            $order_cancel=OrderCancelationDetail::where('order_id',$schedule_order->id)->where('user_id',$user->user_id)->first();
                                            if (count($userData) <= 0 && count($order_notification_count)<=0 && count($order_cancel)<=0 && count($paymentMethods)>0) {                                            
                                               
                                                $flag_available = 1;
                                                $avalale_driver_ids[] = $user->user_id;
                                               if($service_id!='20' && $service_id!='28')
                                               {
                                                   break;
                                               }
                                            }
                                        }
                                        $j++;
                                    }}
                                }
                            }

                            if ($flag_available == 0) {
                                //storing details of order for which star was not available

                                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
                            } else {
                                $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_id)->first();
                                $fare = 0;
                                if (count($arrDataService) > 0) {
                                    if ($arrDataService->price_type == '1') {
                                        $fare = (double) $arrDataService->base_price;
                                    } else {
                                        if ($distance > $arrDataService->base_km) {
                                             $fare= (double)$arrDataService->base_price;
                                            $fare=$fare + (double) ($distance-$arrDataService->base_km) * ($arrDataService->price_per_km);
                         
//                                            $fare = (double) ($distance) * ($arrDataService->price_per_km);
//                                            $fare=$fare + (double)$arrDataService->base_price;
                                        } else {
                                            $fare = (double) $arrDataService->base_price;
                                        }
                                    }
                                    
                                }
                           
                                //sending push notification to star user which is nearest available
                                if (count($avalale_driver_ids)>0) {
                                   foreach($avalale_driver_ids as $driver_id_counter)
                                   {
                                    //storing that user in notification table.
                                    $arrOrderNotificationDetails['order_id'] = $schedule_order->id;
                                    $arrOrderNotificationDetails['user_id'] = $driver_id_counter;
                                    $arrOrderNotificationDetails['message'] = $order_number . " " . Lang::choice('messages.new_request_to_bid', $locale);
                                    OrderNotification::create($arrOrderNotificationDetails);
                                    
                                     $avalale_star_details = UserInformation::where('user_id', $driver_id_counter)->first();
                                    if (isset($avalale_star_details->device_id) && $avalale_star_details->device_id!='') {
                                        $arr_push_message=array();
                                        $arr_push_message_ios=array();
                                        if($service_id!='20' && $service_id!='28')
                                        {
                                              $arr_push_message = array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.order_assign_star', $locale),"flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_star', $locale), 'order_id' => $schedule_order->id);
                                        }else{
                                           
                                            $arr_push_message = array("sound"=>"default","title"=>"BAGGI","text"=>Lang::choice('messages.new_request_to_bid', $locale),"flag" => 'order_quotation_request', 'message' => Lang::choice('messages.new_request_to_bid', $locale), 'order_id' => $schedule_order->id);
                                        
                                        }
                                        $obj_send_push_notification=new SendPushNotification();  
                                        if ($avalale_star_details->device_type == '0') {
                                            
                                            $arr_push_message_android=array();
                                            $arr_push_message_android['to']=$avalale_star_details->device_id;
                                            $arr_push_message_android['priority']="high";
                                            $arr_push_message_android['sound']="default";
                                            $arr_push_message_android['notification']=$arr_push_message;
                                            $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                          

                                        } else {
                                             $arr_push_message_ios['to']=$avalale_star_details->device_id;
                                             $arr_push_message_ios['priority']="high";
                                             $arr_push_message_ios['sound']="default";
                                             $arr_push_message_ios['notification']=$arr_push_message;
                                             $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                            
                                        }
                                    }
                                } 
                                  
                                }else{

                                //sending notification email to super global setting admin for it  and country specific admin
                                $siteAdmin = GlobalValues::get('site-email');
                                $adminusers = UserInformation::where('user_type', 1)->get();
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
                                });
                                $site_email = GlobalValues::get('site-email');
                                $site_title = GlobalValues::get('site-title');
                                //Assign values to all macros
                                $arr_keyword_values['ORDER_NUMBER'] = $order_number;
                                $arr_keyword_values['ORDER_ID'] = $schedule_order->id;
                                $arr_keyword_values['SITE_TITLE'] = $site_title;
                                $email_template_title = "emailtemplate::no-star-found-for-schedule-order";
                                $email_template_subject = Lang::choice('messages.no-star-for-schedule-order', $locale);
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
                              }
                                
                            }
                        } else {
                           echo "invalid client";
                        }

                } else {
                    echo "invalid client";
                }
            } else {
                echo "sorry can not foun location";
               }
           }
        }}
      }
        
    }
    
    protected function removeNotificationsForActiveOrders(Request $request) {
        
       $site_email = GlobalValues::get('site-email');
      
      //get all scheduled orders
      $getAllNotifications=OrderNotification::all();
   
      if(count($getAllNotifications)>0){
          
          foreach($getAllNotifications as $order_notification)
          {
                $orderDetails=$order_notification->orderDetailsInfo;$orderDetails;
               if(!(isset($orderDetails->status) && ($orderDetails->status==0)))
               {
                    
                    $order_notification->delete();
                     
               }
          }
      }  
    }
    
    protected function expiredOrdersCron(Request $request) {
      $order_expired_time = GlobalValues::get('order_expired_time');
      $allOrders=Order::where('status',0)->get();
      
      if(count($allOrders)>0)
      {
           
            foreach($allOrders as $expired_orders)
             {
                $flag=0;
                if($expired_orders->order_type=='2')
                {
                    if($expired_orders->is_cron_execute=='1')
                    {
                        
                        $flag=1;
                    }
                }else{
                    $flag=1;
                }
                
                if($flag==1)
                {
                    $dt = new DateTime(date('Y-m-d H:i:s'));
                    
                   //get timezone as per country
                    $countryInfo=Country::where('id',$expired_orders->country_id)->first();
                    if(count($countryInfo)>0)
                    {
                         $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                         $dt->setTimezone($tz);
                    }
                    $date2_val= $dt->format('Y-m-d H:i:s'); 
                    $date2=new DateTime($date2_val);
                    $date1=new DateTime($expired_orders->order_place_date_time);
                    $diffdate=date_diff($date1,$date2);
                   if($expired_orders->order_type=='2')
                    {
                       if((($diffdate->h)==0)  && ($diffdate->i>$order_expired_time))
                        {
                            $expired_orders->status=4;
                            $expired_orders->save();
                        }
                    } else{
                        if((($diffdate->h)>0)  || ($diffdate->i>$order_expired_time))
                        {
                            $expired_orders->status=4;
                             $expired_orders->save();
                        }
                      
                    }
                    
                }
                   
               
             }
      }
    }
    
     protected function getfareEstimate(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $distance=0;
        $fare = 0;
        $flag = 0;    
        $booking_type=1;
        $nearest_latitude=0;
        $nearest_longtitude=0;
        $near_distance=0;
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $service_id = isset($request['service_id']) ? $request['service_id'] : '0';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $schedule_type = isset($request['type']) ? $request['type'] : '1';
        $distance = isset($request['distance']) ? $request['distance'] : '0';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '0';
        $current_long = isset($request['current_long']) ? $request['current_long'] : '0';
        $radious = GlobalValues::get('star-range-radious');
        \App::setLocale($locale);
      
        //get service type
        $service_details=Service::where('id',$service_id)->first();
       
       if(count($service_details)>0)
       {
        if($service_details->service_type=='0' || $service_details->service_type=='3' )
        {
             $booking_type=1;
        }else  if($service_details->service_type=='1' )
        {
             $booking_type=2;
        }
        else  if($service_details->service_type=='2' )
        {
             if($schedule_type==0)
             {
                 $booking_type=1;
             }else{
                 $booking_type=2;
             }
        }
       

        if ($booking_type == 1 || $booking_type == 3) {
            //check if the service is type of 
            $arrServiceUsers = UserServiceInformation::where('service_id', $service_id)->get();
            $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                if (isset($userInfo->user->driverUserInformation->availability))
                    return ($userInfo->user->driverUserInformation->availability == 0);
            });
            if (count($arrServiceUsers) > 0) {
                $user_ids = "0";
                $arrayUserIds=array();
                foreach ($arrServiceUsers as $users_ids) {
                    if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                        $user_ids.=",$users_ids->user_id";
                        $arrayUserIds[]=$users_ids->user_id;
                    }
                }
                //
                $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");
              
                //check if a user is having any active orders
                if (count($users) > 0) {
                    $j = 0;
                    foreach ($users as $user) {
                        if(in_array($user->user_id,$arrayUserIds))
                         {
                        $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                       
                        if ($userDetailsStatus->user_status == '1') {
                          //  $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                            //$order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
//                            if (count($userData) <= 0 && count($order_notification_count)<=0) {
                                $userAddressDetails = UserAddress::where('user_id', $user->user_id)->first();
                                $flag = 1;
                                $near_distance =number_format($user->distance,3);
                                $nearest_latitude=$userAddressDetails->latitude;
                                $nearest_longtitude=$userAddressDetails->longitude;
                                break;
//                            }
                        }
                        $j++;
                    }}
                        }
                    }
                } else {
                    $arrServiceUsers = UserServiceInformation::where('service_id', $service_id)->get();
                    $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                        if (isset($userInfo->user->driverUserInformation->availability))
                            return ($userInfo->user->driverUserInformation->availability == 0);
                    });

                    if (count($arrServiceUsers) > 0) {
                        $user_ids = "0";
                        $arrayUserIds=array();
                        foreach ($arrServiceUsers as $users_ids) {
                            if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                $user_ids.=",$users_ids->user_id";
                                $arrayUserIds[]=$users_ids->user_id;
                            }
                        }
                        //
                        $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");

                        //check if a user is having any active orders
                        if (count($users) > 0) {

                            $j=0;
                             foreach ($users as $user) {
                                 if(in_array($user->user_id,$arrayUserIds))
                                {
                                 $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();                       
                                  if ($userDetailsStatus->user_status == '1') {
                                        $flag = 1;
                                        $userAddressDetails = UserAddress::where('user_id', $user->user_id)->first();
                                        $near_distance =number_format($user->distance,3);
                                        $nearest_latitude=$userAddressDetails->latitude;
                                        $nearest_longtitude=$userAddressDetails->longitude;
                                        break;

                                }
                                $j++;
                             }}
                }
            }
        }
        
        
        if ($locale != '') {
            if ($flag == 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
            } else {
                //return fare estimate
                $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_id)->first();
                $distance_value=(float)str_replace(" km","",$distance);
             
                if($distance=='' || $distance_value=='' || $distance_value=='0')
                {
                    $distance_value=$near_distance;
                }
              
                if (count($arrDataService) > 0) {
                    if ($arrDataService->price_type == '1') {
                        $fare = (double) $arrDataService->base_price;
                    } else {
                        if ($distance_value > $arrDataService->base_km) {
                             $fare= (double)$arrDataService->base_price;
                            $fare=$fare + (double) ($distance_value-$arrDataService->base_km) * ($arrDataService->price_per_km);
                         
//                            $fare = (double) ($distance_value) * ($arrDataService->price_per_km);
//                            $fare=$fare + (double)$arrDataService->base_price;
//                           
                        } else {
                            $fare = (double) $arrDataService->base_price;
                        }
                    }
                    $arr_to_return = array("error_code" => 0,"distance"=>$near_distance, "nearest_latitude"=>$nearest_latitude,"nearest_longtitude"=>$nearest_longtitude, "fare" => number_format($fare, 3));
                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.service_temprary_unavailable', $locale));
                }
            }
        }
       }else{
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.service_temprary_unavailable', $locale));
       }
        
        return response()->json($arr_to_return);
    }
    
     protected function getfareEstimateByCategory(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $distance=0;
        $fare = 0;
        $flag = 0;    
        $booking_type=1;
        $nearest_latitude=0;
        $nearest_longtitude=0;
        $near_distance=0;
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $category_id = isset($request['category_id']) ? $request['category_id'] : '0';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $schedule_type = isset($request['type']) ? $request['type'] : '1';
        $distance = isset($request['distance']) ? $request['distance'] : '0';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '0';
        $current_long = isset($request['current_long']) ? $request['current_long'] : '0';
        $radious = GlobalValues::get('star-range-radious');
        \App::setLocale($locale);
      
        //get service type
        //get all service as per the category
        $arrServiceEstimates=array();
       $serviceData=Service::where('category_id',$category_id)->get();
       if(count($serviceData)>0)
      {
           $k=0;
        foreach($serviceData as $service_detial)
        {
            $fare=0;
            $service_details=Service::where('id',$service_detial->id)->first();

            if(count($service_details)>0)
            {
             if($service_details->service_type=='0' || $service_details->service_type=='3' )
             {
                  $booking_type=1;
             }else  if($service_details->service_type=='1' )
             {
                  $booking_type=2;
             }
             else  if($service_details->service_type=='2' )
             {
                  if($schedule_type==0)
                  {
                      $booking_type=1;
                  }else{
                      $booking_type=2;
                  }
             }
         $booking_type=1;    
         $distance_value=(Double)str_replace(" km","",$distance);
         $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_detial->id)->first();
          
         if($distance=='' || $distance_value=='' || $distance_value=='0')
           {
             if ($booking_type == 1 || $booking_type == 3) {
                 //return fare estimate
                   
                   
                 //check if the service is type of 
                 $arrServiceUsers = UserServiceInformation::where('service_id', $service_detial->id)->get();
                 $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                     if (isset($userInfo->user->driverUserInformation->availability))
                         return ($userInfo->user->driverUserInformation->availability == 0);
                 });
                 if (count($arrServiceUsers) > 0) {
                     $user_ids = "0";
                     $arrayUserIds=array();
                     foreach ($arrServiceUsers as $users_ids) {
                         if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                             $user_ids.=",$users_ids->user_id";
                             $arrayUserIds[]=$users_ids->user_id;
                         }
                     }
                     //
                     $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");

                     //check if a user is having any active orders
                     if (count($users) > 0) {
                         $j = 0;
                         foreach ($users as $user) {
                            if(in_array($user->user_id,$arrayUserIds))
                              {
                             $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();

                             if ($userDetailsStatus->user_status == '1') {
                               //  $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                 //$order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
     //                            if (count($userData) <= 0 && count($order_notification_count)<=0) {
                                     $userAddressDetails = UserAddress::where('user_id', $user->user_id)->first();
                                     $flag = 1;
                                     $near_distance =(double)($user->distance);
                                     $nearest_latitude=$userAddressDetails->latitude;
                                     $nearest_longtitude=$userAddressDetails->longitude;
                                     break;
     //                            }
                             }
                             $j++;
                         }}
                             }
                      }
             }
           }else{
               $flag = 1;
           }
               if ($flag == 0) {
                    $arrServiceEstimates[$k]['service_id']=$service_detial->id;
                    $arrServiceEstimates[$k]['price']=0;
                    $arrServiceEstimates[$k]['distance']=$distance_value;
                 } else {   
                     
                    if($distance=='' || $distance_value=='' || $distance_value=='0')
                    {
                        $distance_value=$near_distance;
                    }
               
                 if (count($arrDataService) > 0) {
                    if ($arrDataService->price_type == '1') {
                        $fare = (double) $arrDataService->base_price;
                    } else {
                        
                        if ($distance_value > $arrDataService->base_km) {
                              $fare= (double)$arrDataService->base_price;
                            $fare=$fare + (double) ($distance_value-$arrDataService->base_km) * ($arrDataService->price_per_km);
                         
//                            $fare = (double) ($distance_value) * ($arrDataService->price_per_km);
//                            $fare=$fare + (double)$arrDataService->base_price;
                           
                        } else {
                            $fare = (double) $arrDataService->base_price;
                        }
                    }
                  
                }
                if($service_detial->id!=20 && $service_detial->id!=15 && $service_detial->id!=17  && $service_detial->id!=32 && $service_detial->id!=28 && $distance_value<=$service_detial->max_range)
                {
                    
                    $arrServiceEstimates[$k]['service_id']=$service_detial->id;
                    $arrServiceEstimates[$k]['price']=number_format($fare,2);
                    $arrServiceEstimates[$k]['distance']=$distance_value;
                }else{
                    $arrServiceEstimates[$k]['service_id']=$service_detial->id;
                    $arrServiceEstimates[$k]['price']=0;
                    $arrServiceEstimates[$k]['distance']=$distance_value;
                }
           }

       
        }
        $k++;
        }
         $arr_to_return = array("error_code" => 1,"data" =>$arrServiceEstimates);
      
      }else{
             $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
       }
       
        return response()->json($arr_to_return);
    }
    
    protected function getFareEstimateForQuotation(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $fare = 0; 
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $service_id = isset($request['service_id']) ? $request['service_id'] : '0';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $number_of_person = isset($request['number_of_person']) ? $request['number_of_person'] : '0';
        $duration = isset($request['duration']) ? $request['duration'] : '0';      
        $radious = GlobalValues::get('star-range-radious');
        \App::setLocale($locale);
         if ($service_id==20 || $service_id==28) { 
                    $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_id)->first();
                    if ($duration > $arrDataService->base_km) {
                        $fare = (double) ($duration) * ($arrDataService->price_per_km);
                        $fare=$fare + (double)$arrDataService->base_price;
                        
                    } else {
                        $fare = (double) $arrDataService->base_price;
             }
           $fare=$fare* $number_of_person;      
           $arr_to_return = array("error_code" => 0,"fare" => number_format($fare, 3));
         }else{
           $arr_to_return = array("error_code" => 1,"msg" => Lang::choice('messages.service_invalid', $locale));  
         }
        return response()->json($arr_to_return);
    }
   
    protected function socialConnect(Request $request) {

        $arr_to_return = array();
        //getting all inputs
        $first_name = isset($request['first_name']) ? $request['first_name'] : '';
        $last_name = isset($request['last_name']) ? $request['last_name'] : '';
        $username = isset($request['mobile_number']) ? $request['mobile_number'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $email = isset($request['email']) ? $request['email'] : '';
        $password = isset($request['password']) ? $request['password'] : '';
        $profile_picture = isset($request['profile_picture']) ? $request['profile_picture'] : '';
        $country = isset($request['country']) ? $request['country'] : '';
        $region = isset($request['region']) ? $request['region'] : '';
        $city = isset($request['city']) ? $request['city'] : '';
        $address = isset($request['address']) ? $request['address'] : '';
        $latitude = isset($request['latitude']) ? $request['latitude'] : '';
        $lontitude = isset($request['lontitude']) ? $request['lontitude'] : '';
        $device_type = isset($request['device_type']) ? $request['device_type'] : '';
        $device_id = isset($request['device_id']) ? $request['device_id'] : '';
        $facebook_id = isset($request['facebook_id']) ? $request['facebook_id'] : '';
        $twitter_id = isset($request['twitter_id']) ? $request['twitter_id'] : '';
        $google_id = isset($request['google_id']) ? $request['google_id'] : '';
        $social_type = isset($request['social_type']) ? $request['social_type'] : '';
        $user_type = 3;
        \App::setLocale($locale);
        //checking if user is not using existing details

       
        $arrUserName = User::where("username", $username)->first();
        if (count($arrUserName) > 0 && ($arrUserName->userInformation->mobile_code == $mobile_code)) {

            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_already_exist', $locale));
            return response()->json($arr_to_return);
        }
       if ($email != '') {
            $arrUserEmail = User::where("email", $email)->first();       
            if (count($arrUserEmail) > 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.email_already_exist', $locale));
                return response()->json($arr_to_return);
            }
       }
        //checking for social media already connect

        if ($social_type != '') {
            $arrSocialUser = array();
            if ($social_type == 'facebook') {
                $arrSocialUser = UserInformation::where('facebook_id', $facebook_id)->first();
            } else if ($social_type == 'twitter') {
                $arrSocialUser = UserInformation::where('twitter_id', $twitter_id)->first();
            } else if ($social_type == 'google') {
                $arrSocialUser = UserInformation::where('google_id', $google_id)->first();
            }


            if (count($arrSocialUser) > 0) {
                $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.login_success', $locale), "user" => $arrSocialUser->user, "userInformation" => $arrSocialUser);
            }

            if ($username != '') {

                //creating user
                if ($email != '') {
                    $created_user = User::create([
                                'username' => $username,
                                'email' => $email,
                    ]);
                } else {
                    $created_user = User::create([
                                'username' => $username
                    ]);
                }

                //entering details in user Information Table.
                $arr_userinformation["profile_picture"] = $profile_picture;
                $arr_userinformation["first_name"] = $first_name;
                $arr_userinformation["last_name"] = $last_name;
                $arr_userinformation["user_mobile"] = $username;
                $arr_userinformation["mobile_code"] = str_replace(" ","",$mobile_code);
                $arr_userinformation["device_id"] = $device_id;
                $arr_userinformation["device_type"] = $device_type;
                $arr_userinformation["user_status"] = 1;
                $arr_userinformation["twitter_id"] = $twitter_id;
                $arr_userinformation["facebook_id"] = $facebook_id;
                $arr_userinformation["google_id"] = $google_id;
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
                $arr_userAddress["longitude"] = $lontitude;
                if ($latitude != '' && $country != '') {
                    UserAddress::create($arr_userAddress);
                }
                
                //getting country inor by mobile code
                $mobile_code_country=str_replace("+","",$mobile_code);
                $mobile_code_country="+".$mobile_code_country;
                 $mobile_code_country=str_replace(" ","",$mobile_code_country);
                $county_info=Country::where('country_code',$mobile_code_country)->first();
                // asign role to respective user		
                $userRole = Role::where("slug", "registered.user")->first();

                $created_user->attachRole($userRole);

                //sending email for successfull registration
                //getting from global setting
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $arr_keyword_values = array();
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $first_name;
                $arr_keyword_values['LAST_NAME'] = $last_name;
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                $email_subject = Lang::choice('messages.register_email_subject', $locale);
                $tempate_name = "emailtemplate::registration-successfull-" . $locale;
                if (isset($created_user->email)) {
                    Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($created_user, $email_subject, $site_email, $site_title) {

                        $message->to($created_user->email)->subject($email_subject)->from($site_email, $site_title);
                    });
                }
                if ($created_user->id) {
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.register_successfull', $locale), "data" => $created_user,"country_info"=>$county_info, "userInformation" => $created_user->userInformation);
                    return response()->json($arr_to_return);
                }
            }
        }
    }
    
 
}
