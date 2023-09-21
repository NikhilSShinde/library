<?php

namespace App\Http\Controllers;

use App\User;
use App\UserInformation;
use App\Nationality;
use App\UserAddress;
use App\UserOtpCodes;
use App\GeoLimit;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\admin\Models\CountryTranslation;
use App\PiplModules\admin\Models\CountryServices;
use App\PiplModules\admin\Models\State;
use App\PiplModules\admin\Models\City;
use App\PiplModules\admin\Models\CityTranslation;
use App\PiplModules\roles\Models\Role;
use App\PiplModules\slider\Models\SliderImage;
use App\PiplModules\contentpage\Models\ContentPage;
use App\PiplModules\contentpage\Models\ContentPageTranslation;
use App\CategoryStatusMsg;
use App\Solution;
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
use App\PiplModules\orderdetails\Models\OrderAssignedDetail;
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
use App\PiplModules\vehicle\Models\DriverAssignedDetail;
use App\PanaceaClasses\AppNotification;
use App\Notification;
use Twilio;
use App\PiplModules\faq\Models\Faq;

class DeliveryController extends Controller {
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
//        return Validator::make($request, [
//                    'first_name' => 'required',
//                    'last_name' => 'required',
//                    'suburb' => 'required',
//                    'zipcode' => 'required',
//        ]);
    }

    function calculateangle($lat1, $lat2, $lng1, $lng2) {
        $dLon = $lng2 - $lng1;

        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
        echo 360 - ((rad2deg(atan2($y, $x)) + 360) % 360);
        die;
    }

    protected function customerRegistration(Request $request) {

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
        $gender = isset($request['gender']) ? $request['gender'] : '';
        $birth_date = isset($request['birth_date']) ? $request['birth_date'] : '';
        $user_type = 3;
        \App::setLocale($locale);

        //checking if user is not using existing details
        if ($email != '' && $email != NULL) {
            $arrUserEmail = User::where("email", $email)->first();
            if (count($arrUserEmail) > 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.email_already_exist', $locale));
                return response()->json($arr_to_return);
            }
        }

        $arrUserName = User::where("username", $username)->first();

        if ((count($arrUserName) > 0) && (isset($arrUserName->userInformation->mobile_code)) && ($arrUserName->userInformation->mobile_code == $mobile_code)) {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_already_exist', $locale));
            return response()->json($arr_to_return);
        } elseif ($username != '') {

            //creating user
            if ($email != '') {
                $created_user = User::create([
                            'username' => ltrim($username, '0'),
                            'email' => $email,
                            'password' => $password,
                ]);
            } else {
                $created_user = User::create([
                            'username' => ltrim($username, '0'),
                            'password' => $password,
                ]);
            }

            //entering details in user Information Table.
            $mobile_code = str_replace("+", "", $mobile_code);
            $arr_userinformation["profile_picture"] = $profile_picture;
            $arr_userinformation["first_name"] = $first_name;
//            $arr_userinformation["civil_id"] = $civil_id;
            $arr_userinformation["last_name"] = $last_name;
            $arr_userinformation["user_mobile"] = ltrim($username, '0');
            $arr_userinformation["mobile_code"] = str_replace("+", "", $mobile_code);
            $arr_userinformation["device_id"] = $device_id;
            $arr_userinformation["device_type"] = $device_type;
            $arr_userinformation["user_status"] = 1;
            $arr_userinformation["user_birth_date"] = $birth_date;
            $arr_userinformation["gender"] = $gender;
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
            $arr_userAddress["address_type"] = 1;
            if ($country != '') {
                UserAddress::create($arr_userAddress);
            }

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
            if (isset($created_user->email) && $created_user->email != '' && $created_user->email != NULL) {
                Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($created_user, $email_subject, $site_email, $site_title) {

                    $message->to($created_user->email)->subject($email_subject)->from($site_email, $site_title);
                });
            }
            if ($created_user->id) {
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.register_successfull', $locale), "data" => $created_user, "userInformation" => $created_user->userInformation, "userAddress" => $created_user->userAddress);
                return response()->json($arr_to_return);
            }
        }
    }

    protected function deliveryuserRegistration(Request $request) {
        $arr_to_return = array();
        //getting all inputs
        $first_name = isset($request['first_name']) ? $request['first_name'] : '';
        $gender = isset($request['gender']) ? $request['gender'] : '';
        $last_name = isset($request['last_name']) ? $request['last_name'] : '';
        $username = isset($request['mobile_number']) ? $request['mobile_number'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $email = isset($request['email']) ? $request['email'] : '';
        $profile_picture = isset($request['profile_picture']) ? $request['profile_picture'] : '';
        $country = isset($request['country']) ? $request['country'] : '10';
        $region = isset($request['region']) ? $request['region'] : '';
        $city = isset($request['city']) ? $request['city'] : '';
        $nationality = isset($request['nationality']) ? $request['nationality'] : '';
        $address = isset($request['address']) ? $request['address'] : '';
        $latitude = isset($request['latitude']) ? $request['latitude'] : '';
        $lontitude = isset($request['lontitude']) ? $request['lontitude'] : '';
        $device_type = isset($request['device_type']) ? $request['device_type'] : '';
        $device_id = isset($request['device_id']) ? $request['device_id'] : '';
        $birth_date = isset($request['birth_date']) ? $request['birth_date'] : '';
        $user_type = 2;
        \App::setLocale($locale);
        //checking if user is not using existing details
        if ($email != '' && $email != NULL) {
            $arrUserEmail = User::where("email", $email)->first();
            if (count($arrUserEmail) > 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.email_already_exist', $locale));
                return response()->json($arr_to_return);
            }
        }
        $arrUserName = User::where("username", $username)->first();

        if (count($arrUserName) > 0 && ($arrUserName->userInformation->mobile_code == $mobile_code)) {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_already_exist', $locale));
            return response()->json($arr_to_return);
        } elseif ($username != '') {

            //creating user
            if ($email != '' && $email != NULL) {
                $created_user = User::create([
                            'username' => ltrim($username, '0'),
                            'email' => $email,
                ]);
            } else {
                $created_user = User::create([
                            'username' => ltrim($username, '0')
                ]);
            }

            //entering details in user Information Table.
            $mobile_code = str_replace("+", "", $mobile_code);
            $arr_userinformation["profile_picture"] = $profile_picture;
            $arr_userinformation["first_name"] = $first_name;
            $arr_userinformation["last_name"] = $last_name;
            $arr_userinformation["user_mobile"] = ltrim($username, '0');
            $arr_userinformation["mobile_code"] = str_replace("+", "", $mobile_code);
            $arr_userinformation["device_id"] = $device_id;
            $arr_userinformation["device_type"] = $device_type;
            $arr_userinformation["nationality"] = $nationality;
            $arr_userinformation["user_status"] = 0;
            $arr_userinformation["user_birth_date"] = $birth_date;
            $arr_userinformation["user_type"] = $user_type;
            $arr_userinformation["gender"] = $gender;
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
            $arr_userAddress["address_type"] = 1;
            if ($country != '') {
                UserAddress::create($arr_userAddress);
            }

            // asign role to respective user		
            $userRole = Role::where("slug", "registered.user")->first();

            $created_user->attachRole($userRole);

            $arr_starInfo["user_id"] = $created_user->id;
            $arr_starInfo["driver_license"] = "";
            $arr_starInfo["driver_license_flle"] = "";
            $arr_starInfo["id_number"] = "";
            if ($request->file('driver_license')) {
                $extension = $request->file('driver_license')->getClientOriginalExtension();
                if ($extension == '') {
                    $extension = "png";
                }
                $new_file_name = time() . "." . $extension;
                Storage::put('public/star-document/' . $new_file_name, file_get_contents($request->file('driver_license')->getRealPath()));
                $arr_starInfo["driver_license_flle"] = $new_file_name;
            }
            DriverUserInformation::create($arr_starInfo);
            //DriverUserInformation::create(array("user_id" => $created_user->id));
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
            $tempate_name = "emailtemplate::star-registration-successfull-" . $locale;
            if (isset($created_user->email) && $created_user->email != '' && $created_user->email != NULL) {
                Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($created_user, $email_subject, $site_email, $site_title) {

                    $message->to($created_user->email)->subject($email_subject)->from($site_email, $site_title);
                });
            }
            $countryTransInfo = CountryTranslation::where('country_id', $country)->where('locale', 'en')->first();
            //social url's
            $fb_url = GlobalValues::get('facebook-link');
            $google_url = GlobalValues::get('google-link');
            $twitter_url = GlobalValues::get('twitter-link');
            $contact_email = GlobalValues::get('contact-email');
            $mobile_number_to_send = str_replace("+", "", $mobile_code);
            $mobile_number_to_send = "+" . $mobile_number_to_send . "" . ltrim($username, '0');
            $website_link = url('');
            $messagesToSend = "Thank you for joining BAGGI Driver. We will contact you soon.  For more info visit- " . $website_link;

            if (isset($countryTransInfo->support_number) && $countryTransInfo->support_number != '') {
                $messagesToSend .= "for more info contact us on " . $countryTransInfo->support_number;
            }
            //calling to sendsms class
            $obj_sms = new SendSms();
            $obj_sms->sendMessage($mobile_number_to_send, $messagesToSend);
            //Twilio::message($mobile_number_to_send, $messagesToSend);
            if ($created_user->id) {
                $arr_to_return = array("error_code" => 0, "fb_url" => $fb_url, "google_url" => $google_url, "twitter_url" => $twitter_url, "contact_url" => $contact_email, "request_success" => Lang::choice('messages.request_success', $locale), "msg" => Lang::choice('messages.star_register_successfull', $locale), "data" => $created_user, "userInformation" => $created_user->userInformation, "userAddress" => $created_user->userAddress);
                return response()->json($arr_to_return);
            } else {
                $arr_to_return = array("error_code" => 1);
                return response()->json($arr_to_return);
            }
        }
    }

    protected function sendOtpForRegstration(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $rand = rand(1000, 9999);
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $email = (isset($request['email']) && $request['email'] != '') ? $request['email'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $resent = isset($request['resent']) ? $request['resent'] : '0';
        $type = isset($request['user_type']) ? $request['user_type'] : '0';
        \App::setLocale($locale);
        if ($mobile_no != '') {
            //checking if email or email is already register.
            $arrUserEmail = User::where("email", $email)->first();

            $arrUserName = UserInformation::where("user_mobile", $mobile_no)->first();
            if (isset($arrUserName) && count($arrUserName)) {
                 $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_exist', $locale));
            } else {
                $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '+91';
                $mobile_code = str_replace("+", "", $mobile_code);
                $mobile_code = trim($mobile_code);
                $mobile_number_to_send = "+" . $mobile_code . "" . $mobile_no;
                if (count($arrUserName) > 0 && isset($arrUserName->mobile_code) && ($arrUserName->mobile_code == $mobile_code)) {
                    $user_type = isset($arrUserName->user_type) ? $arrUserName->user_type : '';
                    if ($type != '0') {
                        if ($user_type != $type) {
                            $msg_login_issue = '';
                            if ($type == '2') {
                                $msg_login_issue = Lang::choice('messages.invalid_mobile_customer', $locale);
                            } else {
                                $msg_login_issue = Lang::choice('messages.invalid_mobile_driver', $locale);
                            }
                            $arr_to_return = array("error_code" => 2, "msg" => $msg_login_issue);
                        } else {
                            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_exist', $locale));
                        }
                    } else {
                        $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_exist', $locale));
                    }
                } else if (count($arrUserEmail) > 0) {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.email_already_exist', $locale));
                } else {
                    $message = Lang::choice('messages.otp_for_registration', $locale);
                    $rand_msg = ($rand);
                    if ($locale == 'ar') {
                        // $rand_msg=strrev($rand);
                    }
                    $message .= " " . $rand_msg;
                    $obj_sms = new SendSms();
                    $obj_sms->sendMessage($mobile_number_to_send, $message);
                    //inserting opt code to tabl
                    $arr_otp['mobile'] = $mobile_no;
                    $arr_otp['otp_code'] = $rand;
                    $arr_otp['mobile_code'] = $mobile_code;
                    $arr_otp['status'] = 1;
                    $arr_otp['otp_for'] = 1;
                    UserOtpCodes::create($arr_otp);
                    //seding email also if emails is provided   
                    if ($email == '' && isset($arrUserName->user->email)) {
                        $email = $arrUserName->user->email;
                    }
                    if ($email != '' && $email != 'Null' && $email != NULL) {
                        $site_email = GlobalValues::get('site-email');
                        $site_title = GlobalValues::get('site-title');
                        $arr_keyword_values = array();
                        //Assign values to all macros
                        $arr_keyword_values['OTP_CODE'] = $rand;
                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        $email_subject = Lang::choice('messages.otp_sent_email_subject', $locale);
                        $tempate_name = "emailtemplate::send-otp-" . $locale;

                        if ($email != '' && $email != NULL && $email != 0) {
                            Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($email, $email_subject, $site_email, $site_title) {

                                $message->to($email)->subject($email_subject)->from($site_email, $site_title);
                            });
                        }
                    }
                    $arr_to_return = array("error_code" => 0, "otp" => $rand, "msg" => Lang::choice('messages.otp_sent_successfully', $locale));
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_exist', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function sendOtpForForgotPassword(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $rand = rand(1000, 9999);
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $email = isset($request['email']) ? $request['email'] : '0';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '+91';
        $mobile = trim($mobile_code);
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $user_type = isset($request['user_type']) ? $request['user_type'] : '0';
        \App::setLocale($locale);

        if ($mobile_no != '') {
            //checking if email or email is already register.
            // $arrUserEmail = User::where("email", $email)->first();
            $arrUserName = UserInformation::where("user_mobile", $mobile_no)->first();
            if (count($arrUserName) > 0 && ($arrUserName->user_type == $user_type) && ($arrUserName->mobile_code == $mobile_code)) {

                if ($arrUserName->user_status == '0') {
                    $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.user_is_inactive', $locale));
                    return response()->json($arr_to_return);
                    exit;
                } else if ($arrUserName->user_status == '2') {
                    $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.user_is_blocked', $locale));
                    return response()->json($arr_to_return);
                    exit;
                }
                if ($mobile_code == '') {
                    $mobile_code = isset($arrUserName->mobile_code) ? $arrUserName->mobile_code : '91';
                }
                $mobile_code = str_replace("+", "", $mobile_code);
                $mobile_number_to_send = "+" . $mobile_code . "" . $mobile_no;
                $message = Lang::choice('messages.otp_for_reset_password_sms', $locale);
                $rand_msg = ($rand);
                if ($locale == 'ar') {
                    //$rand_msg=strrev($rand);
                }
                $message .= " " . $rand_msg;
                $obj_sms = new SendSms();
                $obj_sms->sendMessage($mobile_number_to_send, $message);
                // Twilio::message($mobile_number_to_send, $message);
                //inserting opt code to tabl
                $arr_otp['mobile'] = $mobile_no;
                $arr_otp['otp_code'] = $rand;
                $arr_otp['status'] = 1;
                $arr_otp['otp_for'] = 0;
                UserOtpCodes::create($arr_otp);
                if ($email == '' && isset($arrUserName->user->email)) {
                    $email = isset($arrUserName->user->email) ? $arrUserName->user->email : '';
                }


                //seding email also if emails is provided   
                if ($email != '' && $email != 'Null' && $email != NULL) {
                    $site_email = GlobalValues::get('site-email');
                    $site_title = GlobalValues::get('site-title');
                    $arr_keyword_values = array();
                    //Assign values to all macros
                    $arr_keyword_values['OTP_CODE'] = $rand;
                    $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $email_subject = Lang::choice('messages.otp_sent_email_subject', $locale);
                    $tempate_name = "emailtemplate::send-otp-" . $locale;
                    if ($email != '' && $email != NULL && $email != 0) {
                        Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($email, $email_subject, $site_email, $site_title) {

                            $message->to($email)->subject($email_subject)->from($site_email, $site_title);
                        });
                    }
                }
                $arr_to_return = array("error_code" => 0, "otp" => $rand, "msg" => Lang::choice('messages.otp_sent_successfully', $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_not_exist', $locale));
            }
            return response()->json($arr_to_return);
        }
    }

    protected function verifyOtp(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $otp = isset($request['otp']) ? $request['otp'] : '';
        $otp_for = isset($request['otp_for']) ? $request['otp_for'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : '';
        \App::setLocale($locale);
        $arrVerifyOtp = UserOtpCodes::where("mobile", $mobile_no)->where('otp_for', $otp_for)->where('otp_code', $otp)->where("status", '1')->first();

        if (count($arrVerifyOtp) > 0) {
            $arrVerifyOtp->status = 0;
            $arrVerifyOtp->save();
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.otp_is_valid', $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.otp_is_not_valid_expired', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function userLogin(Request $request) {

        $arr_to_return = array();
        $arrUserEmergencyDetails = array();
        $arrUserCards = array();
        //getting mobile number
        $mobile_no = isset($request['user_name']) ? $request['user_name'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        $password = isset($request['password']) ? $request['password'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $user_type = isset($request['user_type']) ? $request['user_type'] : '';
        $device_id = isset($request['device_id']) ? $request['device_id'] : '';
        $device_type = isset($request['device_type']) ? $request['device_type'] : '';
        \App::setLocale($locale);
        $fb_url = GlobalValues::get('facebook-link');
        $google_url = GlobalValues::get('google-link');
        $twitter_url = GlobalValues::get('twitter-link');
        $contact_email = GlobalValues::get('contact-email');
        if ($user_type == 'star') {
            $user_type = 2;
        } else {
            $user_type = 3;
        }
        if ($mobile_code != '') {
            $mobile_code = str_replace("+", "", $mobile_code);
        }

        $arrUserLoginDetails = UserInformation::where('user_mobile', $mobile_no)->where('user_type', $user_type)->first();
        
        if (count($arrUserLoginDetails) <= 0) {
            $mobile_no = ltrim($mobile_no, '0');
            $arrUserLoginDetails = UserInformation::where('user_mobile', $mobile_no)->where('user_type', $user_type)->first();
        }
        $login_user_id = isset($arrUserLoginDetails->user_id) ? $arrUserLoginDetails->user_id : '0';
        $arrUserLogin = User::where('id', $login_user_id)->first();

        if (((count($arrUserLogin) > 0) && ($arrUserLogin->userInformation->user_type == $user_type) && ($arrUserLogin->userInformation->mobile_code == $mobile_code))) {
            if (Hash::check($password, $arrUserLogin->password) == true) {
                if ($arrUserLogin->userInformation->user_status == '0') {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_is_inactive', $locale));
                } else if ($arrUserLogin->userInformation->user_status == '2') {
                    $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.user_is_blocked', $locale));
                } else {
                    $user_image = "";
                    if (isset($arrUserLogin->userInformation->profile_picture)) {
                        $user_image = asset("/storageasset/user-images/" . $arrUserLogin->userInformation->profile_picture);
                    }

                    //update user details
                    if ($device_id != '') {
                        $arrUserLogin->userInformation->device_id = $device_id;
                        $arrUserLogin->userInformation->device_type = $device_type;
                        $arrUserLogin->userInformation->save();
                    }
                    //getting getting emergency contact count.
                    if (isset($arrUserLogin->id)) {
                        $arrUserEmergencyDetails = UserEmergencyContactInformation::where("user_id", $arrUserLogin->id)->get();
                    }

                    //getting getting credit cards.
                    if (isset($arrUserLogin->id)) {
                        $arrUserCards = UserCreditCard::where("user_id", $arrUserLogin->id)->get();
                    }
                    //getting country inor by mobile code
                    $mobile_code_country = str_replace("+", "", $mobile_code);
                    $mobile_code_country = "+" . $mobile_code_country;
                    $mobile_code_country = str_replace(" ", "", $mobile_code_country);

                    $county_info = Country::where('country_code', $mobile_code_country)->first();
                    $userAddress = UserAddress::where('user_id', $login_user_id)->get();

                    $homeAddress = $userAddress->reject(function($address) {
                                return ($address->address_type != '4');
                            })->values();
                    $workAddress = $userAddress->reject(function($address) {
                                return ($address->address_type != '5');
                            })->values();

                    $arr_to_return = array("error_code" => 0, "home_address" => $homeAddress, "work_address" => $workAddress, "country_info" => $county_info, "fb_url" => $fb_url, "card_count" => count($arrUserCards), "emergency_count" => count($arrUserEmergencyDetails), "google_url" => $google_url, "twitter_url" => $twitter_url, "contact_url" => $contact_email, "user_image" => $user_image, "welcome_msg" => Lang::choice('messages.welcome_msg', $locale), "msg" => Lang::choice('messages.login_success', $locale), "user" => $arrUserLogin, "userInformation" => $arrUserLogin->userInformation);
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_username_password', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_username_password', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function countryData(Request $request) {

        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($locale != '') {
            $path = realpath(dirname(__FILE__) . '/../../../public');
            $file_name = $path . "/country_data/all_data_" . $locale;
            $file_path = $file_name . ".txt";
            if (file_exists($file_path)) {
                $myfile = fopen($file_path, "r") or die("Unable to open file!");
                $all_countries = fread($myfile, filesize($file_path));
                $all_countries = json_decode($all_countries);
            } else {
                $all_countries = Country::with('statesInfo')->translatedIn(\App::getLocale())->get();
                /*$all_countries = $all_countries->reject(function ($country) {
                    return ($country->id == '17');
                });*/
                //loop on all countries and states
                if ($all_countries) {
                    foreach ($all_countries as $country) {
                        $country->cityInfo = "{}";
                        if ($country->allstatesInfo) {
                            foreach ($country->allstatesInfo as $states) {
                                $country->cityInfo = $states->cityInfo;
                            }
                        }
                    }
                }
            }
            //$nationality=Nationality::all();
            $nationality = array();
            return array("country_info" => $all_countries, "nationality" => $nationality);
        } else {
            return response()->json(array("error_code" => 1));
        }
    }

    protected function countryDataToFileEn(Request $request) {
        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($locale != '') {
            $all_countries = Country::with('statesInfo')->translatedIn(\App::getLocale())->get();
            /*$all_countries = $all_countries->reject(function ($country) {
                        return ($country->id == '17');
                    })->values();
             * 
             */
            $all_countries = $all_countries->values();
            //loop on all countries and states
            if ($all_countries) {
                foreach ($all_countries as $country) {
                    $country->cityInfo = "{}";
                    if ($country->allstatesInfo) {
                        foreach ($country->allstatesInfo as $states) {
                            $country->cityInfo = $states->cityInfo;
                        }
                    }
                }
            }
            $path = realpath(dirname(__FILE__) . '/../../../public');
            $file_name = $path . "/country_data/all_data_en";
            $file_path = $file_name . ".txt";
            $location_resource = fopen($file_path, "w+");
            $write_content = $all_countries;
            fwrite($location_resource, $write_content);
        } else {
            return response()->json(array("error_code" => 1));
        }
    }

    protected function countryDataToFileMr(Request $request) {

        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'mr';
        \App::setLocale($locale);
        if ($locale != '') {
            $all_countries = Country::with('statesInfo')->translatedIn(\App::getLocale())->get();
            $all_countries = $all_countries->reject(function ($country) {
                        return ($country->id == '17');
                    })->values();
            //loop on all countries and states
            if ($all_countries) {
                foreach ($all_countries as $country) {
                    $country->cityInfo = "{}";
                    if ($country->allstatesInfo) {
                        foreach ($country->allstatesInfo as $states) {
                            $country->cityInfo = $states->cityInfo;
                        }
                    }
                }
            }

            $path = realpath(dirname(__FILE__) . '/../../../public');
            $file_name = $path . "/country_data/all_data_mr";
            $file_path = $file_name . ".txt";
            $location_resource = fopen($file_path, "w+");
            $write_content = $all_countries;
            fwrite($location_resource, $write_content);
        } else {
            return response()->json(array("error_code" => 1));
        }
    }

    protected function countryDataToFileHi(Request $request) {

        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'hi';
        \App::setLocale($locale);
        if ($locale != '') {
            $all_countries = Country::with('statesInfo')->translatedIn(\App::getLocale())->get();
            $all_countries = $all_countries->reject(function ($country) {
                        return ($country->id == '17');
                    })->values();
            //loop on all countries and states
            if ($all_countries) {
                foreach ($all_countries as $country) {
                    $country->cityInfo = "{}";
                    if ($country->allstatesInfo) {
                        foreach ($country->allstatesInfo as $states) {
                            $country->cityInfo = $states->cityInfo;
                        }
                    }
                }
            }

            $path = realpath(dirname(__FILE__) . '/../../../public');
            $file_name = $path . "/country_data/all_data_hi";
            $file_path = $file_name . ".txt";
            $location_resource = fopen($file_path, "w+");
            $write_content = $all_countries;
            fwrite($location_resource, $write_content);
        } else {
            return response()->json(array("error_code" => 1));
        }
    }

    protected function countryDataIOS(Request $request) {

        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($locale != '') {
            $all_countries = Country::with('statesInfo')->with('citiesInfo')->translatedIn($locale)->get();

            return $all_countries;
        } else {
            return response()->json(array("error_code" => 1));
        }
    }

    protected function locationData(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($locale != '') {
            $all_countries = Country::translatedIn(\App::getLocale())->get();
            $all_regions = State::translatedIn(\App::getLocale())->get();
            $all_cities = City::translatedIn(\App::getLocale())->get();
            $arr_to_return = array("error_code" => 0, "countries" => $all_countries, "regions" => $all_regions, "cities" => $all_cities);
        } else {
            return response()->json(array("error_code" => 1));
        }
        return response()->json($arr_to_return);
    }

    protected function resetPassword(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $mobile_no = isset($request['mobile_number']) ? $request['mobile_number'] : '';
        $otp = isset($request['otp']) ? $request['otp'] : '';
        $password = isset($request['password']) ? $request['password'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '91';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $mobile_code = str_replace("+", "", $mobile_code);
        $arrUserLogin = User::where(array("username" => $mobile_no))->first();
        if (count($arrUserLogin) > 0) {

            if (Hash::check($password, $arrUserLogin->password) == true) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.previous_password_used', $locale));
            } else {
                //check for valid otp once and if valid 
                $arrVerifyOtp = UserOtpCodes::where(array("mobile" => $mobile_no, "otp_code" => $otp, "otp_for" => '0'))->first();
                if (count($arrVerifyOtp) > 0) {
                    $arrUserLogin->password = $password;
                    $arrUserLogin->save();
                }
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.password_reset_successfully', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_does_not_exist', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function getContentPages(Request $request) {

        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $page_alias = isset($request['page_alias']) ? $request['page_alias'] : '';
        $page_data = ContentPage::where('page_alias', $page_alias)->translatedIn(\App::getLocale())->get();
        $arr_to_return = array("error_code" => 0, "data" => $page_data);
        return response()->json($arr_to_return);
    }

    protected function getContactUsCategories(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $all_categories = ContactRequestCategory::translatedIn(\App::getLocale())->get();
        $arr_to_return = array("error_code" => 0, "data" => $all_categories);
        return response()->json($arr_to_return);
    }

    protected function contactUs(Request $request) {
        $arr_to_return = array();
        $contact_subject = isset($request['contact_subject']) ? $request['contact_subject'] : '';
        $contact_message = isset($request['contact_message']) ? $request['contact_message'] : '';
        $contact_category_name = isset($request['category_name']) ? $request['category_name'] : '';
        $contact_request_category = isset($request['contact_request_category']) ? $request['contact_request_category'] : '';
        $contact_name = isset($request['contact_name']) ? $request['contact_name'] : '';
        $contact_email = isset($request['contact_email']) ? $request['contact_email'] : '';
        $contact_phone = isset($request['contact_phone']) ? $request['contact_phone'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : '';
        \App::setLocale($locale);
        if ($contact_subject != '') {

            $reference_no = rand(10000, 999999);
            //adding contact request
            $arr_contact = array();
            $arr_contact['contact_subject'] = $contact_subject;
            $arr_contact['contact_message'] = $contact_message;
            if ($contact_request_category > 0) {
                $arr_contact['contact_request_category'] = $contact_request_category;
            }
            $arr_contact['contact_name'] = $contact_name;
            $arr_contact['contact_email'] = $contact_email;
            $arr_contact['contact_phone'] = $contact_phone;
            $arr_contact['reference_no'] = $reference_no;
            ContactRequest::create($arr_contact);

            //sent emaill to admin 
            //sending email for successfull registration
            //getting from global setting
            $site_email = GlobalValues::get('site-email');
            $site_title = GlobalValues::get('site-title');
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

            $email_subject = Lang::choice('messages.contact_sent_success', $locale);
            $tempate_name = "emailtemplate::contact-request";
            Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($email_subject, $site_email, $site_title) {

                $message->to($site_email)->subject($email_subject)->from($site_email, $site_title);
            });

            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.contact_done_successfully', $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.issue_in_contact', $locale));
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
        $gender = isset($request['gender']) ? $request['gender'] : '';
        $birth_date = isset($request['birth_date']) ? $request['birth_date'] : '';
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
                                'password' => $password
                    ]);
                } else {
                    $created_user = User::create([
                                'username' => $username,
                                'password' => $password
                    ]);
                }

                //entering details in user Information Table.
                $arr_userinformation["profile_picture"] = $profile_picture;
                $arr_userinformation["first_name"] = $first_name;
                $arr_userinformation["last_name"] = $last_name;
                $arr_userinformation["user_mobile"] = $username;
                $arr_userinformation["mobile_code"] = str_replace(" ", "", $mobile_code);
                $arr_userinformation["device_id"] = $device_id;
                $arr_userinformation["device_type"] = $device_type;
                $arr_userinformation["user_status"] = 1;
                $arr_userinformation["twitter_id"] = $twitter_id;
                $arr_userinformation["facebook_id"] = $facebook_id;
                $arr_userinformation["google_id"] = $google_id;
                $arr_userinformation["user_type"] = $user_type;
                $arr_userinformation["user_id"] = $created_user->id;
                $arr_userinformation["gender"] = $gender;
                $arr_userinformation["user_birth_date"] = $birth_date;
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
                $mobile_code_country = str_replace("+", "", $mobile_code);
                $mobile_code_country = "+" . $mobile_code_country;
                $mobile_code_country = str_replace(" ", "", $mobile_code_country);
                $county_info = Country::where('country_code', $mobile_code_country)->first();
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
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.register_successfull', $locale), "data" => $created_user, "country_info" => $county_info, "userInformation" => $created_user->userInformation);
                    return response()->json($arr_to_return);
                }
            }
        }
    }

    protected function isSocialConnect(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $facebook_id = isset($request['facebook_id']) ? $request['facebook_id'] : '';
        $twitter_id = isset($request['twitter_id']) ? $request['twitter_id'] : '';
        $google_id = isset($request['google_id']) ? $request['google_id'] : '';
        $social_type = isset($request['social_type']) ? $request['social_type'] : '';
        \App::setLocale($locale);
        if ($social_type != '') {
            $arrSocialUser = "";
            if ($social_type == 'facebook') {
                $arrSocialUser = UserInformation::where('facebook_id', $facebook_id)->first();
            } else if ($social_type == 'twitter') {
                $arrSocialUser = UserInformation::where('twitter_id', $twitter_id)->first();
            } else if ($social_type == 'google') {
                $arrSocialUser = UserInformation::where('google_id', $google_id)->first();
            }
            if (count($arrSocialUser) > 0) {
                //getting country inor by mobile code
                $mobile_code_country = str_replace("+", "", $arrSocialUser->mobile_code);
                $mobile_code_country = "+" . $mobile_code_country;
                $mobile_code_country = str_replace(" ", "", $mobile_code_country);
                $county_info = Country::where('country_code', $mobile_code_country)->first();

                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.login_success', $locale), "user" => $arrSocialUser->user, "country_info" => $county_info, "userInformation" => $arrSocialUser);
            } else {
                $arr_to_return = array("error_code" => 1);
            }
        } else {
            $arr_to_return = array("error_code" => 2);
        }
        return response()->json($arr_to_return);
    }

    protected function calculatePrice(Request $request) {
        $latitude1 = isset($request['latitude1']) ? $request['latitude1'] : '';
        $longtitude1 = isset($request['longtitude1']) ? $request['longtitude1'] : '';

        $latitude2 = isset($request['latitude2']) ? $request['latitude2'] : '';
        $longtitude2 = isset($request['longtitude2']) ? $request['longtitude2'] : '';
        $coordA = Geotools::coordinate([48.8234055, 2.3072664]);
        $coordB = Geotools::coordinate([43.296482, 5.36978]);
        $distance = Geotools::distance()->setFrom($coordA)->setTo($coordB);
        echo $distance->flat() . "dd";
        echo $distance->in('km')->haversine();
        die;
    }

    protected function getCategoryData(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $solution_id = isset($request['solution_id']) ? $request['solution_id'] : 1;
        \App::setLocale($locale);
        $arrCaregoryServiceData = array();
        $city_name = isset($request['city_name']) ? $request['city_name'] : '0';
        $cityInfo = CityTranslation::where('name', $city_name)->first();
        if (count($cityInfo) > 0) {
            $country_id = isset($cityInfo->city_id) ? $cityInfo->city_id : '0';

            if ($locale != '' && $country_id != 0) {
                $categoryData = Category::translatedIn($locale)->where('status', 1)->where('solution_id', $solution_id)->get();


                if (count($categoryData) > 0) {
                    $path = realpath(dirname(__FILE__) . '/../../../public');
                    $file_name = $path . "/category_data/" . $country_id . "_" . $solution_id . "_all_data_" . $locale;
                    if ($locale == 'ar') {
                        $file_name = $path . "/category_data/" . $country_id . "_" . $solution_id . "_all_data_" . $locale . "_ios";
                    }
                    $file_path = $file_name . ".txt";
                    if (file_exists($file_path)) {
                        $myfile = fopen($file_path, "r") or die("Unable to open file!");
                        $arrCaregoryServiceData = fread($myfile, filesize($file_path));
                        $arrCaregoryServiceData = json_decode($arrCaregoryServiceData);
                    } else {
                        $i = 0;
                        //get all services which belong to specifc category
                        foreach ($categoryData as $category) {
                            $j = 0;
                            $arrCaregoryServiceData[$i] = $category;

                            $category_id = $category->id;
                            $countryserviceData = CountryServices::where('city_id', $country_id)->orderBy('sort_index', 'asc')->with('serviceInformation')->get();


                            $countryserviceData = $countryserviceData->reject(function($countryservice) use ($category_id) {
                                        if (isset($countryservice->serviceInformation->category_id)) {
                                            return ($countryservice->serviceInformation->category_id != $category_id);
                                        } else {
                                            return true;
                                        }
                                    })->values();
                            // $countryserviceData=$countryserviceData->sortByDesc('max_range');  
                            $countryserviceData = $countryserviceData->reject(function($service_data) {

                                        return ($service_data->serviceInformation->parent_id > 0);
                                    })->values();
                            $arrCaregoryServiceData[$i]['service'] = $countryserviceData;

                            if (count($countryserviceData) > 0) {

                                foreach ($countryserviceData as $service_key => $service_data) {
                                    $sub_services = Service::where('parent_id', $service_data->serviceInformation->id)->translatedIn($locale)->get();
                                    $sub_services = $sub_services->reject(function($sub_service_detail) use($country_id) {
                                                $sub_service_country_exist = CountryServices::where('service_id', $sub_service_detail->id)->where('country_id', $country_id)->first();
                                                return (count($sub_service_country_exist) <= 0);
                                            })->values();
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['subcategories'] = $sub_services;
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_image);
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['selected_image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_selected_image);
                                }
                            }

                            $i++;
                        }
                    }
                    //get city geo limit setting
                    $geoLimitSetting = GeoLimit::where('city_id', $country_id)->get();

                    $arr_to_return = array("error_code" => 0, "geo_limit" => $geoLimitSetting, "data" => $arrCaregoryServiceData);
                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.data_not_found', $locale));
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.data_not_found', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.data_not_found', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function getCategoryDataIOS(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
//        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $city_name = isset($request['city_name']) ? $request['city_name'] : '0';
        $cityInfo = CityTranslation::where('name', $city_name)->first();
        if (count($cityInfo) > 0) {
            $country_id = isset($cityInfo->city_id) ? $cityInfo->city_id : '0';
            $solution_id = isset($request['solution_id']) ? $request['solution_id'] : 2;
            \App::setLocale($locale);
            $arrCaregoryServiceData = array();
            if ($locale != '' && $country_id != 0) {
                $categoryData = Category::translatedIn($locale)->where('status', 1)->where('solution_id', $solution_id)->get();

                if (count($categoryData) > 0) {
                    $path = realpath(dirname(__FILE__) . '/../../../public');
                    $file_name = $path . "/category_data/" . $country_id . "_" . $solution_id . "_all_data_" . $locale;
                    if ($locale == 'ar') {
                        $file_name = $path . "/category_data/" . $country_id . "_" . $solution_id . "_all_data_" . $locale . "_ios";
                    }

                    $file_path = $file_name . ".txt";
                    if (file_exists($file_path)) {

                        $myfile = fopen($file_path, "r") or die("Unable to open file!");
                        $arrCaregoryServiceData = fread($myfile, filesize($file_path));
                        $arrCaregoryServiceData = json_decode($arrCaregoryServiceData);
                    } else {
                        $i = 0;
                        //get all services which belong to specifc category
                        foreach ($categoryData as $category) {
                            $j = 0;
                            $arrCaregoryServiceData[$i] = $category;

                            $category_id = $category->id;
                            $countryserviceData = CountryServices::where('city_id', $country_id)->orderBy('sort_index', 'asc')->with('serviceInformation')->get();


                            $countryserviceData = $countryserviceData->reject(function($countryservice) use ($category_id) {
                                        return ($countryservice->serviceInformation->category_id != $category_id);
                                    })->values();
                            // $countryserviceData=$countryserviceData->sortByDesc('max_range');  
                            $countryserviceData = $countryserviceData->reject(function($service_data) {

                                        return ($service_data->serviceInformation->parent_id > 0);
                                    })->values();
                            $arrCaregoryServiceData[$i]['service'] = $countryserviceData;

                            if (count($countryserviceData) > 0) {

                                foreach ($countryserviceData as $service_key => $service_data) {
                                    $sub_services = Service::where('parent_id', $service_data->serviceInformation->id)->translatedIn($locale)->get();
                                    $sub_services = $sub_services->reject(function($sub_service_detail) use($country_id) {
                                                $sub_service_country_exist = CountryServices::where('service_id', $sub_service_detail->id)->where('country_id', $country_id)->first();
                                                return (count($sub_service_country_exist) <= 0);
                                            })->values();
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['subcategories'] = $sub_services;
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_image);
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['selected_image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_selected_image);
                                }
                            }

                            $i++;
                        }
                    }
                    $geoLimitSetting = GeoLimit::where('city_id', $country_id)->get();
                    $arr_to_return = array("error_code" => 0, "geo_limit" => $geoLimitSetting, "data" => $arrCaregoryServiceData);
                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.data_not_found', $locale));
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.data_not_found', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.data_not_found', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function getCategoryDataToFileEn(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $arrCountryData = City::all();
        $arrSolutionData = Solution::all();
        if (count($arrCountryData) > 0) {
            foreach ($arrCountryData as $country) {
                $solution_id = 0;
                $arrCaregoryServiceData = array();
                if (count($arrSolutionData) > 0) {
                    foreach ($arrSolutionData as $solution) {
                        $country_id = isset($country->id) ? $country->id : '0';
                        $solution_id = isset($solution->id) ? $solution->id : '0';
                        if ($locale != '' && $country_id != 0) {
                            $categoryData = Category::translatedIn($locale)->where('status', 1)->where('solution_id', $solution_id)->get();

                            if (count($categoryData) > 0) {
                                $i = 0;
                                //get all services which belong to specifc category
                                foreach ($categoryData as $category) {
                                    $j = 0;
                                    $arrCaregoryServiceData[$i] = $category;

                                    $category_id = $category->id;
                                    $countryserviceData = CountryServices::where('city_id', $country_id)->orderBy('sort_index', 'asc')->with('serviceInformation')->get();


                                    $countryserviceData = $countryserviceData->reject(function($countryservice) use ($category_id) {
                                                return ($countryservice->serviceInformation->category_id != $category_id);
                                            })->values();
                                    // $countryserviceData=$countryserviceData->sortByDesc('max_range');  
                                    $countryserviceData = $countryserviceData->reject(function($service_data) {

                                                return ($service_data->serviceInformation->parent_id > 0 || $service_data->serviceInformation->id == '17');
                                            })->values();
                                    $arrCaregoryServiceData[$i]['service'] = $countryserviceData;

                                    if (count($countryserviceData) > 0) {

                                        foreach ($countryserviceData as $service_key => $service_data) {
                                            $sub_services = Service::where('parent_id', $service_data->serviceInformation->id)->translatedIn($locale)->get();
                                            $sub_services = $sub_services->reject(function($sub_service_detail) use($country_id) {
                                                        $sub_service_country_exist = CountryServices::where('service_id', $sub_service_detail->id)->where('country_id', $country_id)->first();
                                                        return (count($sub_service_country_exist) <= 0);
                                                    })->values();
                                            $arrCaregoryServiceData[$i]['service'][$service_key]['subcategories'] = $sub_services;
                                            $arrCaregoryServiceData[$i]['service'][$service_key]['image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_image);
                                            $arrCaregoryServiceData[$i]['service'][$service_key]['selected_image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_selected_image);
                                        }
                                    }

                                    $i++;
                                }
                                $path = realpath(dirname(__FILE__) . '/../../../public');
                                $file_name = $path . "/category_data/" . $country_id . "_" . $solution_id . "_all_data_en";
                                $file_path = $file_name . ".txt";
                                $location_resource = fopen($file_path, "w+");
                                $write_content = json_encode($arrCaregoryServiceData);

                                fwrite($location_resource, $write_content);
                            }
                        }
                    }
                }
            }
        }
    }

//    protected function  getCategoryDataToFileAr(Request $request) {
//        $arr_to_return = array();
//        //getting mobile number
//        $locale = isset($request['locale']) ? $request['locale'] : 'ar';
//        
//        \App::setLocale($locale);
//       
//        $arrCountryData= Country::all();
//       if(count($arrCountryData)>0)
//       {
//         foreach($arrCountryData as $country)
//         {
//           $arrCaregoryServiceData=array();
//       
//            $country_id = isset($country->id) ? $country->id : '0';
//            if ($locale != '' && $country_id != 0) {
//                $categoryData = Category::translatedIn($locale)->where('status', 1)->get();
//
//            if (count($categoryData) > 0) {
//                $i = 0;
//                //get all services which belong to specifc category
//                foreach ($categoryData as $category) {
//                    $j = 0;
//                    $arrCaregoryServiceData[$i] = $category;
//
//                    $category_id = $category->id;
//                    $countryserviceData = CountryServices::where('country_id', $country_id)->orderBy('sort_index_arabic','asc')->with('serviceInformation')->get();
//                    
//                           
//                    $countryserviceData = $countryserviceData->reject(function($countryservice) use ($category_id) {
//                                return ($countryservice->serviceInformation->category_id != $category_id);
//                    })->values();
//                   // $countryserviceData=$countryserviceData->sortByDesc('max_range');  
//                    $countryserviceData=$countryserviceData->reject(function($service_data)
//                    {
//                        
//                        return ($service_data->serviceInformation->parent_id>0 || $service_data->serviceInformation->id=='17');
//                    })->values();
//                    $arrCaregoryServiceData[$i]['service'] = $countryserviceData;
//                    
//                    if (count($countryserviceData) > 0) {
//
//                        foreach ($countryserviceData as $service_key => $service_data) {
//                             $sub_services=Service::where('parent_id', $service_data->serviceInformation->id)->translatedIn($locale)->get();
//                             $sub_services=$sub_services->reject(function($sub_service_detail) use($country_id)
//                             {
//                                 $sub_service_country_exist=CountryServices::where('service_id',$sub_service_detail->id)->where('country_id',$country_id)->first();
//                                 return (count($sub_service_country_exist)<=0);
//                             })->values();
//                            $arrCaregoryServiceData[$i]['service'][$service_key]['subcategories'] =$sub_services;
//                            $arrCaregoryServiceData[$i]['service'][$service_key]['image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_image);
//                            $arrCaregoryServiceData[$i]['service'][$service_key]['selected_image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_selected_image);
//                        }
//                    }
//
//                    $i++;
//                }
//                $path = realpath(dirname(__FILE__) . '/../../../public');
//                $file_name = $path . "/category_data/".$country_id."_all_data_ar";
//                $file_path = $file_name . ".txt";
//                $location_resource = fopen($file_path, "w+");
//                $write_content = json_encode($arrCaregoryServiceData);
//                
//                fwrite($location_resource, $write_content);
//            }
//         } }
//       }
//        
//    }
    protected function getCategoryDataToFileArIOS(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $locale = isset($request['locale']) ? $request['locale'] : 'ar';

        \App::setLocale($locale);

        $arrCountryData = Country::all();
        if (count($arrCountryData) > 0) {
            foreach ($arrCountryData as $country) {
                $arrCaregoryServiceData = array();

                $country_id = isset($country->id) ? $country->id : '0';
                if ($locale != '' && $country_id != 0) {
                    $categoryData = Category::translatedIn($locale)->where('status', 1)->get();

                    if (count($categoryData) > 0) {
                        $i = 0;
                        //get all services which belong to specifc category
                        foreach ($categoryData as $category) {
                            $j = 0;
                            $arrCaregoryServiceData[$i] = $category;

                            $category_id = $category->id;
                            $countryserviceData = CountryServices::where('country_id', $country_id)->orderBy('sort_index', 'asc')->with('serviceInformation')->get();


                            $countryserviceData = $countryserviceData->reject(function($countryservice) use ($category_id) {
                                        return ($countryservice->serviceInformation->category_id != $category_id);
                                    })->values();
                            // $countryserviceData=$countryserviceData->sortByDesc('max_range');  
                            $countryserviceData = $countryserviceData->reject(function($service_data) {

                                        return ($service_data->serviceInformation->parent_id > 0 || $service_data->serviceInformation->id == '17');
                                    })->values();
                            $arrCaregoryServiceData[$i]['service'] = $countryserviceData;

                            if (count($countryserviceData) > 0) {

                                foreach ($countryserviceData as $service_key => $service_data) {
                                    $sub_services = Service::where('parent_id', $service_data->serviceInformation->id)->translatedIn($locale)->get();
                                    $sub_services = $sub_services->reject(function($sub_service_detail) use($country_id) {
                                                $sub_service_country_exist = CountryServices::where('service_id', $sub_service_detail->id)->where('country_id', $country_id)->first();
                                                return (count($sub_service_country_exist) <= 0);
                                            })->values();
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['subcategories'] = $sub_services;
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_image);
                                    $arrCaregoryServiceData[$i]['service'][$service_key]['selected_image'] = asset("/storageasset/service-image/" . $service_data->serviceInformation->service_selected_image);
                                }
                            }

                            $i++;
                        }
                        $path = realpath(dirname(__FILE__) . '/../../../public');
                        $file_name = $path . "/category_data/" . $country_id . "_all_data_ar_ios";
                        $file_path = $file_name . ".txt";
                        $location_resource = fopen($file_path, "w+");
                        $write_content = json_encode($arrCaregoryServiceData);

                        fwrite($location_resource, $write_content);
                    }
                }
            }
        }
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
        $service_details_radious = Service::where('id', $service_id)->first();
        if (isset($service_details_radious->categoryInfo->request_range) && (($service_details_radious->categoryInfo->request_range) > 0)) {
            $radious = $service_details_radious->categoryInfo->request_range;
        }
        \App::setLocale($locale);
        if ($service_id == 20 || $service_id == 28) {
            $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_id)->first();
            $check_point_distance = 0;
            if (isset($arrDataService->check_point_distance) && $arrDataService->check_point_distance > 0) {
                $check_point_distance = $arrDataService->check_point_distance;
            }
            if ($duration >= $check_point_distance && $check_point_distance > 0) {
                $fare = (double) $arrDataService->flat_price;
            } else {
                if ($duration > $arrDataService->base_km) {
                    $fare = (double) ($duration) * ($arrDataService->price_per_km);
                    $fare = $fare + (double) $arrDataService->base_price;
                } else {
                    $fare = (double) $arrDataService->base_price;
                }
            }
            $fare = $fare * $number_of_person;
            $arr_to_return = array("error_code" => 0, "fare" => number_format($fare, 3));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.service_invalid', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function getfareEstimate(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $fare = 0;
        $flag = 0;
        $booking_type = 1;
        $nearest_latitude = 0;
        $nearest_longtitude = 0;
        $near_distance = 0;
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $service_id = isset($request['service_id']) ? $request['service_id'] : '0';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $city_name = isset($request['city_name']) ? $request['city_name'] : '0';
        $schedule_type = isset($request['type']) ? $request['type'] : '1';
        $distance = isset($request['distance']) ? $request['distance'] : '0';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '0';
        $current_long = isset($request['current_long']) ? $request['current_long'] : '0';
        $time = isset($request['time']) ? $request['time'] : '0';
        $time_min = ($time / 60);
        $radious = GlobalValues::get('star-range-radious');
        $service_details_radious = Service::where('id', $service_id)->first();
        if (isset($service_details_radious->categoryInfo->request_range) && (($service_details_radious->categoryInfo->request_range) > 0)) {
            $radious = $service_details_radious->categoryInfo->request_range;
        }
        $cityDetails = CityTranslation::where('name', $city_name)->first();
        if (count($cityDetails) > 0) {
            $city_id = isset($cityDetails->city_id) ? $cityDetails->city_id : '0';

            \App::setLocale($locale);
            //get service type
            $service_details = Service::where('id', $service_id)->first();
            if (count($service_details) > 0) {
                if ($service_details->service_type == '0' || $service_details->service_type == '3') {
                    $booking_type = 1;
                } else if ($service_details->service_type == '1') {
                    $booking_type = 2;
                } else if ($service_details->service_type == '2') {
                    if ($schedule_type == 0) {
                        $booking_type = 1;
                    } else {
                        $booking_type = 2;
                    }
                }
                $distance_value_check = (float) str_replace(" km", "", $distance);
                if (($service_details->max_range) < $distance_value_check) {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.service_temprary_unavailable', $locale));
                } else {
                    $required_drop_up_address = isset($service_details_radious->required_drop_up_address) ? $service_details_radious->required_drop_up_address : '0';
                    if ($required_drop_up_address == '0') {
                        if ($booking_type == 1 || $booking_type == 3) {
                            //check if the service is type of 
                            $arrServiceUsers = UserServiceInformation::where('service_id', $service_id)->get();
                            $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                                if (isset($userInfo->user->driverUserInformation->availability))
                                    return ($userInfo->user->driverUserInformation->availability == 0);
                            });
                            if (count($arrServiceUsers) > 0) {
                                $user_ids = "0";
                                $arrayUserIds = array();
                                foreach ($arrServiceUsers as $users_ids) {
                                    if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                        $user_ids .= ",$users_ids->user_id";
                                        $arrayUserIds[] = $users_ids->user_id;
                                    }
                                }
                                //
                                $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");

                                //check if a user is having any active orders
                                if (count($users) > 0) {
                                    $j = 0;
                                    foreach ($users as $user) {
                                        if (in_array($user->user_id, $arrayUserIds)) {
                                            $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();

                                            if ($userDetailsStatus->user_status == '1') {
                                                //  $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                                //$order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
//                            if (count($userData) <= 0 && count($order_notification_count)<=0) {
                                                $userAddressDetails = UserAddress::where('user_id', $user->user_id)->first();
                                                $flag = 1;
                                                $near_distance = number_format($user->distance, 3);
                                                $nearest_latitude = $userAddressDetails->latitude;
                                                $nearest_longtitude = $userAddressDetails->longitude;
                                                break;
//                            }
                                            }
                                            $j++;
                                        }
                                    }
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
                                $arrayUserIds = array();
                                foreach ($arrServiceUsers as $users_ids) {
                                    if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                        $user_ids .= ",$users_ids->user_id";
                                        $arrayUserIds[] = $users_ids->user_id;
                                    }
                                }
                                //
                                $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");

                                //check if a user is having any active orders
                                if (count($users) > 0) {

                                    $j = 0;
                                    foreach ($users as $user) {
                                        if (in_array($user->user_id, $arrayUserIds)) {
                                            $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                                            if ($userDetailsStatus->user_status == '1') {
                                                $flag = 1;
                                                $userAddressDetails = UserAddress::where('user_id', $user->user_id)->first();
                                                $near_distance = number_format($user->distance, 3);
                                                $nearest_latitude = $userAddressDetails->latitude;
                                                $nearest_longtitude = $userAddressDetails->longitude;
                                                break;
                                            }
                                            $j++;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $flag = 1;
                    }
                    if ($locale != '') {
                        if ($flag == 0 && $required_drop_up_address == '0') {
                            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
                        } else {
                            //return fare estimate
                            $arrDataService = CountryServices::where('city_id', $city_id)->where('service_id', $service_id)->first();
                            $distance_value = (float) str_replace(" km", "", $distance);

                            if ($distance == '' || $distance_value == '' || $distance_value == '0') {
                                $distance_value = $near_distance;
                            }
//                $distance_value=ceil($distance_value);
                            if (count($arrDataService) > 0) {
                                if ($arrDataService->price_type == '1') {
                                    $fare = (double) $arrDataService->base_price;
                                } else {
                                    $check_point_distance = 0;

                                    if ($distance_value > $arrDataService->base_km) {
                                        $fare = (double) $arrDataService->base_price;
                                        $extra_meter = (double) (($distance_value - $arrDataService->base_km) * 10);

                                        $per_meter_price = ($arrDataService->price_per_km) * ($extra_meter);
                                        $fare = $fare + $per_meter_price;
                                        if ($required_drop_up_address == '1') {
                                            $fare = $fare + (double) ($time_min * $arrDataService->price_per_min);
                                        }
                                    } else {
                                        $fare = (double) $arrDataService->base_price;
                                        if ($required_drop_up_address == '1') {
                                            $fare = $fare + (double) ($time_min * $arrDataService->price_per_min);
                                        }
                                    }
                                    //night charges                            
                                }
                                $current_time = date('H');
                                $night_time_from = isset($arrDataService->night_time_from) ? $arrDataService->night_time_from : '12';
                                $night_time_to = isset($arrDataService->night_time_to) ? $arrDataService->night_time_to : '5';
                                $fare_night = 0;

                                if (((($current_time < $night_time_to) && ($current_time >= $night_time_from)) || ($current_time == '24')) && (($arrDataService->night_percentage) > 0)) {
                                    $fare_night = (double) ((($fare) * $arrDataService->night_percentage) / 100);
                                }

                                if ($fare_night > 0) {
                                    $fare += $fare_night;
                                }

                                $fare = round($fare, 0);
                                $arr_to_return = array("error_code" => 0, "distance" => $near_distance, "nearest_latitude" => $nearest_latitude, "nearest_longtitude" => $nearest_longtitude, "fare" => number_format($fare, 2));
                            } else {
                                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.service_temprary_unavailable', $locale));
                            }
                        }
                    }
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.service_temprary_unavailable', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.service_temprary_unavailable', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function getfareEstimateByCategory(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $distance = 0;
        $fare = 0;
        $flag = 0;
        $booking_type = 1;
        $nearest_latitude = 0;
        $nearest_longtitude = 0;
        $near_distance = 0;
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $category_id = isset($request['category_id']) ? $request['category_id'] : '0';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $schedule_type = isset($request['type']) ? $request['type'] : '1';
        $distance = isset($request['distance']) ? $request['distance'] : '0';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '0';
        $current_long = isset($request['current_long']) ? $request['current_long'] : '0';
        $time = isset($request['time']) ? $request['time'] : '0';
        $time_min = ($time / 60);
        $radious = GlobalValues::get('star-range-radious');
        $category_details_radious = Category::where('id', $category_id)->first();
        if (isset($category_details_radious->request_range) && (($category_details_radious->request_range) > 0)) {
            $radious = $category_details_radious->request_range;
        }
        \App::setLocale($locale);

        //get service type
        //get all service as per the category
        $arrServiceEstimates = array();
        $serviceData = Service::where('category_id', $category_id)->get();
        if (count($serviceData) > 0) {
            $k = 0;
            foreach ($serviceData as $service_detial) {
                $fare = 0;
                $service_details = Service::where('id', $service_detial->id)->first();

                if (count($service_details) > 0) {
                    if ($service_details->service_type == '0' || $service_details->service_type == '3') {
                        $booking_type = 1;
                    } else if ($service_details->service_type == '1') {
                        $booking_type = 2;
                    } else if ($service_details->service_type == '2') {
                        if ($schedule_type == 0) {
                            $booking_type = 1;
                        } else {
                            $booking_type = 2;
                        }
                    }
                    $booking_type = 1;
                    $distance_value = (Double) str_replace(" km", "", $distance);
                    $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_detial->id)->first();

                    if ($distance == '' || $distance_value == '' || $distance_value == '0') {
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
                                $arrayUserIds = array();
                                foreach ($arrServiceUsers as $users_ids) {
                                    if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                        $user_ids .= ",$users_ids->user_id";
                                        $arrayUserIds[] = $users_ids->user_id;
                                    }
                                }
                                //
                                $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");

                                //check if a user is having any active orders
                                if (count($users) > 0) {
                                    $j = 0;
                                    foreach ($users as $user) {
                                        if (in_array($user->user_id, $arrayUserIds)) {
                                            $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();

                                            if ($userDetailsStatus->user_status == '1') {
                                                //  $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                                //$order_notification_count=OrderNotification::where('user_id',$user->user_id)->first();
                                                //                            if (count($userData) <= 0 && count($order_notification_count)<=0) {
                                                $userAddressDetails = UserAddress::where('user_id', $user->user_id)->first();
                                                $flag = 1;
                                                $near_distance = (double) ($user->distance);
                                                $nearest_latitude = $userAddressDetails->latitude;
                                                $nearest_longtitude = $userAddressDetails->longitude;
                                                break;
                                                //                            }
                                            }
                                            $j++;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $flag = 1;
                    }
                    if ($flag == 0) {
                        $arrServiceEstimates[$k]['service_id'] = $service_detial->id;
                        $arrServiceEstimates[$k]['price'] = 0;
                        $arrServiceEstimates[$k]['distance'] = $distance_value;
                    } else {

                        if ($distance == '' || $distance_value == '' || $distance_value == '0') {
                            $distance_value = $near_distance;
                        }
                        $distance_value = ceil($distance_value);
                        if (count($arrDataService) > 0) {
                            if ($arrDataService->price_type == '1') {
                                $fare = (double) $arrDataService->base_price;
                            } else {
                                $check_point_distance = 0;
                                if (isset($arrDataService->check_point_distance) && $arrDataService->check_point_distance > 0) {
                                    $check_point_distance = $arrDataService->check_point_distance;
                                }
                                if ($distance_value >= $check_point_distance && $check_point_distance > 0) {
                                    $fare = (double) $arrDataService->flat_price;
                                } else {
                                    if ($distance_value > $arrDataService->base_km) {
                                        $fare = (double) $arrDataService->base_price;
                                        $fare = $fare + (double) ($distance_value - $arrDataService->base_km) * ($arrDataService->price_per_km);
                                        $fare = $fare + (double) ($time_min * $arrDataService->price_per_min);
                                    } else {
                                        $fare = (double) $arrDataService->base_price;
                                        $fare = $fare + (double) ($time_min * $arrDataService->price_per_min);
                                    }
                                }
                            }
                        }
                        if ($service_detial->id != 20 && $service_detial->id != 15 && $service_detial->id != 17 && $service_detial->id != 32 && $service_detial->id != 28 && $distance_value <= $service_detial->max_range) {

                            $arrServiceEstimates[$k]['service_id'] = $service_detial->id;
                            $arrServiceEstimates[$k]['price'] = number_format($fare, 2);
                            $arrServiceEstimates[$k]['distance'] = $distance_value;
                        } else {
                            $arrServiceEstimates[$k]['service_id'] = $service_detial->id;
                            $arrServiceEstimates[$k]['price'] = 0;
                            $arrServiceEstimates[$k]['distance'] = $distance_value;
                        }
                    }
                }
                $k++;
            }
            $arr_to_return = array("error_code" => 1, "data" => $arrServiceEstimates);
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
        }

        return response()->json($arr_to_return);
    }

    protected function getdeliveryUsers(Request $request) {
        //getting mobile number
        $arr_to_return = array();
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        //  $category_id = isset($request['category_id']) ? $request['category_id'] : '0';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '0';
        $current_long = isset($request['current_long']) ? $request['current_long'] : '0';
        $radious = GlobalValues::get('star-range-radious');
        $allCategories = Category::where('status', '1')->get();
        $arrserviceAvailableUsers = array();
        $p = 0;
        $category_id = 0;
        \App::setLocale($locale);
        if (count($allCategories) > 0) {
            foreach ($allCategories as $categoryData) {
                $arrserviceAvailableUsers[$p]['category_id'] = $categoryData->id;
                $category_id = $categoryData->id;
                $category_details_radious = Category::where('id', $category_id)->first();
                if (isset($category_details_radious->request_range) && (($category_details_radious->request_range) > 0)) {
                    $radious = $category_details_radious->request_range;
                }
                $arrserviceAvailableUsers[$p]['category_name'] = $categoryData->name;

                if ($category_id != '') {
                    //get all services by category id

                    $arrServicesData = Service::where('category_id', $category_id)->get();
                    if (count($arrServicesData) > 0) {
                        $i = 0;
                        //getting all stars in particlular services
                        foreach ($arrServicesData as $service_data) {
                            //get all user who serve in this services
                            $arrserviceAvailableUsers[$p]['services'][$i]['service_name'] = $service_data->name;
                            $arrserviceAvailableUsers[$p]['services'][$i]['service_id'] = $service_data->id;
                            $arrserviceAvailableUsers[$p]['services'][$i]['category_id'] = $service_data->category_id;
                            $arrServiceUsers = UserServiceInformation::where('service_id', $service_data->id)->get();

                            $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                                if (isset($userInfo->user->driverUserInformation->availability))
                                    return ($userInfo->user->driverUserInformation->availability == 0);
                            });

                            //get all user who has only 50 km range
                            $arrserviceAvailableUsers[$p]['services'][$i]['drivers'] = array();
                            if (count($arrServiceUsers) > 0) {
                                $user_ids = "0";
                                $arrayUserIds = array();
                                foreach ($arrServiceUsers as $users_ids) {
                                    if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                        $user_ids .= ",$users_ids->user_id";
                                        $arrayUserIds[] = $users_ids->user_id;
                                    }
                                }
                                //
                                $radious = 1000;
                                $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");

                                //check if a user is having any active orders
                                if (count($users) > 0) {

                                    $j = 0;
                                    foreach ($users as $user) {
                                        if (in_array($user->user_id, $arrayUserIds)) {
                                            $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                                            if ($userDetailsStatus->user_status == '1') {
                                                $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();


                                                if (count($userData) <= 0) {
                                                    $arrserviceAvailableUsers[$p]['services'][$i]['drivers'][$j] = $user;
                                                    $j++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            //check if user is having active order right now

                            $i++;
                        }
                    }
                }$p++;
            }
        }

        $arr_to_return = array("error_code" => 0, "data" => $arrserviceAvailableUsers);
        return response()->json($arr_to_return);
    }

    protected function saveUserCard(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $card_holder_name = isset($request['card_holder_name']) ? $request['card_holder_name'] : '';
        $card_number = isset($request['card_number']) ? str_replace(" ", "", $request['card_number']) : '';
        $exp_month = isset($request['exp_month']) ? $request['exp_month'] : '';
        $exp_year = isset($request['exp_year']) ? $request['exp_year'] : '';
        $card_type = isset($request['card_type']) ? $request['card_type'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($user_id > 0 && $user_id != '') {
            $arr_user_data = User::find($user_id);
            if (count($arr_user_data) > 0) {
                //creating user cards
                $arr_card_details = array("user_id" => $user_id, "card_type" => $card_type, "name_on_card" => $card_holder_name, "card_no" => $card_number, "exp_month" => $exp_month, "exp_year" => $exp_year);

                //check if user alreayd had a card added
                $chkUserCardExist = UserCreditCard::where('card_no', $card_number)->first();
                if (count($chkUserCardExist) <= 0) {
                    $chkUserCard = UserCreditCard::where('user_id', $user_id)->first();
                    if (count($chkUserCard) > 0) {
                        $arr_card_details['is_default'] = 0;
                    } else {
                        $arr_card_details['is_default'] = 1;
                    }
                    $user_card = UserCreditCard::create($arr_card_details);

                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.user_card_added', $locale));
                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.card_exist', $locale));
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function EditUserCard(Request $request) {
        $card_id = isset($request['card_id']) ? $request['card_id'] : '';
//        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $card_holder_name = isset($request['card_holder_name']) ? $request['card_holder_name'] : '';
        $card_number = isset($request['card_number']) ? str_replace(" ", "", $request['card_number']) : '';
        $exp_month = isset($request['exp_month']) ? $request['exp_month'] : '';
        $exp_year = isset($request['exp_year']) ? $request['exp_year'] : '';
        $card_type = isset($request['card_type']) ? $request['card_type'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';

        if ($card_id > 0 && $card_id != '') {
            $arr_card_data = UserCreditCard::find($card_id);
            if (count($arr_card_data) > 0) {
                //update user cards
//                   $arr_card_data->user_id = $user_id;
                $arr_card_data->card_type = $card_type;
                $arr_card_data->name_on_card = $card_holder_name;
                $arr_card_data->card_no = $card_number;
                $arr_card_data->exp_month = $exp_month;
                $arr_card_data->exp_year = $exp_year;
                $arr_card_data->save();

                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.card_update', $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.card_id_invalid', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.card_id_invalid', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function listUserCards(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';

        if ($user_id > 0 && $user_id != '') {
            $arr_user_data = User::find($user_id);
            if (count($arr_user_data) > 0) {
                //check if user alreayd had a card added
                $chkUserCard = UserCreditCard::where('user_id', $user_id)->get();

                $arr_to_return = array("error_code" => 0, "data" => $chkUserCard);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
        }
        return response()->json($arr_to_return);
    }

    protected function removeUserCard(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $card_id = isset($request['card_id']) ? $request['card_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($user_id > 0 && $user_id != '') {
            $arr_user_data = User::find($user_id);
            if (count($arr_user_data) > 0) {
                //check if user alreayd had a card added
                $chkUserCard = UserCreditCard::where('user_id', $user_id)->where('id', $card_id)->first();

                if (count($chkUserCard) > 0) {
                    $chkUserCard->delete();
                }

                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.user_card_removed', $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.client_invalid', $locale));
        }
        return response()->json($arr_to_return);
    }

    //save order details and send notification to nearest user.

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
        $pickup_detail_address = isset($request['pickup_detail_address']) ? $request['pickup_detail_address'] : '';
        $dropoff_detail_address = isset($request['dropoff_detail_address']) ? $request['dropoff_detail_address'] : '';
        $fuel_amt = isset($request['fuel_amt']) ? $request['fuel_amt'] : '';
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
        $city_name = isset($request['city_name']) ? $request['city_name'] : '0';
        $item_description = isset($request['item_description']) ? $request['item_description'] : '';
        $payment_type = isset($request['payment_type']) ? $request['payment_type'] : '';
        //
        // $is_shared_order = isset($request['is_shared_order']) ? $request['is_shared_order'] : '';
        $passengers_count = isset($request['passengers_count']) ? $request['passengers_count'] : '';
        //
        $order_id_to_return = 0;
        $distance_value = (float) str_replace(" km", "", $distance);
        \App::setLocale($locale);
        $country_curl = '';
        $state_curl = '';
        $city_curl = '';
        $locality1_curl = '';
        $locality2_curl = '';

        $flag_available = 0;
        $avalale_driver_id = 0;
        $available_driver_ids = array();
        $radious = GlobalValues::get('star-range-radious');
        $distance_limit_to_accept_dropoff = GlobalValues::get('distance_limit_to_accept_dropoff');
        $pickup_distance_limit = GlobalValues::get('pickup_distance_limit');
        $service_details = Service::where('id', $service_id)->first();
        $is_shared_order = 0;
        $persons_limit = 0;
        if (count($service_details) > 0) {
            $is_shared_order = $service_details->is_sharable;
            $persons_limit = $service_details->number_of_person_limit;
        }

        if (isset($service_details->categoryInfo->request_range) && (($service_details->categoryInfo->request_range) > 0)) {
            $radious = $service_details->categoryInfo->request_range;
        }
        $instant_order_minutes = GlobalValues::get('instant-order-minutes');
        $service_type = 1;
        $countryInfo = Country::where('id', $country_id)->first();
        $cityInfo = CityTranslation::where('name', $city_name)->first();
        $city_id = isset($cityInfo->city_id) ? $cityInfo->city_id : '0';

        //check for user details
        $userInformationCheck = UserInformation::where('user_id', $user_id)->first();

        if (count($userInformationCheck) <= 0) {
            $arr_to_return = array("error_code" => 4, "msg" => Lang::choice('messages.account_has_deleted_invalid_user', $locale));
        } else if (count($userInformationCheck) > 0 && $userInformationCheck->user_status == '2') {
            $arr_to_return = array("error_code" => 5, "msg" => Lang::choice('messages.account_has_blocked_invalid_user', $locale));
        } else {
            //
            //get service type
            $service_details = Service::where('id', $service_id)->first();
            $category_id = isset($service_details->category_id) ? $service_details->category_id : '';
            if ($service_details->service_type == '0' || $service_details->service_type == '3') {
                $service_type = 1;
            } else if ($service_details->service_type == '1') {
                $service_type = 2;
            } else if ($service_details->service_type == '2') {
                if ($schedule_type == 0) {
                    $service_type = 1;
                } else {
                    $service_type = 2;
                }
            }

            //adding time
            if ($service_type == 1) {
                $dt1 = new DateTime(date('Y-m-d H:i:s'));

                //get timezone as per country
                // Config::set('app.timezone',$countryInfo->time_zone);
                if (count($countryInfo) > 0) {
                    $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                    $dt1->setTimezone($tz);
                }

                $date_time_val = $dt1->format('Y-m-d H:i:s');
                $order_date_time = new DateTime($date_time_val);
                //$order_date_time = new DateTime($order_date_time);
                $order_date_time->modify("+{$instant_order_minutes} minutes");
            }
            if ($pick_up_lat != '' && $pick_up_long != '') {
                if ($user_id > 0 && $user_id != '') {

                    $arr_user_data = User::find($user_id);
                    //check for payment type and then if user has added any card
                    //getting getting credit cards.
                    if ($user_id != '' && $payment_type == '1') {
                        $arrUserCards = UserCreditCard::where("user_id", $user_id)->get();
                        if (count($arrUserCards) <= 0) {

                            $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.need_to_add_card', $locale));
                            return response()->json($arr_to_return);
                        }
                    }

                    if (count($arr_user_data) > 0) {

                        // first checking and get first available star to inform


                        $arrDataService = CountryServices::where('country_id', $country_id)->where('service_id', $service_id)->first();

                        if ($fare_amount != '0' && $fare_amount != '') {
                            $fare = $fare_amount;
                        } else {
                            $fare = 0;
                            if (count($arrDataService) > 0) {
                                if ($arrDataService->price_type == '1') {
                                    $fare = (double) $arrDataService->base_price;
                                } else {
                                    $check_point_distance = 0;
                                    if (isset($arrDataService->check_point_distance) && $arrDataService->check_point_distance > 0) {
                                        $check_point_distance = $arrDataService->check_point_distance;
                                    }
                                    if ($distance_value >= $check_point_distance && $check_point_distance > 0) {
                                        $fare = (double) $arrDataService->flat_price;
                                    } else {
                                        if ($distance_value > $arrDataService->base_km) {
                                            $fare = (double) $arrDataService->base_price;
                                            $fare = $fare + (double) ($distance_value - $arrDataService->base_km) * ($arrDataService->price_per_km);
                                            //                         
                                            //                                         $fare = (double) ($distance_value) * ($arrDataService->price_per_km);
                                            //                                         $fare=$fare + (double)$arrDataService->base_price;
                                        } else {
                                            $fare = (double) $arrDataService->base_price;
                                        }
                                    }
                                }
                            }
                        }

                        if ($payment_type == 2) {
                            //check wallter amount
                            $userWalletDetails = UserWalletDetail::where('user_id', $user_id)->orderBy('id', 'desc')->first();
                            if (isset($userWalletDetails) && count($userWalletDetails)) {
                                $wallet_amount = $userWalletDetails->final_amout;
                            } else {
                                $wallet_amount = 0;
                            }

                            $final_remaining_wallter_amount = 0;
                            //get all actve pending order of this user by using wallte payment
                            $arrStatus = array("0", "1");
                            $order_amt_total = Order::where('mate_id', $user_id)->whereIn('status', $arrStatus)->sum('fare_amount');
                            $final_remaining_wallter_amount = $wallet_amount - $order_amt_total;
                            if ($fare > $final_remaining_wallter_amount) {
                                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.wallet_insufficient', $locale));
                                return response()->json($arr_to_return);
                            }
                        }

                        $dt = new DateTime(date('Y-m-d H:i:s'));

                        //get timezone as per country
                        Config::set('app.timezone', $countryInfo->time_zone);
                        if (count($countryInfo) > 0) {
                            $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                            $dt->setTimezone($tz);
                        }
                        $date2_val = $dt->format('Y-m-d H:i:s');
                        $date2 = new DateTime($date2_val);
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
                        $arrOrder['city_id'] = $city_id;
                        $arrOrder['status'] = 0;
                        $arrOrder['is_shared_order'] = $is_shared_order;
                        $arrOrder['passengers_count'] = $passengers_count;
                        $arrOrder['payment_type'] = $payment_type;
                        $arrOrder['created_at'] = $date2;
                        $arrOrder['updated_at'] = $date2;
                        $arrOrder['locale'] = $locale;
                        $order = Order::create($arrOrder);
                        $order_id_to_return = $order->id;
                        $arrOrderDetails = array();
                        $arrOrderDetails['order_id'] = $order->id;
                        $arrOrderDetails['selected_pickup_lat'] = $pick_up_lat;
                        $arrOrderDetails['selected_pickup_long'] = $pick_up_long;
                        $arrOrderDetails['pickup_area'] = $pick_up_area;
                        $arrOrderDetails['dropoff_detail_address'] = $dropoff_detail_address;
                        $arrOrderDetails['pickup_detail_address'] = $pickup_detail_address;
                        $arrOrderDetails['selected_drop_lat'] = $drop_up_lat;
                        $arrOrderDetails['selected_drop_long'] = $drop_up_long;
                        $arrOrderDetails['drop_area'] = $drop_up_area;
                        $arrOrderDetails['contact_person_for_pickup'] = $pick_up_person_name;
                        $arrOrderDetails['contact_person_for_destination'] = $drop_up_person_name;
                        $arrOrderDetails['pickup_person_contact_no'] = $pick_up_person_mobile_number;
                        $arrOrderDetails['destination_person_contact_no'] = $drop_up_person_mobile_number;
                        $arrOrderDetails['distance'] = $distance;
                        $arrOrderDetails['distance_value'] = $distance_value;
                        $arrOrderDetails['marine_duration'] = $marine_duration;
                        $arrOrderDetails['number_of_person'] = $number_of_person;
                        $arrOrderDetails['coupon_code'] = $coupon_code;
                        $arrOrderDetails['duration'] = $duration;
                        $arrOrderDetails['item_description'] = $item_description;
                        $arrOrderDetails['created_at'] = $date2;
                        $arrOrderDetails['updated_at'] = $date2;
                        $arrOrderDetails['fuel_amt'] = $fuel_amt;
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
                                $command = "/usr/local/bin/convert " . $old_file . " -resize 200x150^ " . $new_file;
                                exec($command);
                                $arrOrderImages['order_id'] = $order->id;
                                $arrOrderImages['item_image'] = $new_file_name;
                                OrderItemImage::create($arrOrderImages);
                            }
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
                            if ($user_details->user->hasRole('superadmin')) {
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
//                        $companyusers = UserInformation::where('user_type', 5)->get();
//                        $companyusers = $companyusers->reject(function($user_details) use ($country_id) {
//                            $country = 0;
//                            if (isset($user_details->user->userAddress)) {
//
//                                foreach ($user_details->user->userAddress as $address) {
//                                    $country = $address->user_country;
//                                }
//                            }
//                            if ($country && $country != 0) {
//                                return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
//                            }
//                        });
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
//                        if (count($agentusers) > 0) {
//                            foreach ($agentusers as $agent) {
//
//                                Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
//                                    if (isset($agent->user->email)) {
//                                        $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
//                                    }
//                                });
//                            }
//                        }
//                      
                        //sending email to site admin
                        Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title) {
                            if (isset($site_email)) {
                                $message->to($site_email)->subject($email_template_subject)->from($site_email, $site_title);
                            }
                        });

                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_success', $locale), "order_id" => $order_id_to_return);
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

    protected function makeAnOrderSendRequest(Request $request) {

        $order_id = isset($request['order_id']) ? $request['order_id'] : '';
        $orderDetails = Order::where('id', $order_id)->first();
        $locale = isset($orderDetails->locale) ? $orderDetails->locale : 'en';
        $service_id = isset($orderDetails->service_id) ? $orderDetails->service_id : '';
        $user_id = isset($orderDetails->mate_id) ? $orderDetails->mate_id : '';
        $distance = isset($orderDetails->getOrderTransInformation->distance_value) ? $orderDetails->getOrderTransInformation->distance_value : '';
        $number_of_person = isset($orderDetails->getOrderTransInformation->number_of_person) ? $orderDetails->getOrderTransInformation->number_of_person : '';
        $marine_duration = isset($orderDetails->getOrderTransInformation->marine_duration) ? $orderDetails->getOrderTransInformation->marine_duration : '';
        $pick_up_lat = isset($orderDetails->getOrderTransInformation->selected_pickup_lat) ? $orderDetails->getOrderTransInformation->selected_pickup_lat : '';
        $pick_up_long = isset($orderDetails->getOrderTransInformation->selected_pickup_long) ? $orderDetails->getOrderTransInformation->selected_pickup_long : '';
        $drop_up_lat = isset($orderDetails->getOrderTransInformation->selected_drop_lat) ? $orderDetails->getOrderTransInformation->selected_drop_lat : '';
        $drop_up_long = isset($orderDetails->getOrderTransInformation->selected_drop_long) ? $orderDetails->getOrderTransInformation->selected_drop_long : '';
        $pick_up_area = isset($orderDetails->getOrderTransInformation->pickup_area) ? $orderDetails->getOrderTransInformation->pickup_area : '';
        $drop_up_area = isset($orderDetails->getOrderTransInformation->drop_up_area) ? $orderDetails->getOrderTransInformation->drop_up_area : '';
        $pickup_detail_address = isset($orderDetails->getOrderTransInformation->pickup_detail_address) ? $orderDetails->getOrderTransInformation->pickup_detail_address : '';
        $dropoff_detail_address = isset($orderDetails->getOrderTransInformation->dropoff_detail_address) ? $orderDetails->getOrderTransInformation->dropoff_detail_address : '';
        $fuel_amt = isset($orderDetails->getOrderTransInformation->fuel_amt) ? $orderDetails->getOrderTransInformation->fuel_amt : '';
        $pick_up_person_name = isset($orderDetails->getOrderTransInformation->contact_person_for_pickup) ? $orderDetails->getOrderTransInformation->contact_person_for_pickup : '';
        $pick_up_person_mobile_number = isset($orderDetails->getOrderTransInformation->pickup_person_contact_no) ? $orderDetails->getOrderTransInformation->pickup_person_contact_no : '';
        $drop_up_person_name = isset($orderDetails->getOrderTransInformation->contact_person_for_destination) ? $orderDetails->getOrderTransInformation->contact_person_for_destination : '';
        $drop_up_person_mobile_number = isset($orderDetails->getOrderTransInformation->destination_person_contact_no) ? $orderDetails->getOrderTransInformation->destination_person_contact_no : '';
        $coupon_code = isset($orderDetails->getOrderTransInformation->coupon_code) ? $orderDetails->getOrderTransInformation->coupon_code : '';
        $order_date_time = isset($orderDetails->order_place_date_time) ? $orderDetails->order_place_date_time : '';
        $fare_amount = isset($orderDetails->fare_amount) ? $orderDetails->fare_amount : '';
        $service_type = isset($orderDetails->order_type) ? $orderDetails->order_type : '';
        $country_id = isset($orderDetails->country_id) ? $orderDetails->country_id : '';
        $item_description = isset($orderDetails->getOrderTransInformation->item_description) ? $orderDetails->getOrderTransInformation->item_description : '';
        $payment_type = isset($orderDetails->payment_type) ? $orderDetails->payment_type : '';
        $is_shared_order = isset($orderDetails->is_shared_order) ? $orderDetails->is_shared_order : '';
        $passengers_count = isset($orderDetails->passengers_count) ? $orderDetails->passengers_count : '';
        $order_number = isset($orderDetails->order_unique_id) ? $orderDetails->order_unique_id : '';
        $flag_available = 0;
        $avalale_driver_id = 0;
        $available_driver_ids = array();
        $radious = GlobalValues::get('star-range-radious');
        $distance_limit_to_accept_dropoff = GlobalValues::get('distance_limit_to_accept_dropoff');
        $pickup_distance_limit = GlobalValues::get('pickup_distance_limit');
        $service_details = Service::where('id', $service_id)->first();
        $is_shared_order = 0;
        $persons_limit = 0;
        if (count($service_details) > 0) {
            $is_shared_order = $service_details->is_sharable;
            $persons_limit = $service_details->number_of_person_limit;
        }
        //fetching user location 

        if (isset($service_details->categoryInfo->request_range) && (($service_details->categoryInfo->request_range) > 0)) {
            $radious = $service_details->categoryInfo->request_range;
        }
        $instant_order_minutes = GlobalValues::get('instant-order-minutes');

        $countryInfo = Country::where('id', $country_id)->first();


        //check for user details
        $userInformationCheck = UserInformation::where('user_id', $user_id)->first();
        $orderNotificationData = OrderNotification::where('order_id', $order_id)->first();
        if (count($orderNotificationData) > 0) {
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_success', $locale));
        } else {
            if (count($userInformationCheck) <= 0) {
                $arr_to_return = array("error_code" => 4, "msg" => Lang::choice('messages.account_has_deleted_invalid_user', $locale));
            } else if (count($userInformationCheck) > 0 && $userInformationCheck->user_status == '2') {
                $arr_to_return = array("error_code" => 5, "msg" => Lang::choice('messages.account_has_blocked_invalid_user', $locale));
            } else {
                //
                //get service type
                $service_details = Service::where('id', $service_id)->first();
                $category_id = $service_details->category_id;
                //adding time
                if ($service_type == 1) {
                    $dt1 = new DateTime(date('Y-m-d H:i:s'));

                    //get timezone as per country
                    // Config::set('app.timezone',$countryInfo->time_zone);
                    if (count($countryInfo) > 0) {
                        $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                        $dt1->setTimezone($tz);
                    }

                    $date_time_val = $dt1->format('Y-m-d H:i:s');
                    $order_date_time = new DateTime($date_time_val);
                    //$order_date_time = new DateTime($order_date_time);
                    $order_date_time->modify("+{$instant_order_minutes} minutes");
                }

                if ($pick_up_lat != '' && $pick_up_long != '') {
                    if ($user_id > 0 && $user_id != '') {

                        $arr_user_data = User::find($user_id);
                        //check for payment type and then if user has added any card
                        //getting getting credit cards.
                        if ($user_id != '' && $payment_type == '1') {
                            $arrUserCards = UserCreditCard::where("user_id", $user_id)->get();
                            if (count($arrUserCards) <= 0) {

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

                                    //if order is shared,we will check with near shared  user's only  
                                    if ($is_shared_order == '1') {
                                        $user_ids = "0";
                                        $arrayUserIdShared = array();
                                        foreach ($arrServiceUsers as $users_ids) {
                                            if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                                $user_ids .= ",$users_ids->user_id";
                                                $arrayUserIdShared[] = $users_ids->user_id;
                                            }
                                        }

                                        //
                                        $users = DB::select("call getUserByDistance(" . $pick_up_lat . "," . $pick_up_long . ",'" . $user_ids . "'," . $radious . ")");

                                        //check if a user is having any active orders
                                        if (count($users) > 0) {
                                            $k = 0;

                                            foreach ($users as $user) {

                                                if (in_array($user->user_id, $arrayUserIdShared)) {
                                                    $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                                                    if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                                        $arrServiceUsersInfo = UserServiceInformation::where('service_id', $service_id)->where('user_id', $user->user_id)->first();
                                                        if (isset($arrServiceUsersInfo->goe_fence_area) && ($arrServiceUsersInfo->goe_fence_area >= $user->distance)) {
                                                            $userShareOrder = Order::where('driver_id', $user->user_id)->where('is_shared_order', '1')->where('status', '1')->get();
                                                            if (count($userShareOrder) > 0) {
                                                                $userData = Order::where('driver_id', $user->user_id)->where('is_shared_order', '0')->where('status', '1')->first();
                                                                $order_notification_count = OrderNotification::where('user_id', $user->user_id)->first();
                                                                $order_notification_count_single = OrderNotification::where('order_id', $order_id)->first();
                                                                $payment_method_id = isset($payment_type) ? $payment_type : '2';
                                                                $paymentMethods = UserPaymentMethod::where('user_id', $user->user_id)->where('payment_method_id', $payment_method_id)->first();
                                                                $already_person_travel = $userShareOrder->sum('number_of_person');
                                                                if ($persons_limit >= ($already_person_travel + $passengers_count)) {
                                                                    if (count($userData) <= 0 && count($order_notification_count_single) <= 0 && count($order_notification_count) <= 0 && count($paymentMethods) > 0) {
                                                                        // $available_driver_ids[]= $user->user_id;
                                                                        $flag_available = 1;
                                                                        $avalale_driver_id = $user->user_id;
                                                                        break;
                                                                    }
                                                                }
                                                            } else {
                                                                $userData = Order::where('driver_id', $user->user_id)->where('is_shared_order', '0')->where('status', '1')->first();
                                                                //check if user has notification of any orders
                                                                $order_notification_count = OrderNotification::where('user_id', $user->user_id)->first();
                                                                $order_notification_count_single = OrderNotification::where('order_id', $order_id)->first();
                                                                $payment_method_id = isset($request['payment_type']) ? $request['payment_type'] : '2';
                                                                $paymentMethods = UserPaymentMethod::where('user_id', $user->user_id)->where('payment_method_id', $payment_method_id)->first();
                                                                if ($persons_limit >= $passengers_count) {
                                                                    if (count($userData) <= 0 && count($order_notification_count_single) <= 0 && count($order_notification_count) <= 0 && count($paymentMethods) > 0) {
                                                                        // $available_driver_ids[]= $user->user_id;
                                                                        $flag_available = 1;
                                                                        $avalale_driver_id = $user->user_id;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    $k++;
                                                }
                                            }
                                        }
                                    } else if ($is_shared_order != '1' || $flag_available == 0) {

                                        $user_ids = "0";
                                        $arrayUserIds = array();
                                        foreach ($arrServiceUsers as $users_ids) {
                                            if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                                                $user_ids .= ",$users_ids->user_id";
                                                $arrayUserIds[] = $users_ids->user_id;
                                            }
                                        }

                                        //
                                        $users = DB::select("call getUserByDistance(" . $pick_up_lat . "," . $pick_up_long . ",'" . $user_ids . "'," . $radious . ")");

                                        //check if a user is having any active orders
                                        if (count($users) > 0) {
                                            $j = 0;

                                            foreach ($users as $user) {

                                                if (in_array($user->user_id, $arrayUserIds)) {
                                                    $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();

                                                    if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                                        $arrServiceUsersInfo = UserServiceInformation::where('service_id', $service_id)->where('user_id', $user->user_id)->first();
                                                        if (isset($arrServiceUsersInfo->goe_fence_area) && ($arrServiceUsersInfo->goe_fence_area >= $user->distance)) {

                                                            $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                                            //check if user has notification of any orders
                                                            $order_notification_count = OrderNotification::where('user_id', $user->user_id)->first();
                                                            $order_notification_count_single = OrderNotification::where('order_id', $order_id)->first();
                                                            $payment_method_id = isset($request['payment_type']) ? $request['payment_type'] : '2';
                                                            $paymentMethods = UserPaymentMethod::where('user_id', $user->user_id)->where('payment_method_id', $payment_method_id)->first();
                                                            if (count($userData) <= 0 && count($order_notification_count_single) <= 0 && count($order_notification_count) <= 0 && count($paymentMethods) > 0) {
                                                                // $available_driver_ids[]= $user->user_id;
                                                                $flag_available = 1;
                                                                $avalale_driver_id = $user->user_id;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    $j++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if ($flag_available == 0) {
                                //storing details of order for which star was not available

                                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.no_star_available_for_this_service', $locale));
                            } else {

                                if ($payment_type == 2) {
                                    //check wallter amount
                                    $userWalletDetails = UserWalletDetail::where('user_id', $user_id)->orderBy('id', 'desc')->first();
                                    if (isset($userWalletDetails) && count($userWalletDetails)) {
                                        $wallet_amount = $userWalletDetails->final_amout;
                                    } else {
                                        $wallet_amount = 0;
                                    }

                                    $final_remaining_wallter_amount = 0;
                                    //get all actve pending order of this user by using wallte payment
                                    $arrStatus = array("0", "1");
                                    $order_amt_total = Order::where('mate_id', $user_id)->whereIn('status', $arrStatus)->sum('fare_amount');
                                    $final_remaining_wallter_amount = $wallet_amount - $order_amt_total;
                                    if ($fare > $final_remaining_wallter_amount) {
                                        $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.wallet_insufficient', $locale));
                                        return response()->json($arr_to_return);
                                    }
                                }

                                $dt = new DateTime(date('Y-m-d H:i:s'));

                                if ($service_type != 2) {
                                    $dt = new DateTime(date('Y-m-d H:i:s'));

                                    //get timezone as per country
                                    Config::set('app.timezone', $countryInfo->time_zone);
                                    if (count($countryInfo) > 0) {
                                        $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                        $dt->setTimezone($tz);
                                    }

                                    $date2_val = $dt->format('Y-m-d H:i:s');
                                    $date2 = new DateTime($date2_val);
                                    $notification_start_time = $date2_val;
                                    $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . GlobalValues::get('star-reject-time') . ' min'));
                                    //sending push notification to star user which is nearest available
                                    if ($avalale_driver_id != '') {
                                        //storing that user in notification table.
                                        $arrOrderNotificationDetails['order_id'] = $orderDetails->id;
                                        $arrOrderNotificationDetails['created_at'] = $date2;
                                        $arrOrderNotificationDetails['updated_at'] = $date2;
                                        $arrOrderNotificationDetails['user_id'] = $avalale_driver_id;
                                        $arrOrderNotificationDetails['message'] = $order_number . " " . Lang::choice('messages.order_assigned', $locale);
                                        OrderNotification::create($arrOrderNotificationDetails);

                                        $order_assigned_status = array();
                                        $order_assigned_status['order_id'] = $orderDetails->id;
                                        $order_assigned_status['user_id'] = $avalale_driver_id;
                                        $order_assigned_status['reason_text'] = "Assigned to this user";
                                        //storing cancel reason
                                        OrderAssignedDetail::create($order_assigned_status);

                                        $avalale_star_details = UserInformation::where('user_id', $avalale_driver_id)->first();
                                        if (isset($avalale_star_details->device_id) && $avalale_star_details->device_id != '') {
                                            if ($service_id == 20 || $service_id == 28) {
                                                $arr_push_message = array("sound" => "default", "title" => "BAGGI", "flag" => 'order_quotation_request', 'message' => Lang::choice('messages.new_request_to_bid', $locale), 'order_id' => $orderDetails->id);
                                                $arr_push_message_ios = $orderDetails->id . ": " . Lang::choice('messages.new_request_to_bid', $locale);
                                            } else {
                                                $arr_push_message = array("sound" => "default", "title" => "BAGGI", "text" => Lang::choice('messages.order_assign_star', $locale), "flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_star', $locale), 'order_id' => $orderDetails->id, 'notification_start_time' => $notification_start_time, 'notification_end_time' => $notification_end_time);
                                                $arr_push_message_ios = array();
                                            }
                                            $obj_send_push_notification = new SendPushNotification();
                                            if ($avalale_star_details->device_type == '0') {
                                                //sending push notification star user.
                                                $arr_push_message_android = array();
                                                $arr_push_message_android['to'] = $avalale_star_details->device_id;
                                                $arr_push_message_android['priority'] = "high";
                                                $arr_push_message_android['sound'] = "default";
                                                $arr_push_message_android['notification'] = $arr_push_message;

                                                $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                                            } else {
                                                $arr_push_message_ios['to'] = $avalale_star_details->device_id;
                                                $arr_push_message_ios['priority'] = "high";
                                                $arr_push_message_ios['sound'] = "default";
                                                $arr_push_message_ios['notification'] = $arr_push_message;
                                                $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));

//                                          $response = PushNotification::app('appNameIOS')
//                                             ->to($avalale_star_details->device_id)
//                                               ->send(($arr_push_message_ios));
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
                                    if ($user_details->user->hasRole('superadmin')) {
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
//                        $companyusers = UserInformation::where('user_type', 5)->get();
//                        $companyusers = $companyusers->reject(function($user_details) use ($country_id) {
//                            $country = 0;
//                            if (isset($user_details->user->userAddress)) {
//
//                                foreach ($user_details->user->userAddress as $address) {
//                                    $country = $address->user_country;
//                                }
//                            }
//                            if ($country && $country != 0) {
//                                return (($country != $country_id) || ($user_details->user->supervisor_id != 0));
//                            }
//                        });
                                $site_email = GlobalValues::get('site-email');
                                $site_title = GlobalValues::get('site-title');
                                //Assign values to all macros
                                $arr_keyword_values['ORDER_NUMBER'] = $order_number;
                                $arr_keyword_values['ORDER_ID'] = $orderDetails->id;
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
//                        if (count($agentusers) > 0) {
//                            foreach ($agentusers as $agent) {
//
//                                Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agent, $site_email, $site_title) {
//                                    if (isset($agent->user->email)) {
//                                        $message->to($agent->user->email)->subject($email_template_subject)->from($site_email, $site_title);
//                                    }
//                                });
//                            }
//                        }
//                      
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
        }

        return response()->json($arr_to_return);
    }

    protected function getDriverDetails(Request $request) {
        $order_id = isset($request['order_id']) ? $request['order_id'] : '';
        $orderDetails = Order::where('id', $order_id)->first();
        $staruserDetails = array();
        if (isset($orderDetails->driver_id) && ($orderDetails->driver_id > 0)) {
            $star_user_details = UserInformation::where('user_id', $orderDetails->driver_id)->first();
            $staruserDetails['star_first_name'] = $star_user_details->first_name;
            $staruserDetails['star_last_name'] = $star_user_details->last_name;
            $staruserDetails['star_mobile'] = "+" . str_replace("+", "", $star_user_details->mobile_code) . "" . $star_user_details->user_mobile;
            if (isset($star_user_details->profile_picture)) {
                $staruserDetails['star_image'] = asset("/storageasset/user-images/" . $star_user_details->profile_picture);
            } else {
                $staruserDetails['star_image'] = "";
            }
            //getting avarage rating
            $userRating = UserRatingInformation::where('to_id', $orderDetails->driver_id)->where('status', '1')->avg('rating');
            $staruserDetails['star_rating'] = isset($userRating) ? $userRating : '0';
            //getting vehicle inforamtion
            $userDriverDetails = DriverAssignedDetail::where('user_id', $orderDetails->driver_id)->first();
            $vehicleDetails = "";
            if (count($userDriverDetails) > 0) {
                $vehcile_make = isset($userDriverDetails->vehicleInformation->vehicle_name) ? $userDriverDetails->vehicleInformation->vehicle_name : '';
                $vehicleDetails = $vehcile_make . "- " . isset($userDriverDetails->vehicleInformation->plate_number) ? $userDriverDetails->vehicleInformation->plate_number : '';
            }
            $staruserDetails['star_vehicle'] = $vehicleDetails;
            $staruserDetails['fare_amount'] = isset($orderDetails->fare_amount) ? $orderDetails->fare_amount : '';
            $arr_to_return = array("error_code" => 0, "driver_details" => $staruserDetails);
        } else {
            $arr_to_return = array("error_code" => 1);
        }
        return response()->json($arr_to_return);
    }

    protected function checkMsg() {
        $mobile_number_to_send = "+249912443746";
        echo $message = "Hi- From BAGGI";
        Twilio::message($mobile_number_to_send, $message);
        echo "message sent";
        die;
    }

    protected function checkPushNotification() {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $device_id = isset($request['device_id']) ? $request['device_id'] : 'en';
        $message_text = 'A new order(122) has bee assigned to you';
        $data['alert'] = "ddfssdffsd";
        $message = ($message_text);
        $arr_push_message = array("flag" => 'order_post', 'alert' => "dd", 'message' => Lang::choice('messages.order_assign_star', $locale), 'order_id' => 5);

        $this->iOSPushNotificaton();

        echo "Hey Anuj";
        die;
        $response = PushNotification::app('appNameIOSCheck')
                //->to('36698c86685c7e9ea4c3a2929c5da9f0eee2d3cebb7ac51abd5d4b17f30db2bb')
                ->to('f-cCqdIlehc:APA91bGZz_rAoQKRb9CqGIZwrZ3jgzniAVJtOPN1ghiKn0escwe7RnnF41Qm9c9LwR-cp5mgImvlpPVRC-bqcXwlV3MJheDXaOjFVblfR-yeRYT1b6TRiOL1jiXfbHcXmQCgkTMxeYCn')
                // ->to('29a39a6364ebfdbf9ca6f31fc058b42ec7ad976ab54cc896836f6c252044e9a6')
                ->send(json_encode($arr_push_message));
        $arr_to_return = array("error_code" => 0);
        return response()->json($arr_to_return);
    }

    protected function updateDeliveryuserCurrentLocation(Request $request) {

        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $user_id = isset($request['user_id']) ? $request['user_id'] : '0';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '0';
        $current_long = isset($request['current_long']) ? $request['current_long'] : '0';
        $locations = isset($request['location']) ? $request['location'] : '';
        $distance = 0;
        $flag = 0;
        $insert_flag = 0;
        if ($flag == 0) {
            if ($current_lat) {
                $userAddress = UserAddress::where('user_id', $user_id)->first();
                if (isset($userAddress) && count($userAddress)) {
                    $userAddress->user_current_latitude = $current_lat;
                    $userAddress->user_current_longtitude = $current_long;
                    $userAddress->latitude = $current_lat;
                    $userAddress->longitude = $current_long;
                    $userAddress->save();
                    //update location of user as per give
                }
            }
        }
        //check the current order of star
        $orderDetails = Order::where('driver_id', $user_id)->where('status', '1')->first();


        if (isset($orderDetails) && count($orderDetails)) {

//            if ($locations != '') {
            $path = realpath(dirname(__FILE__) . '/../../../public');
            $file_name = $path . "/order_tracking/order_" . $orderDetails->id;
            $file_path = $file_name . ".txt";
            if (!(file_exists($file_path))) {
                $location_resource = fopen($file_path, "a+");
                $write_content = $current_lat . "," . $current_long . "" . PHP_EOL;
                fwrite($location_resource, $write_content);
                fclose($location_resource);
            } else {

                $location_resource = fopen($file_path, "a+");
                $write_content = $current_lat . "," . $current_long . "" . PHP_EOL;
                $file_last_lat_long = escapeshellarg($file_path); // for the security concious (should be everyone!)
                $last_lat_long = `tail -n 1 $file_last_lat_long`;

                if (($orderDetails->status_by_star) > 1) {

                    if (isset($last_lat_long) && $last_lat_long != '') {
                        $arr_last_lat_long = explode(",", $last_lat_long);

                        if (count($arr_last_lat_long) > 0) {
                            $last_lat = str_replace("\n", "", $arr_last_lat_long[0]);
                            $last_long = str_replace("\n", "", $arr_last_lat_long[1]);
                            if (($current_lat == $last_lat) && ($current_long == $last_long)) {
                                $insert_flag = 1;
                            }

                            if ($insert_flag == 0) {
                                $distance = $this->getDistanceBetweenPointsNew($current_lat, $current_long, $last_lat, $last_long, 'Km');
                            }
                        }
                    }

                    if ($insert_flag == 0) {
                        //update distance
                        $file_name1 = $path . "/order_tracking/order_distance_" . $orderDetails->id;
                        $path1 = $file_name1 . ".txt";
                        $location_resource1 = fopen($path1, "a+");
                        $distance = (float) (round($distance, 5));

                        if ($distance > 0) {

                            $write_content1 = $distance . "" . PHP_EOL;
                            fwrite($location_resource1, $write_content1);
                        }
                        fclose($location_resource1);
                    }
                }
                if (isset($last_lat_long) && $last_lat_long != '') {
                    $arr_last_lat_long = explode(",", $last_lat_long);

                    if (count($arr_last_lat_long) > 0) {
                        $last_lat = str_replace("\n", "", $arr_last_lat_long[0]);
                        $last_long = str_replace("\n", "", $arr_last_lat_long[1]);


                        if (($current_lat == $last_lat) && ($current_long == $last_long)) {
                            $insert_flag = 1;
                        }
                    }
                }
                if ($insert_flag == 0) {
                    fwrite($location_resource, $write_content);
                }
                fclose($location_resource);
            }
        }

        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.location_updated', $locale));
        return response()->json($arr_to_return);
    }

    protected function getDistanceBetweenPointsNew($lat1, $long1, $lat2, $long2, $unit = 'Km') {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&key=AIzaSyBwFtxLlj0Bs_4akvRQGPGRFPH5cCV48_4";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
        // $time = $response_a['rows'][0]['elements'][0]['duration']['text'];

        $distance = 0;
        if (strstr($dist, " km")) {
            $distance_value = explode(",", $dist);
            $distance1 = isset($distance_value[0]) ? str_replace(" km", "", $distance_value[0]) : '0';
            $distance2 = isset($distance_value[1]) ? str_replace(" km", "", $distance_value[1]) : '0';
            $distance = $distance1 . "." . $distance2;
        } else {

            $distance1 = str_replace(" m", "", $dist);
            $distance = ($distance1) / 1000;
        }

        return ((float) ($distance));
    }

    protected function trackDeliveryuserOrderLocation(Request $req) {


        $locale = isset($req['locale']) ? $req['locale'] : 'en';
        $order_id = isset($req['order_id']) ? $req['order_id'] : '0';

        \App::setLocale($locale);
        $location_data = "";
        $arrLocationData = array();

        if ($order_id > 0) {

            //fecth the file
            $path = realpath(dirname(__FILE__) . '/../../../public');
            $file_name = $path . "/order_tracking/order_" . $order_id;
            $path = $file_name . ".txt";
            if (file_exists($path)) {
                $location_resource = fopen($path, "r");
                $i = 0;
                while (!feof($location_resource)) {
                    $line = fgets($location_resource);
                    $line_data = explode(",", $line);

                    if (isset($line_data[0]) && isset($line_data[1])) {
                        $arrLocationData[$i]['lat'] = $line_data[0];
                        $arrLocationData[$i]['long'] = str_replace("\n", "", $line_data[1]);
                        $i++;
                    }
                }
                $arr_to_return = array("error_code" => 0, "data" => ($arrLocationData));
            } else {
                $arr_to_return = array("error_code" => 0, "data" => ($arrLocationData));
            }
        }

        return response()->json($arr_to_return);
    }

    protected function getAllRatingQuestions(Request $request) {

        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $ratingQuestion = array();
        $all_rating_questions = array("1", "2", "3", "4", "5");
        $i = 0;
        \App::setLocale($locale);
        foreach ($all_rating_questions as $question) {

            $arrRatingTags = RatingQuestion::where('rating_star_no', $question)->get();
            if (count($arrRatingTags) > 0) {
                $j = 0;
                foreach ($arrRatingTags as $rating_tag) {
                    $ratingQuestion[$i][$j]['title'] = $rating_tag->ques_title;
                    $ratingQuestion[$i][$j]['id'] = $rating_tag->id;
                    $j++;
                }
            }
            $i++;
        }
        $arr_to_return = array("error_code" => 0, "data" => ($ratingQuestion));
        return response()->json($arr_to_return);
    }

    protected function giveRating(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : '0';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $from_id = isset($request['from_id']) ? $request['from_id'] : '0';
        $to_id = isset($request['to_id']) ? $request['to_id'] : '0';
        $rating_ques_id = isset($request['rating_ques_id']) ? $request['rating_ques_id'] : '0';
        $rating = isset($request['rating']) ? $request['rating'] : '0';
        $review = isset($request['review']) ? $request['review'] : '';
        \App::setLocale($locale);
        $arr_rating = array(
            "order_id" => $order_id,
            "to_id" => $to_id,
            "from_id" => $from_id,
            "rating_ques_id" => $rating_ques_id,
            "rating" => $rating,
            "review" => (isset($review)) ? $review : "",
            "status" => "1",
        );

        $created_rating = UserRatingInformation::create($arr_rating);

        if ($created_rating) {
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.rating_successfull', $locale), "data" => $created_rating);
        } else {
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.rating_fail', $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getAllDeliveryuserSliderImages(Request $request) {
        $arr_to_return = array();
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $sliderImages = SliderImage::where('type', 0)->where('locale', $locale)->get();
        $arr_to_return = array("error_code" => 0, "data" => $sliderImages, "image_path" => asset("/storageasset/slider-images/"));
        return response()->json($arr_to_return);
    }

    public function getStatusMessagesByCategory(Request $request) {
        $arr_to_return = array();
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $category_id = isset($request['category_id']) ? $request['category_id'] : '';
        \App::setLocale($locale);
        $categoryStatusData = array();
        if ($category_id != '' && $category_id != 0) {
            $statusData = CategoryStatusMsg::where('category_id', $category_id)->where('status', 1)->where('locale', $locale)->get();
            $arr_to_return = array("error_code" => 0, "data" => $statusData);
        } else {
            $categories = Category::where('status', '1')->get();
            if (count($categories) > 0) {
                $j = 0;
                foreach ($categories as $category_data) {
                    $categoryStatusData[$j]['id'] = $category_data->id;
                    $categoryStatusData[$j]['name'] = $category_data->name;
                    $statusData = CategoryStatusMsg::where('category_id', $category_data->id)->where('status', 1)->where('locale', $locale)->get();
                    if (count($statusData) > 0) {
                        $k = 0;
                        foreach ($statusData as $status_data) {

                            $categoryStatusData[$j]['status_data'][$k] = $status_data;
                            $k++;
                        }
                    }

                    $j++;
                }
            }
            $arr_to_return = array("error_code" => 0, "data" => $categoryStatusData);
        }

        return response()->json($arr_to_return);
    }

    public function getAllCustomerSliderImages(Request $request) {
        $arr_to_return = array();

        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $sliderImages = SliderImage::where('type', 1)->where('locale', $locale)->get();
        $arr_to_return = array("error_code" => 0, "data" => $sliderImages, "image_path" => asset("/storageasset/slider-images/"));
        return response()->json($arr_to_return);
    }

    public function getServiceName(Request $request) {
        $arr_to_return = array();
        $seviceData = array();
        $user_id = isset($request['user_id']) ? $request['user_id'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);

        $service_details = UserServiceInformation::where('user_id', $user_id)->get();

        if ($service_details) {
            foreach ($service_details as $key => $service) {
                if ($service->service_id != 15 && $service->service_id != 17 && $service->service_id != 32 && $service->serviceInfo->categoryInfo->status == '1') {
                    $seviceData[$key]["id"] = $service->id;
                    $seviceData[$key]["service_id"] = $service->userService->service_id;
                    // $seviceData[$key]["name"] = isset($service->serviceInfo->categoryInfo->name)?($service->serviceInfo->categoryInfo->name."- ".$service->userService->name):'';
                    $seviceData[$key]["name"] = isset($service->serviceInfo->categoryInfo->name) ? ($service->serviceInfo->categoryInfo->name . "- " . $service->userServiceName->name) : '';
                    $seviceData[$key]["goe_fence_area"] = $service->goe_fence_area;
                    $seviceData[$key]["max_value"] = isset($service->serviceInfo->max_range) ? ($service->serviceInfo->max_range) : '';
                }
            }
        }
        $seviceData = array_values($seviceData);
        $arr_to_return = array("error_code" => 0, "data" => $seviceData);
        return response()->json($arr_to_return);
    }

    public function updateServiceName(Request $request) {
        $arr_to_return = array();
        $updatedSevice = array();
        $user_service_id = isset($request['user_service_id']) ? $request['user_service_id'] : '0';
        $goe_fence_area = isset($request['goe_fence_area']) ? $request['goe_fence_area'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);

        $service_details = UserServiceInformation::where('id', $user_service_id)->first();


        $service_details->goe_fence_area = $goe_fence_area;

        $service_details->save();


        if ($service_details) {

            $updatedSevice["id"] = $service_details->id;
            $updatedSevice["service_id"] = $service_details->service_id;
            $updatedSevice["user_id"] = $service_details->user_id;
            $updatedSevice["vehicle_id"] = $service_details->vehicle_id;
            $updatedSevice["status"] = $service_details->status;
            $updatedSevice["goe_fence_area"] = $service_details->goe_fence_area;
        }

        $arr_to_return = array("error_code" => 0, "data" => $updatedSevice);
        return response()->json($arr_to_return);
    }

    //cron job to send notification for all schedule orders
    protected function makeAnScheduleOrder(Request $request) {

        //get all scheduled orders
        $allOrders = Order::where('order_type', 2)->where('status', '0')->get();
        $radious = GlobalValues::get('star-range-radious');
        $schedule_exec_time = GlobalValues::get('schedule_order_exec_time');
        $allOrders = $allOrders->reject(function($schedule_order) {
                    return ($schedule_order->getServicesDetails->category_id == 5);
                })->values();

        $locale = 'en';

        if (count($allOrders) > 0) {
            foreach ($allOrders as $schedule_order) {


                $dt = new DateTime(date('Y-m-d H:i:s'));

                //get timezone as per country
                $countryInfo = Country::where('id', $schedule_order->country_id)->first();
                if (count($countryInfo) > 0) {
                    $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                    $dt->setTimezone($tz);
                }

                $date2_val = $dt->format('Y-m-d H:i:s');
                $date2 = new DateTime($date2_val);
                $date1 = new DateTime($schedule_order->order_place_date_time);

                $diffdate = date_diff($date1, $date2);

                if ((($diffdate->h) == 0) && ($diffdate->i <= $schedule_exec_time)) {

                    if ($schedule_order->order_place_date_time != '') {

                        $schedule_order->is_cron_execute = 1;
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
                            $service_details_radious = Service::where('id', $service_id)->first();
                            if (isset($service_details_radious->categoryInfo->request_range) && (($service_details_radious->categoryInfo->request_range) > 0)) {
                                $radious = $service_details_radious->categoryInfo->request_range;
                            }
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
                                        $arrayUserIds = array();
                                        foreach ($arrServiceUsers as $users_ids) {
                                            if (isset($users_ids->user_id) && $users_ids->user_id != 0 && $distance <= $users_ids->goe_fence_area) {
                                                $user_ids .= ",$users_ids->user_id";
                                                $arrayUserIds[] = $users_ids->user_id;
                                            }
                                        }

                                        //
                                        $users = DB::select("call getUserByDistance(" . $pick_up_lat . "," . $pick_up_long . ",'" . $user_ids . "'," . $radious . ")");

                                        //check if a user is having any active orders

                                        if (count($users) > 0) {
                                            $j = 0;
                                            foreach ($users as $user) {
                                                if (in_array($user->user_id, $arrayUserIds)) {
                                                    $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();

                                                    if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                                        $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                                        //check if user has notification of any orders
                                                        $order_notification_count = OrderNotification::where('user_id', $user->user_id)->first();
                                                        $payment_method_id = isset($schedule_order->payment_type) ? $schedule_order->payment_type : '0';
                                                        $paymentMethods = UserPaymentMethod::where('user_id', $user->user_id)->where('payment_method_id', $payment_method_id)->first();
                                                        $order_cancel = OrderCancelationDetail::where('order_id', $schedule_order->id)->where('user_id', $user->user_id)->first();

                                                        if (count($userData) <= 0 && count($order_notification_count) <= 0 && count($order_cancel) <= 0 && count($paymentMethods) > 0) {
                                                            $flag_available = 1;
                                                            $avalale_driver_id = $user->user_id;
                                                            break;
                                                        }
                                                    }
                                                    $j++;
                                                }
                                            }
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
                                                $check_point_distance = 0;
                                                if (isset($arrDataService->check_point_distance) && $arrDataService->check_point_distance > 0) {
                                                    $check_point_distance = $arrDataService->check_point_distance;
                                                }
                                                if ($distance >= $check_point_distance && $check_point_distance > 0) {
                                                    $fare = (double) $arrDataService->flat_price;
                                                } else {
                                                    if ($distance > $arrDataService->base_km) {
                                                        $fare = (double) $arrDataService->base_price;
                                                        $fare = $fare + (double) ($distance - $arrDataService->base_km) * ($arrDataService->price_per_km);

                                                        //                                            $fare = (double) ($distance) * ($arrDataService->price_per_km);
                                                        //                                             $fare=$fare + (double)$arrDataService->base_price;
                                                    } else {
                                                        $fare = (double) $arrDataService->base_price;
                                                    }
                                                }
                                            }
                                            $arr_to_return = array("error_code" => 0, "fare" => number_format($fare, 3));
                                        }

                                        if ($payment_type == 2) {
                                            //check wallter amount
                                            $userWalletDetails = UserWalletDetail::where('user_id', $user_id)->orderBy('id', 'desc')->first();
                                            if (isset($userWalletDetails) && count($userWalletDetails)) {
                                                $wallet_amount = $userWalletDetails->final_amout;
                                            } else {
                                                $wallet_amount = 0;
                                            }
                                            $final_remaining_wallter_amount = 0;
                                            //get all actve pending order of this user by using wallte payment
                                            $arrStatus = array("0", "1");
                                            $order_amt_total = Order::where('mate_id', $user_id)->whereIn('status', $arrStatus)->sum('fare_amount');
                                            $final_remaining_wallter_amount = $wallet_amount - $order_amt_total;
                                            if ($fare > $final_remaining_wallter_amount) {
                                                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.wallet_insufficient', $locale));
                                                return response()->json($arr_to_return);
                                            }
                                        }


                                        //sending push notification to star user which is nearest available
                                        if ($avalale_driver_id != '') {
                                            $countryInfo = Country::where('id', $country_id)->first();
                                            $dt = new DateTime(date('Y-m-d H:i:s'));
                                            if (count($countryInfo) > 0) {
                                                $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                                $dt->setTimezone($tz);
                                            }

                                            $date2_val = $dt->format('Y-m-d H:i:s');
                                            $date2 = new DateTime($date2_val);
                                            $notification_start_time = $date2;
                                            $notification_end_time = date("Y-m-d H:i:s", strtotime($date2) . ' + ' . GlobalValues::get('star-reject-time') . ' min');
                                            //storing that user in notification table.
                                            $arrOrderNotificationDetails['order_id'] = $schedule_order->id;
                                            $arrOrderNotificationDetails['user_id'] = $avalale_driver_id;
                                            $arrOrderNotificationDetails['created_at'] = $date2;
                                            $arrOrderNotificationDetails['updated_at'] = $date2;
                                            $arrOrderNotificationDetails['message'] = $order_number . " " . Lang::choice('messages.order_assigned', $locale);
                                            OrderNotification::create($arrOrderNotificationDetails);

                                            $order_assigned_status = array();
                                            $order_assigned_status['order_id'] = $schedule_order->id;
                                            $order_assigned_status['user_id'] = $avalale_driver_id;
                                            $order_assigned_status['reason_text'] = "Assigned to this user";
                                            //storing cancel reason
                                            OrderAssignedDetail::create($order_assigned_status);
                                            $avalale_star_details = UserInformation::where('user_id', $avalale_driver_id)->first();
                                            $obj_send_push_notification = new SendPushNotification();
                                            if (isset($avalale_star_details->device_id) && $avalale_star_details->device_id != '') {
                                                $arr_push_message = array("sound" => "default", "title" => "BAGGI", "text" => Lang::choice('messages.order_assign_star', $locale), "flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_star', $locale), 'order_id' => $schedule_order->id, 'notification_start_time' => $notification_start_time, 'notification_end_time' => $notification_end_time);
                                                $arr_push_message_ios = array();
                                                if ($avalale_star_details->device_type == '0') {
                                                    //sending push notification star user.
                                                    //sending push notification star user.
                                                    $arr_push_message_android = array();
                                                    $arr_push_message_android['to'] = $avalale_star_details->device_id;
                                                    $arr_push_message_android['priority'] = "high";
                                                    $arr_push_message_android['sound'] = "default";
                                                    $arr_push_message_android['notification'] = $arr_push_message;
                                                    $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                                                } else {
                                                    $arr_push_message_ios['to'] = $avalale_star_details->device_id;
                                                    $arr_push_message_ios['priority'] = "high";
                                                    $arr_push_message_ios['sound'] = "default";
                                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                                    $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                                }
                                            }
                                        }

                                        if (count($arr_user_data) > 0) {
                                            //saving
                                            $notiMsg = Lang::choice('messages.order_has_been_scheduled_msg', $locale);
                                            $notiMsg = str_replace("%%ORDER_NUMBER%%", $order_details->order_unique_id, $notiMsg);
                                            $saveNotification = new AppNotification();
                                            $saveNotification->saveNotification($arr_user_data->id, $order_details->id, Lang::choice('messages.order_has_been_scheduled', $locale), $notiMsg, date("Y-m-d"), 0, 'order');
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
                }
            }
        }
    }

    protected function makeAnScheduleOrderMarine(Request $request) {

        //get all scheduled orders
        $allOrders = Order::where('order_type', '2')->where('status', '0')->where('is_cron_execute', '0')->get();

        $radious = GlobalValues::get('star-range-radious');
        $schedule_exec_time = GlobalValues::get('schedule_order_exec_time_marine');
        $locale = 'en';
        $allOrders = $allOrders->reject(function($schedule_order) {
                    return ($schedule_order->getServicesDetails->category_id != 5);
                })->values();


        if (count($allOrders) > 0) {

            foreach ($allOrders as $schedule_order) {

                $dt = new DateTime(date('Y-m-d H:i:s'));

                //get timezone as per country
                $countryInfo = Country::where('id', $schedule_order->country_id)->first();
                if (count($countryInfo) > 0) {
                    $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                    $dt->setTimezone($tz);
                }
                $date2_val = $dt->format('Y-m-d H:i:s');
                $date2 = new DateTime($date2_val);
                $date1 = new DateTime($schedule_order->order_place_date_time);
                $diffdate = date_diff($date2, $date1);

                if ((($diffdate->h) == 0) && (($diffdate->i) <= $schedule_exec_time)) {

                    if (isset($schedule_order->order_place_date_time)) {

                        $schedule_order->is_cron_execute = 1;
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
                                $service_details_radious = Service::where('id', $service_id)->first();
                                if (isset($service_details_radious->categoryInfo->request_range) && (($service_details_radious->categoryInfo->request_range) > 0)) {
                                    $radious = $service_details_radious->categoryInfo->request_range;
                                }
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
                                        $arrayUserIds = array();
                                        foreach ($arrServiceUsers as $users_ids) {
                                            if (isset($users_ids->user_id) && $users_ids->user_id != 0 && $distance <= $users_ids->goe_fence_area) {
                                                $user_ids .= ",$users_ids->user_id";
                                                $arrayUserIds[] = $users_ids->user_id;
                                            }
                                        }
                                        //
                                        $users = DB::select("call getUserByDistance(" . $pick_up_lat . "," . $pick_up_long . ",'" . $user_ids . "'," . $radious . ")");

                                        //check if a user is having any active orders
                                        if (count($users) > 0) {
                                            $j = 0;
                                            foreach ($users as $user) {
                                                if (in_array($user->user_id, $arrayUserIds)) {
                                                    $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                                                    if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                                        $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->first();
                                                        //check if user has notification of any orders

                                                        $order_notification_count = OrderNotification::where('user_id', $user->user_id)->first();

                                                        $payment_method_id = isset($schedule_order->payment_type) ? $schedule_order->payment_type : '0';
                                                        $paymentMethods = UserPaymentMethod::where('user_id', $user->user_id)->where('payment_method_id', $payment_method_id)->first();
                                                        $order_cancel = OrderCancelationDetail::where('order_id', $schedule_order->id)->where('user_id', $user->user_id)->first();
                                                        if (count($userData) <= 0 && count($order_notification_count) <= 0 && count($order_cancel) <= 0 && count($paymentMethods) > 0) {

                                                            $flag_available = 1;
                                                            $avalale_driver_ids[] = $user->user_id;
                                                            if ($service_id != '20' && $service_id != '28') {
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    $j++;
                                                }
                                            }
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
                                                $check_point_distance = 0;
                                                if (isset($arrDataService->check_point_distance) && $arrDataService->check_point_distance > 0) {
                                                    $check_point_distance = $arrDataService->check_point_distance;
                                                }
                                                if ($distance >= $check_point_distance && $check_point_distance > 0) {
                                                    $fare = (double) $arrDataService->flat_price;
                                                } else {
                                                    if ($distance > $arrDataService->base_km) {
                                                        $fare = (double) $arrDataService->base_price;
                                                        $fare = $fare + (double) ($distance - $arrDataService->base_km) * ($arrDataService->price_per_km);

                                                        //                                            $fare = (double) ($distance) * ($arrDataService->price_per_km);
                                                        //                                            $fare=$fare + (double)$arrDataService->base_price;
                                                    } else {
                                                        $fare = (double) $arrDataService->base_price;
                                                    }
                                                }
                                            }
                                        }

                                        //sending push notification to star user which is nearest available
                                        if (count($avalale_driver_ids) > 0) {
                                            foreach ($avalale_driver_ids as $driver_id_counter) {
                                                $countryInfo = Country::where('id', $country_id)->first();
                                                $dt = new DateTime(date('Y-m-d H:i:s'));
                                                if (count($countryInfo) > 0) {
                                                    $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                                    $dt->setTimezone($tz);
                                                }

                                                $date2_val = $dt->format('Y-m-d H:i:s');
                                                $date2 = new DateTime($date2_val);
                                                //storing that user in notification table.
                                                $arrOrderNotificationDetails['order_id'] = $schedule_order->id;
                                                $arrOrderNotificationDetails['user_id'] = $driver_id_counter;
                                                $arrOrderNotificationDetails['created_at'] = $date2;
                                                $arrOrderNotificationDetails['updated_at'] = $date2;
                                                $arrOrderNotificationDetails['message'] = $order_number . " " . Lang::choice('messages.new_request_to_bid', $locale);
                                                OrderNotification::create($arrOrderNotificationDetails);
                                                $order_assigned_status = array();
                                                $order_assigned_status['order_id'] = $schedule_order->id;
                                                $order_assigned_status['user_id'] = $driver_id_counter;
                                                $order_assigned_status['reason_text'] = "Assigned to this user";
                                                //storing cancel reason
                                                OrderAssignedDetail::create($order_assigned_status);
                                                $avalale_star_details = UserInformation::where('user_id', $driver_id_counter)->first();
                                                if (isset($avalale_star_details->device_id) && $avalale_star_details->device_id != '') {
                                                    $arr_push_message = array();
                                                    $arr_push_message_ios = array();
                                                    if ($service_id != '20' && $service_id != '28') {
                                                        $arr_push_message = array("sound" => "default", "title" => "BAGGI", "text" => Lang::choice('messages.order_assign_star', $locale), "flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_star', $locale), 'order_id' => $schedule_order->id);
                                                    } else {

                                                        $arr_push_message = array("sound" => "default", "title" => "BAGGI", "text" => Lang::choice('messages.new_request_to_bid', $locale), "flag" => 'order_quotation_request', 'message' => Lang::choice('messages.new_request_to_bid', $locale), 'order_id' => $schedule_order->id);
                                                    }
                                                    $obj_send_push_notification = new SendPushNotification();
                                                    if ($avalale_star_details->device_type == '0') {
                                                        //sending push notification star user.
                                                        $arr_push_message_android = array();
                                                        $arr_push_message_android['to'] = $avalale_star_details->device_id;
                                                        $arr_push_message_android['priority'] = "high";
                                                        $arr_push_message_android['sound'] = "default";
                                                        $arr_push_message_android['notification'] = $arr_push_message;
                                                        $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                                                    } else {
                                                        $arr_push_message_ios['to'] = $avalale_star_details->device_id;
                                                        $arr_push_message_ios['priority'] = "high";
                                                        $arr_push_message_ios['sound'] = "default";
                                                        $arr_push_message_ios['notification'] = $arr_push_message;
                                                        $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                                    }
                                                }
                                            }
                                        } else {

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
                                                if ($user_details->user->hasRole('superadmin')) {
                                                    return true;
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
                }
            }
        }
    }

    protected function removeNotificationsForActiveOrders(Request $request) {

        $site_email = GlobalValues::get('site-email');

        //get all scheduled orders
        $getAllNotifications = OrderNotification::all();

        if (count($getAllNotifications) > 0) {

            foreach ($getAllNotifications as $order_notification) {
                $orderDetails = $order_notification->orderDetailsInfo;
                if (isset($orderDetails->status) && (($orderDetails->status) > 0)) {
                    $order_notification->delete();
                }
            }
        }
    }

    protected function expiredOrdersCron(Request $request) {
        $order_expired_time = GlobalValues::get('order_expired_time');
        $allOrders = Order::where('status', 0)->get();
        $flag_time = 0;
        if (isset($allOrders) && count($allOrders) > 0) {

            foreach ($allOrders as $expired_orders) {
                $flag = 0;
                $flag_time = 0;
                if ($expired_orders->order_type == '2') {
                    if ($expired_orders->is_cron_execute == '1') {

                        $flag = 1;
                    }
                } else {
                    $flag = 1;
                }

                if ($flag == 1) {
                    $dt = new DateTime(date('Y-m-d H:i:s'));

                    //get timezone as per country
                    $countryInfo = Country::where('id', $expired_orders->country_id)->first();
                    if (isset($countryInfo) && count($countryInfo) > 0) {
                        $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                        $dt->setTimezone($tz);
                    }
                    $date2_val = $dt->format('Y-m-d H:i:s');
                    $date1_val = $expired_orders->order_place_date_time;
                    $date2 = new DateTime($date2_val);
                    $date1 = new DateTime($expired_orders->order_place_date_time);
                    $diffdate = date_diff($date1, $date2);

                    if (strtotime($date2_val) > strtotime($date1_val)) {
                        $flag_time = 1;
                    }

                    if ($flag_time == 1) {
                        if ($expired_orders->order_type == '2') {
                            if ((($diffdate->h) == 0) && ($diffdate->i > $order_expired_time)) {
                                $expired_orders->status = 4;
                                $expired_orders->save();
                            }
                        } else {
                            if ((($diffdate->h) > 0) || ($diffdate->i > $order_expired_time)) {
                                $expired_orders->status = 4;
                                $expired_orders->save();
                            }
                        }
                    }
                }
            }
        }
    }

    protected function getAppFAQS(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        //get all scheduled orders
        \App::setLocale($locale);
        $getAllNotifications = Faq::where('faq_type', '1')->get();
        $arr_to_return = array("error_code" => 0, "data" => $getAllNotifications);
        return response()->json($arr_to_return);
    }

}
