<?php
namespace App\Http\Controllers;
use App\User;
use App\UserInformation;
use App\UserAddress;
use App\UserOtpCodes;
use App\DeliveryuserBalanceDetail;
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
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\PiplModules\contactrequest\Models\ContactRequestCategory;
use App\PiplModules\contactrequest\Models\ContactRequest;
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
use App\UserCreditCard;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use Storage;
use App\UserPaymentMethod;
use DateTime;
use DateTimeZone; 
use Srmklive\PayPal\Services\ExpressCheckout;
use Srmklive\PayPal\Services\AdaptivePayments;
use App\PiplModules\wallethistory\Models\UserWalletDetail;
use PayPal;
use Session;
use App\PanaceaClasses\SendSms;
use App\PanaceaClasses\SendPushNotification;

class DeliveryPaymentController extends Controller {
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

use AuthenticatesAndRegistersUsers,
    ThrottlesLogins;

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

//   protected function paymentForOrder(Request $request,$order_number,$locale) {
//        if($locale!='ar')
//        {
//            $locale='en';
//        }
//       $provider = new ExpressCheckout;      // To use express checkout.
//       $provider = PayPal::setProvider('express_checkout');
//       $provider = express_checkout();  
//       //getting order details
//       $order_details=Order::where('order_unique_id',$order_number)->first();
//       
//      if(count($order_details)>0)
//      {
//        if(isset($order_details->total_amount) && $order_details->total_amount!=0.00)
//        {
//          Session::put('order_number',$order_number);
//          Session::put('locale',$locale);
//         //getting country currency
//          $country_id=$order_details->country_id;
//          $currency_details=Country::where('id',$country_id)->first();
//        
//          //$provider->setCurrency('AED');
//          $options = [
//            'BRANDNAME' => 'DLVR4ALL',
//            'LOGOIMG' => 'http://dlvr4all.com/public/media/front/images/logo.png',
//            'CHANNELTYPE' => 'Merchant'
//         ];
//         $provider->addOptions($options);
//         $data = [];
//         $data['items'] = [
//            [
//                'name' => 'Order Comptition Payment',
//                'price' => $order_details->total_amount,
//                'qty' => 1
//            ],
//            
//        ];
//        $data['invoice_id'] = 1;
//        $data['invoice_description'] = "Order complete payment";
//        $data['return_url'] = url('/api/complete-order-payment-success');
//        $data['cancel_url'] = url('/api/payment-fail');
//        $total = 0;
//        foreach($data['items'] as $item) {
//            $total += $item['price']*$item['qty'];
//        }
//
//        $data['total'] = $total;
//        $response = $provider->setExpressCheckout($data);
//     
//       if(isset($response['paypal_link']))
//       {
//         return redirect($response['paypal_link']);
//       }else{
//             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_invalid',$locale));
//             return response()->json($arr_to_return);
//       }
//      }else{
//             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_invalid',$locale));
//             return response()->json($arr_to_return);
//        }
//      }else{
//           $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_invalid',$locale));
//           return response()->json($arr_to_return);
//      }
//      
//    }
   protected function paymentForOrderCompleteTap(Request $request,$order_number,$locale) 
   {
        //
        if($locale!='ar')
        {
            $locale='en';
        }
       Session::put('locale',$locale);
       Session::put('order_number',$order_number);
       \App::setLocale($locale);
       $order_details=Order::where('order_unique_id',$order_number)->first();
      if(count($order_details)>0)
      {
          
         if(isset($order_details->total_amount) && $order_details->total_amount!=0.00)
       {
        $amount=$order_details->total_amount;
        $userDetails=User::where('id',$order_details->mate_id)->first();
        $countryInfo=Country::where('id',$order_details->country_id)->first();
       // $APIKey = env('tap_api_key', '1tap7');
        $APIKey = "1tap7";
        // $MerchantID =  env('tap_merchant_id', '1014');
        $MerchantID =  "1014";
        $UserName = env('tap_user_name', 'test');
        $UserName = "test";
      $UserPass = "test";
      $UserPass = env('tap_user_pass', 'test');
        $ref = rand(10999,99999999899);
        $Mobile = isset($userDetails->userInformation->user_mobile)?$userDetails->userInformation->user_mobile:''; 
        $currency=isset($countryInfo->currency_code)?str_replace("SR","SAR",$countryInfo->currency_code):'';
        $currency=isset($countryInfo->currency_code)?str_replace("KD","KWD",$countryInfo->currency_code):'';
        $CurrencyCode = (isset($currency)&&$currency!='')?$currency:'KWD';
       
        $Total = $order_details->total_amount;
         $str = 'X_MerchantID'.$MerchantID.'X_UserName'.$UserName.'X_ReferenceID'.$ref.'X_Mobile'.$Mobile.'X_CurrencyCode'.$CurrencyCode.'X_Total'.$Total.''; 
         $hashstr = hash_hmac('sha256', $str, $APIKey); 
       //getting order details
       //creating parameters
        
        $arrCustomerDetails['CustomerDC']['email']=$userDetails->email;
        $arrCustomerDetails['CustomerDC']['Floor']="0";
        $arrCustomerDetails['CustomerDC']['Gender']=($userDetails->userInformation->gender=='2')?'F':'M';
        $arrCustomerDetails['CustomerDC']['ID']=($userDetails->id);
        $arrCustomerDetails['CustomerDC']['Mobile']=isset($userDetails->userInformation->user_mobile)?$userDetails->userInformation->user_mobile:'';        $arrCustomerDetails['CustomerDC']['Name']="ewqewqewq";
        $nationality_name='';
        if(isset($userDetails->userInformation->nationality) && $userDetails->userInformation->nationality!=0)
        {
            $nationality=  App\Nationality::where('id',$userDetails->userInformation->nationality)->first();
            if($locale=='ar')
            {
                $nationality_name=$nationality->country_name_arabic;
            }else{
                  $nationality_name=$nationality->country_name;
            }
        }
        $arrCustomerDetails['CustomerDC']['Nationality']=$nationality_name;
        $arrCustomerDetails['CustomerDC']['Street']="";
        $arrCustomerDetails['CustomerDC']['Area']="";
        $arrCustomerDetails['CustomerDC']['CivilID']="";
        $arrCustomerDetails['CustomerDC']['Building']="";
        $arrCustomerDetails['CustomerDC']['Apartment']="";
        $arrCustomerDetails['CustomerDC']['DOB']="";
        
        $arrCustomerDetails['lstProductDC'][0]['CurrencyCode']=$CurrencyCode;
        $arrCustomerDetails['lstProductDC'][0]['ImgUrl']=url('public/media/front/images/logo.png');
        $arrCustomerDetails['lstProductDC'][0]['Quantity']="1";
        $arrCustomerDetails['lstProductDC'][0]['TotalPrice']=$amount;
        $arrCustomerDetails['lstProductDC'][0]['UnitDesc']=Lang::choice('messages.recharge_wallet', $locale);
        $arrCustomerDetails['lstProductDC'][0]['UnitName']=Lang::choice('messages.recharge_wallet', $locale);
        $arrCustomerDetails['lstProductDC'][0]['UnitPrice']=$amount;
        $arrCustomerDetails['lstProductDC'][0]['VndID']=($userDetails->id);
        $arrCustomerDetails['lstGateWayDC'][0]['Name']="ALL";
        $arrCustomerDetails['MerMastDC']['AutoReturn']="Y";
        $arrCustomerDetails['MerMastDC']['ErrorURL']=url('api/complete-order-payment-error');
        $arrCustomerDetails['MerMastDC']['HashString']=$hashstr;
        $arrCustomerDetails['MerMastDC']['LangCode']=  strtoupper($locale);
        $arrCustomerDetails['MerMastDC']['MerchantID']=$MerchantID;
        $arrCustomerDetails['MerMastDC']['Password']=$UserPass;
        $arrCustomerDetails['MerMastDC']['PostURL']=url('');
        $arrCustomerDetails['MerMastDC']['ReferenceID']=$ref;
        $arrCustomerDetails['MerMastDC']['ReturnURL']=url('api/complete-order-payment-success');
        $arrCustomerDetails['MerMastDC']['UserName']=$UserName;
       
        $strCustomerDetails= json_encode($arrCustomerDetails);
       
         $curl = curl_init();

          curl_setopt_array($curl, array(
       //  CURLOPT_URL => 'https://www.gotapnow.com/TapWebConnect/Tap/WebPay/PaymentRequest',
       CURLOPT_URL => 'http://tapapi.gotapnow.com/TapWebConnect/Tap/WebPay/PaymentRequest',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 60,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $strCustomerDetails,
          CURLOPT_HTTPHEADER => array(
            "content-type: application/json"
          ),
        ));

        $response = curl_exec($curl);
    
        $err = curl_error($curl);

        curl_close($curl);

            if ($err) {
               $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_payment_tap', $locale));  
                return response()->json($arr_to_return);
            } else {
              $response_data=json_decode($response);
          
              if(count($response_data->PaymentURL)>0)
              {
                  return redirect($response_data->PaymentURL);exit;
              }else{
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_payment_tap', $locale));  
                    return response()->json($arr_to_return);
              }
            }
      }}else{
          
      }
      
    }
   public function  orderCompletePaymentSuccess(Request $request){
    $agent_percentage=GlobalValues::get('agent-percentage');
    $star_percentage=GlobalValues::get('star-percentage');
    $company_percentage=(100-($agent_percentage+$star_percentage));
    $admin_percentage=(100-($agent_percentage+$star_percentage)); 
     $Tap_Ref = $_REQUEST['ref'];
     $Txn_Result = $_REQUEST['result'];
     $Txn_OrderID = $_REQUEST['trackid'];
    
     $Hash = $_REQUEST['hash']; 
     
  $APIKey = '1tap7';
    
   $MerchantID =  '1014'; 
    
    $toBeHashedString = 'x_account_id'.$MerchantID.'x_ref'.$Tap_Ref.'x_result'.$Txn_Result.'x_referenceid'.$Txn_OrderID.'';
    $myHashStr = hash_hmac('sha256', $toBeHashedString, $APIKey);
    $locale= Session::get('locale');
    if($myHashStr == $Hash)
    {
     
        $order_number= Session::get('order_number');
        
        $order_details=Order::where('order_unique_id',$order_number)->first();
      
         if($order_details->id !=''){
                            
            $order_details->is_payment_done=1;                
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
            $order_details->save();
            //amount to star account
            $fare_amount=isset($order_details->total_amount)?$order_details->total_amount:$order_details->fare_amount;
            //adding amount to user walltes 80% to star
            $user_id=isset($order_details->driver_id)?$order_details->driver_id:'0';
                //get user final amount
                $star_wallet_data = UserWalletDetail::where('user_id',$user_id)->orderBy('id', 'desc')->first(['final_amout']);
                $prev_file_amount=isset($star_wallet_data->final_amout)?$star_wallet_data->final_amout:'0';
                $commision_star=(($fare_amount)*($star_percentage/100));
                $walletAmount['user_id']=$user_id;
                $walletAmount['transaction_amount']=(($fare_amount)*($star_percentage/100));
                $walletAmount['final_amout']=(double)($prev_file_amount+$commision_star);
                $walletAmount['trans_desc']=Lang::choice('messages.trans_desc',$locale);
                $walletAmount['transaction_type']=0;
                $walletAmount['payment_type']=1;
                $walletAmount['order_id']=$order_details->id;
                //check for same order
                $walletAmountDetails=array();
                $walletAmountDetails['user_id']=$user_id;
                $walletAmountDetails['star_amount']=(($fare_amount)*($star_percentage/100));
                $walletAmountDetails['total_amount']=(double)($fare_amount);
                $walletAmountDetails['pay_type']='1';
                $walletAmountDetails['order_id']=$order_details->id;
                
                $user_total_order_amount_data = UserInformation::where('user_id',$user_id)->first();
                $star_wallet_data_check = UserWalletDetail::where('user_id',$user_id)->where('order_id',$order_details->id)->first();
                if(count($star_wallet_data_check)<=0)
                 {
                    UserWalletDetail::create($walletAmount);
                    DeliveryuserBalanceDetail::create($walletAmountDetails);
                    $user_total_order_amount=(isset($user_total_order_amount_data->total_order_amount))?$user_total_order_amount_data->total_order_amount:0;
                    $user_total_order_amount=(double)$user_total_order_amount+($order_details->total_amount);
                    $user_total_order_amount_data->total_order_amount=$user_total_order_amount;
                    $user_total_order_amount_data->save();
                 }

                $order_details->star_commission=$commision_star;
                $order_details->save();
                $is_agent_flag=0;                    
                $star_country=0;
                $star_state=0;
                $star_city=0;
                $agentDetails=array();
                $agent_id=0;
                $userAddress=UserAddress::where('user_id',$user_id)->first();
                 $userInformationData=  UserInformation::where('user_id',$user_id)->first();
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
                                $agent_city = $address->user_country;
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
               if($agent_id!=0)
               {
                   //adding 10% to agent
                   $agent_wallet_data = UserWalletDetail::where('user_id',$agent_id)->orderBy('id', 'desc')->first(['final_amout']);
                   $prev_file_amount_agent=isset($agent_wallet_data->final_amout)?$agent_wallet_data->final_amout:'0';
                   $commision_agent=(($fare_amount)*($agent_percentage/100));
                   $walletAmountAgent['user_id']=$agent_id;
                   $walletAmountAgent['transaction_amount']=(($fare_amount)*($agent_percentage/100));
                   $walletAmountAgent['final_amout']=(double)($prev_file_amount_agent+$commision_agent);
                   $walletAmountAgent['trans_desc']=Lang::choice('messages.trans_desc',$locale);
                   $walletAmountAgent['transaction_type']=0;
                   $walletAmountAgent['payment_type']=1;
                   $walletAmountAgent['order_id']=$order_details->id;

                  $star_wallet_data_check = UserWalletDetail::where('user_id',$agent_id)->where('order_id',$order_details->id)->first();
                 if(count($star_wallet_data_check)<=0)
                 {
                     UserWalletDetail::create($walletAmountAgent);
                 }
               }
            
            
            //end amount to star account
          
            //  sending push notification to mate and star for rating
                  //get mate details.
                    $starDetailsPush=UserInformation::where('user_id',$order_details->driver_id)->first();
                    $arr_push_message=array("sound"=>"default","flag"=>'order_success','message'=>Lang::choice('messages.payment_success',$locale),'order_id'=>$order_details->id);
                    if(isset($starDetailsPush->device_id) && $starDetailsPush->device_id!='')
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
                            $arr_push_message_ios['notification']=$arr_push_message;
                            $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                       }
                    }

                   //seding to mate

                    $mateDetailsPush=UserInformation::where('user_id',$order_details->mate_id)->first();
                    $arr_push_message=array("sound"=>"default","flag"=>'order_success','message'=>Lang::choice('messages.payment_success',$locale),'order_id'=>$order_details->id);
                    if(isset($mateDetailsPush->device_id) && $mateDetailsPush->device_id!='')
                    {   
                       $obj_send_push_notification=new SendPushNotification();    
                       if($mateDetailsPush->device_type=='0')
                       {
                          //sending push notification customer user.
                          $arr_push_message_android=array();
                          $arr_push_message_android['to']=$mateDetailsPush->device_id;
                          $arr_push_message_android['priority']="high";
                          $arr_push_message_android['sound']="default";
                          $arr_push_message_android['notification']=$arr_push_message;
                          $obj_send_push_notification->androidPushNotificaton(json_encode($arr_push_message_android)); 
                        
                       }else{
                            $arr_push_message_ios['to']=$mateDetailsPush->device_id;
                            $arr_push_message_ios['priority']="high";
                            $arr_push_message_ios['notification']=$arr_push_message;
                            $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                          
                       }
                    } 
                    $arr_to_return = array("error_code" => 0, "msg" =>Lang::choice('messages.payment_success',$locale));       
                    return response()->json($arr_to_return);   
        }
        else{
            $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.order_not_found',$locale));       
            return response()->json($arr_to_return);   
        }
    }else{
        $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.order_id_should_not_blank',$locale));       
        return response()->json($arr_to_return);   
    }
     return response()->json($arr_to_return);
   }
   public function  orderCompletePaymentError(Request $request){
      
      $arr_to_return = array("error_code" => 2, "msg" =>Lang::choice('messages.order_id_should_not_blank',$locale));       
      
     return response()->json($arr_to_return);
   }
  
   protected function paymentForOrderTap(Request $request,$user_id,$amount,$locale) {
       
       //
        if($locale!='ar')
        {
            $locale='en';
        }
       Session::put('user_id',$user_id);
       Session::put('locale',$locale);
       \App::setLocale($locale);
       $userDetails=User::where('id',$user_id)->first();
      if(count($userDetails)>0 && doubleval($amount)>0)
      {
    
         $user_mobile_code=$userDetails->userInformation->mobile_code;
          $user_mobile_code_with_plus="+".trim($user_mobile_code);
          $countryInfo=Country::where('country_code',$user_mobile_code_with_plus)->first();
          if(count($countryInfo)<=0)
          {
              $countryInfo=Country::where('country_code',$user_mobile_code)->first();
          }
        
        // $APIKey = env('tap_api_key', '1tap7');
        $APIKey = "1tap7";
        // $MerchantID =  env('tap_merchant_id', '1014');
        $MerchantID =  "1014";
        $UserName = env('tap_user_name', 'test');
        $UserName = "test";
      $UserPass = "test";
      $UserPass = env('tap_user_pass', 'test');
        $ref = rand(10999,99999999899);
        $Mobile = isset($userDetails->userInformation->user_mobile)?$userDetails->userInformation->user_mobile:''; 
        $currency=isset($countryInfo->currency_code)?str_replace("SR","SAR",$countryInfo->currency_code):'';
        $currency=isset($countryInfo->currency_code)?str_replace("KD","KWD",$countryInfo->currency_code):'';
        $CurrencyCode = isset($currency)?$currency:'KWD';
       
        $Total = $amount;
         $str = 'X_MerchantID'.$MerchantID.'X_UserName'.$UserName.'X_ReferenceID'.$ref.'X_Mobile'.$Mobile.'X_CurrencyCode'.$CurrencyCode.'X_Total'.$Total.''; 
         $hashstr = hash_hmac('sha256', $str, $APIKey); 
       //getting order details
       //creating parameters
        
        $arrCustomerDetails['CustomerDC']['email']=$userDetails->email;
        $arrCustomerDetails['CustomerDC']['Floor']="0";
        $arrCustomerDetails['CustomerDC']['Gender']=($userDetails->userInformation->gender=='2')?'F':'M';
        $arrCustomerDetails['CustomerDC']['ID']=($userDetails->id);
        $arrCustomerDetails['CustomerDC']['Mobile']=isset($userDetails->userInformation->user_mobile)?$userDetails->userInformation->user_mobile:'';        $arrCustomerDetails['CustomerDC']['Name']="ewqewqewq";
        $nationality_name='';
        if(isset($userDetails->userInformation->nationality) && $userDetails->userInformation->nationality!=0)
        {
            $nationality=  App\Nationality::where('id',$userDetails->userInformation->nationality)->first();
            if($locale=='ar')
            {
                $nationality_name=$nationality->country_name_arabic;
            }else{
                  $nationality_name=$nationality->country_name;
            }
        }
        $arrCustomerDetails['CustomerDC']['Nationality']=$nationality_name;
        $arrCustomerDetails['CustomerDC']['Street']="";
        $arrCustomerDetails['CustomerDC']['Area']="";
        $arrCustomerDetails['CustomerDC']['CivilID']="";
        $arrCustomerDetails['CustomerDC']['Building']="";
        $arrCustomerDetails['CustomerDC']['Apartment']="";
        $arrCustomerDetails['CustomerDC']['DOB']="";
        
        $arrCustomerDetails['lstProductDC'][0]['CurrencyCode']=$CurrencyCode;
        $arrCustomerDetails['lstProductDC'][0]['ImgUrl']=url('public/media/front/images/logo.png');
        $arrCustomerDetails['lstProductDC'][0]['Quantity']="1";
        $arrCustomerDetails['lstProductDC'][0]['TotalPrice']=$amount;
        $arrCustomerDetails['lstProductDC'][0]['UnitDesc']=Lang::choice('messages.recharge_wallet', $locale);
        $arrCustomerDetails['lstProductDC'][0]['UnitName']=Lang::choice('messages.recharge_wallet', $locale);
        $arrCustomerDetails['lstProductDC'][0]['UnitPrice']=$amount;
        $arrCustomerDetails['lstProductDC'][0]['VndID']=($userDetails->id);
        $arrCustomerDetails['lstGateWayDC'][0]['Name']="ALL";
        $arrCustomerDetails['MerMastDC']['AutoReturn']="Y";
        $arrCustomerDetails['MerMastDC']['ErrorURL']=url('tap-payment-error');
        $arrCustomerDetails['MerMastDC']['HashString']=$hashstr;
        $arrCustomerDetails['MerMastDC']['LangCode']=  strtoupper($locale);
        $arrCustomerDetails['MerMastDC']['MerchantID']=$MerchantID;
        $arrCustomerDetails['MerMastDC']['Password']=$UserPass;
        $arrCustomerDetails['MerMastDC']['PostURL']=url('');
        $arrCustomerDetails['MerMastDC']['ReferenceID']=$ref;
        $arrCustomerDetails['MerMastDC']['ReturnURL']=url('tap-payment-success');
        $arrCustomerDetails['MerMastDC']['UserName']=$UserName;
       
        $strCustomerDetails= json_encode($arrCustomerDetails);
       
         $curl = curl_init();

          curl_setopt_array($curl, array(
          // CURLOPT_URL => 'https://www.gotapnow.com/TapWebConnect/Tap/WebPay/PaymentRequest',
         CURLOPT_URL => 'http://tapapi.gotapnow.com/TapWebConnect/Tap/WebPay/PaymentRequest',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 60,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $strCustomerDetails,
          CURLOPT_HTTPHEADER => array(
            "content-type: application/json"
          ),
        ));

        $response = curl_exec($curl);
    
        $err = curl_error($curl);

        curl_close($curl);

            if ($err) {
               $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_payment_tap', $locale));  
                return response()->json($arr_to_return);
            } else {
              $response_data=json_decode($response);
            
             
              if(count($response_data->PaymentURL)>0)
              {
                  return redirect($response_data->PaymentURL);exit;
              }else{
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_payment_tap', $locale));  
                    return response()->json($arr_to_return);
              }
            }
      }else{
          
      }
    }
    
    
   public function orderPaymentSuccessTap(Request $request){
    
    $Tap_Ref = $_REQUEST['ref'];
    $Txn_Result = $_REQUEST['result'];
    $Txn_OrderID = $_REQUEST['trackid'];
     $user_id= Session::get('user_id');
     $locale= Session::get('locale');
    $Hash = $_REQUEST['hash']; 
    $APIKey = env('tap_api_key', '1tap7');
    $MerchantID =  env('tap_merchant_id', '1014'); 
    $toBeHashedString = 'x_account_id'.$MerchantID.'x_ref'.$Tap_Ref.'x_result'.$Txn_Result.'x_referenceid'.$Txn_OrderID.'';
    $myHashStr = hash_hmac('sha256', $toBeHashedString, $APIKey);
    if($myHashStr == $Hash)
    {
      
        
        \App::setLocale($locale);
        $user_details=User::where('id',$user_id)->first();
       if(count($user_details)>0)
       {
        $wallet_amount =  isset($_REQUEST['amt'])?$_REQUEST['amt']:'0';
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
        $arrWalletAmt['ref_id']=$Tap_Ref;
        $checkTransaction=UserWalletDetail::where('ref_id',$Tap_Ref)->first();
       if(count($checkTransaction)<=0)
       {
            $mate_wallet_data = UserWalletDetail::create($arrWalletAmt);
       }
       
        if(isset($mate_wallet_data->id))
        {
             $arr_to_return = array("error_code" => 0,"final_amount"=>$final_amount, "msg" =>Lang::choice('messages.wallet_recharge',$locale));
        }else{
             $arr_to_return = array("error_code" => 1, "msg" =>Lang::choice('messages.recharge_fail',$locale));
        }
       }
        else
        {
          $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_payment_tap', $locale));  

        }
    }else
    {
      $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_payment_tap', $locale));  
     
    }
     return response()->json($arr_to_return);

  }
   public function orderPaymentErrorTap(Request $request){
    
    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_payment_tap', $locale));  
     
     return response()->json($arr_to_return);

  }
  
}
