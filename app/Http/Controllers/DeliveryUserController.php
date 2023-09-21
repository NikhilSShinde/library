<?php

namespace App\Http\Controllers;

use App\User;
use App\DriverPendingAmount;
use App\UserInformation;
use App\UserAddress;
use App\DeliveryuserBalanceDetail;
use App\UserEmergencyContactInformation;
use App\MonthlyDriverAmountEvaluation;
use App\UserOtpCodes;
use App\OrderWaitingTimeDetail;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\admin\Models\SubscriptionPlanForDriverDetail;
use App\PiplModules\coupon\Models\UserReferencingReward;
use App\PiplModules\coupon\Models\Coupon;
use App\PiplModules\serviceplan\Models\SubscriptionPlanDetail;
use App\PiplModules\serviceplan\Models\SubscriptionPlan;
use App\PiplModules\admin\Models\CountryServices;
use App\PiplModules\ratingreview\Models\UserRatingInformation;
use App\PiplModules\admin\Models\UserCustomNotification;
use App\PiplModules\orderdetails\Models\OrderFareCalculation;
use App\PiplModules\admin\Models\City;
use App\DriverMobileNumberHistory;
use App\PiplModules\vehicle\Models\DriverAssignedDetail;
use App\PiplModules\driverdocument\Models\Document;
use App\DriverDocumentInformation;
use App\UserSuspendedReason;
use App\PiplModules\orderdetails\Models\MiddleLocation;
use Validator;
use Mail;
use Hash;
use Lang;
use App;
use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use GlobalValues;
use App\PiplModules\wallethistory\Models\UserWalletDetail;
use App\PiplModules\supporttickets\Models\SupportTicket;
use App\PiplModules\admin\Models\SpokenLanguage;
use App\PiplModules\supporttickets\Models\TicketDescription;
use App\PiplModules\orderdetails\Models\Order;
use App\PiplModules\orderdetails\Models\OrdersInformation;
use App\PiplModules\orderdetails\Models\OrderCancelationDetail;
use App\PiplModules\orderdetails\Models\OrderNotification;
use App\Notification;
use App\PiplModules\orderdetails\Models\OrderAssignedDetail;
use App\PiplModules\orderdetails\Models\OrdersTransactionStatus;
use App\PiplModules\orderdetails\Models\UserServiceQuotation;
use App\PiplModules\admin\Models\CountryZoneService;
use App\PiplModules\service\Models\Service;
Use App\DriverUserInformation;
Use App\DriverUserAvailability;
use Storage;
use App\PaymentMethod;
use App\UserPaymentMethod;
use App\UserServiceInformation;
use DB;
use App\CategoryStatusMsg;
use DateTimeZone;
use App\PanaceaClasses\SendSms;
use App\PanaceaClasses\SendPushNotification;
use App\PanaceaClasses\AppNotification;
use App\Http\Controllers\DeliveryController;
use App\PiplModules\orderdetails\Controllers\OrderController;
use App\PiplModules\cron\Controllers\CronController;
use App\PiplModules\finance\Controllers\FinanceController;
use Config;

class DeliveryUserController extends Controller {
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

    /* to show customer user profile */

    public function customerUserProfile(Request $request) {
        $arr_to_return = array();
        $customer_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $customer_app_version = GlobalValues::get('customer-app-version');
        $driver_app_version = GlobalValues::get('driver-app-version');
        $customer_app_ios_version = GlobalValues::get('customer-app-ios-version');
        $driver_app_ios_version = GlobalValues::get('driver-app-ios-version');
        $support_chat_id = 0;
        if ($customer_id != '') {

            $arrCustomerDetails = UserInformation::where('user_id', $customer_id)->where('user_status', '1')->where('user_type', '3')->first();
            if (isset($arrCustomerDetails) && count($arrCustomerDetails) > 0) {
                $mobile_code = str_replace("+", "", $arrCustomerDetails->mobile_code);
                $arrCountryDetails = Country::where('country_code', '+' . str_replace(' ', '', $mobile_code))->first();
            }
//get support chat user
            $userSupportInfo = UserInformation::where('user_type', '8')->first();
            if (count($userSupportInfo) > 0) {
                $support_chat_id = $userSupportInfo->user_id;
            }
            if (count($arrCustomerDetails) <= 0) {
                $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 4, "msg" => Lang::choice('messages.account_has_deleted_invalid_user', "", [], $locale));
            } else if (count($arrCustomerDetails) > 0 && $arrCustomerDetails->user_status == '2') {
                $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 5, "msg" => Lang::choice('messages.account_has_blocked_invalid_user', "", [], $locale));
            } else {
                $arrCustomerDetails->profile_picture = asset("/storageasset/user-images/" . $arrCustomerDetails->profile_picture);

                //get customer rating
                $avg_passenger_rating = 0;
                $userRating = UserRatingInformation::where('to_id', $arrCustomerDetails->user_id)->where('status', '1')->avg('rating');
                $avg_passenger_rating = isset($userRating) ? round($userRating, 2) : '0';
                $arrCustomerDetails->avg_passenger_rating = $avg_passenger_rating;

                if (count($arrCustomerDetails) > 0) {
                    //check for rating_notification
                    $unratedOrderData = Order::where('status', 2)->where('customer_id', $customer_id)->where('customer_rating_notify', '0')->orderBy('id', 'desc')->first();
                    $unratedOrder = array();
                    if (count($unratedOrderData) > 0) {
                        $unratedOrder['order_id'] = $unratedOrderData->id;
                        $unratedOrder['driver_id'] = $unratedOrderData->driver_id;
                        $unratedOrder['order_amount'] = $unratedOrderData->total_amount;
                        $unratedOrder['customer_id'] = $unratedOrderData->customer_id;
                        $unratedOrder['driver_image'] = isset($unratedOrderData->getUserDriverInformation->profile_picture) ? asset("/storageasset/user-images/" . $unratedOrderData->getUserDriverInformation->profile_picture) : '';
                        $unratedOrder['driver_mobile'] = isset($unratedOrderData->getUserDriverInformation->user_mobile) ? $unratedOrderData->getUserDriverInformation->user_mobile : '';
                        $unratedOrder['driver_name'] = isset($unratedOrderData->getUserDriverInformation->first_name) ? $unratedOrderData->getUserDriverInformation->first_name . " " . $unratedOrderData->getUserDriverInformation->last_name : '';

                        $unratedOrder['order_information'] = $unratedOrderData->getOrderTransInformation();

                        $unratedOrderData->customer_rating_notify = 1;
                        $unratedOrderData->save();
                    }
                    $date_campare = date("Y-m-d 00:00:00", strtotime('-15 Days', strtotime(date('Y-m-d'))));
                    $arrAllNotifications = Notification::where("read_status", 0)->where("user_id", $arrCustomerDetails->user_id)->whereDate('notification_date', '>=', $date_campare)->get();
                    $unreadNotification = count($arrAllNotifications);

                    $arr_to_return = array("unreadNotification" => $unreadNotification, "unrated_order" => $unratedOrder, "customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 0, "user" => $arrCustomerDetails->user, "user_informations" => $arrCustomerDetails, "country_informations" => $arrCountryDetails);
                } else {
                    $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
                }
            }
        } else {
            $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function deliveryuserUserProfile(Request $request) {
        $arr_to_return = array();
        $driver_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : '';
        $customer_app_version = GlobalValues::get('customer-app-version');
        $driver_app_version = GlobalValues::get('driver-app-version');
        $customer_app_ios_version = GlobalValues::get('customer-app-ios-version');
        $driver_app_ios_version = GlobalValues::get('driver-app-ios-version');
        $support_chat_id = 0;
        \App::setLocale($locale);
        if ($driver_id != '') {
            $arrUser = UserInformation::where('user_type', 2)->where('user_id', $driver_id)->first();
            if (count($arrUser) <= 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.driver_not_exist', "", [], $locale));
            } else {
                $userSupportInfo = UserInformation::where('user_type', '8')->first();
                if (count($userSupportInfo) > 0) {
                    $support_chat_id = $userSupportInfo->user_id;
                }
                $arrdriverDetails = UserInformation::where('user_id', $driver_id)->where('user_type', '2')->first();
//            if (count($arrdriverDetails) <= 0) {
//                $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 4, "msg" => Lang::choice('messages.account_has_deleted_invalid_user',"",[],$locale));
//            } else if (count($arrdriverDetails) > 0 && $arrdriverDetails->user_status == '2') {
//                $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 5, "msg" => Lang::choice('messages.account_has_blocked_invalid_user',"",[],$locale));
//            } else {
                if (isset($arrdriverDetails->profile_picture)) {
                    $arrdriverDetails->profile_picture = asset("/storageasset/user-images/" . $arrdriverDetails->profile_picture);
                }
                $mobile_code = str_replace("+", "", $arrdriverDetails->mobile_code);
                $all_countries = Country::where('country_code', '+' . $mobile_code)->first();

                if (isset($all_countries->flag_img)) {
                    $arrdriverDetails->flag_image = $all_countries->flag_img;
                } else {
                    $arrdriverDetails->flag_image = "";
                }
                $nationality_name = '';
                if (isset($arrdriverDetails->nationality) && $arrdriverDetails->nationality != 0) {
                    $nationality = App\Nationality::where('id', $arrdriverDetails->nationality)->first();
                    if ($locale == 'ar') {
                        $nationality_name = $nationality->country_name_arabic;
                    } else {
                        $nationality_name = $nationality->country_name;
                    }
                }
                //get taxi type information
                $arrServiceUsers = UserServiceInformation::where('user_id', $arrdriverDetails->user_id)->first();
                if (isset($arrServiceUsers) && count($arrServiceUsers)) {
                    $serviceDetails = DB::table('services')
                            ->join('service_translations', function($join) use($locale) {
                                $join->on('service_translations.service_id', '=', 'services.id');
                                $join->where('service_translations.locale', '=', $locale);
                            })
                            ->where('services.id', $arrServiceUsers->service_id)
                            ->first();
                    $arrdriverDetails->service_name = $serviceDetails->name;
                    $arrdriverDetails->service_id = $arrServiceUsers->service_id;
                } else {
                    $arrdriverDetails->service_name = '';
                    $arrdriverDetails->service_id = 0;
                }
                //get driver rating
                $avg_drivclearer_rating = 0;
                $userRating = UserRatingInformation::where('to_id', $arrdriverDetails->user_id)->where('status', '1')->avg('rating');
                $arrdriverDetails->avg_driver_rating = isset($userRating) ? round($userRating, 2) : '0';

                $mobile_code = str_replace("+", "", $arrdriverDetails->mobile_code);
                $arrdriverDetails->mobile_code = "+" . $mobile_code;

                $arrUserAddress = UserAddress::with("countryinfo")->with("stateInfo")->with("cityInfo")->where("user_id", $driver_id)->first();
                if (count($arrdriverDetails) > 0) {
                    $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 0, "nationality_name" => $nationality_name, "user" => $arrdriverDetails->user, "user_informations" => $arrdriverDetails, "addressInformation" => $arrUserAddress);
                } else {
                    $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 1, "msg" => Lang::choice('messages.driver_profile_not_found', "", [], $locale));
                }
            }
        } else {
            $arr_to_return = array("customer_app_ios_version" => $customer_app_ios_version, "driver_app_ios_version" => $driver_app_ios_version, "customer_app_version" => $customer_app_version, "driver_app_version" => $driver_app_version, "support_id" => $support_chat_id, "error_code" => 1, "msg" => Lang::choice('messages.driver_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function updateCustomerUser(Request $request) {
        $arr_to_return = array();
        $customer_id = isset($request['user_id']) ? $request['user_id'] : '';
        $email = isset($request['email']) ? $request['email'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (isset($customer_id) && $customer_id > 0) {
            $arrCustomerDetails = User::find($customer_id);
            if (count($arrCustomerDetails) > 0) {

//                if (isset($request["email"])) {
//                    $arrCustomerDetails->email = $request["email"];
//                    $arrCustomerDetails->save();
//                }

                if (isset($request["first_name"])) {
                    $arrCustomerDetails->userInformation->first_name = $request["first_name"];
                }
                if (isset($request["last_name"])) {
                    $arrCustomerDetails->userInformation->last_name = $request["last_name"];
                }
                if (isset($request["birth_date"])) {
                    $arrCustomerDetails->userInformation->user_birth_date = $request["birth_date"];
                }
//                if (isset($request["profile_picture"])) {
//                    $extension = $request->file('profile_picture')->getClientOriginalExtension();
//                    if ($extension == '') {
//                        $extension = "png";
//                    }
//                    $new_file_name = time() . "." . $extension;
//                    Storage::put('public/user-images/' . $new_file_name, file_get_contents($request->file('profile_picture')->getRealPath()));
//                    $path = realpath(dirname(__FILE__) . '/../../../');
//                    $old_file = $path . '/storage/app/public/user-images/' . $new_file_name;
//                    $new_file = $path . '/storage/app/public/user-images/thumbs/' . $new_file_name;
//                    $command = "convert " . $old_file . " -resize 300x200^ " . $new_file;
//                    exec($command);
//                    $arrCustomerDetails->userInformation->profile_picture = $new_file_name;
//                }
                $arrCustomerDetails->userInformation->save();
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_profile_update', "", [], $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function updateProfilePicture(Request $request) {
        $arr_to_return = array();
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $customer_id = isset($request['user_id']) ? $request['user_id'] : '';
        \App::setLocale($locale);

        if (isset($customer_id) && $customer_id > 0) {
            $arrCustomerDetails = User::find($customer_id);
            if (count($arrCustomerDetails) > 0) {
                if (isset($request["profile_picture"])) {

                    if ($arrCustomerDetails->userInformation->profile_picture) {
                        // delete previous file
                        $this->removeProfilePictureFromStrorage($arrCustomerDetails->userInformation->profile_picture);
                    }

                    $extension = $request->file('profile_picture')->getClientOriginalExtension();
                    if ($extension == '') {
                        $extension = "png";
                    }
                    $new_file_name = time() . "." . $extension;
                    Storage::put('public/user-images/' . $new_file_name, file_get_contents($request->file('profile_picture')->getRealPath()));
                    $arrCustomerDetails->userInformation->profile_picture = $new_file_name;
                }
                $arrCustomerDetails->userInformation->save();
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.profile_picture', "", [], $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function updateDeliveryuserUser(Request $request) {
        $arr_to_return = array();
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (isset($driver_id) && $driver_id > 0) {
            $arrDriverDetails = User::find($driver_id);
            if (count($arrDriverDetails) > 0) {
                if ($request->file('profile_picture') != '') {
                    $extension = $request->file('profile_picture')->getClientOriginalExtension();
                    if ($extension == '') {
                        $extension = "png";
                    }
                    $new_file_name = time() . "." . $extension;
                    Storage::put('public/user-images/' . $new_file_name, file_get_contents($request->file('profile_picture')->getRealPath()));
                    $arrDriverDetails->userInformation->profile_picture_temp = $new_file_name;
                }
                $arrDriverDetails->userInformation->save();
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
                $arr_keyword_values['FIRST_NAME'] = isset($arrDriverDetails->userInformation->first_name) ? $arrDriverDetails->userInformation->first_name : '';
                $arr_keyword_values['LAST_NAME'] = isset($arrDriverDetails->userInformation->last_name) ? $arrDriverDetails->userInformation->last_name : '';
                $arr_keyword_values['USER_UPDATE_LINK'] = url('admin/update-driver-user/' . $arrDriverDetails->id);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                $email_template_title = "emailtemplate::driver-profile-picture-update-" . $locale;
                $email_template_subject = Lang::choice('messages.profile_picture_for_approval', "", [], $locale);
                if (count($adminusers) > 0) {
                    foreach ($adminusers as $admin) {
                        @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin, $site_email, $site_title) {
                                    if (isset($admin->user->email)) {
                                        $message->to($admin->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                    }
                                });
                    }
                }

                //Send email to driver's agent
                $arrUserAddress = UserAddress::where('user_id', $driver_id)->first();
                $countryId = "";
                $stateId = "";
                $cityId = "";
                if (isset($arrUserAddress) && count($arrUserAddress)) {
                    $countryId = $arrUserAddress->user_country;
                    $stateId = $arrUserAddress->user_state;
                    $cityId = $arrUserAddress->user_city;
                }

                $arrAggentUserAddressDetails = UserAddress::where('user_country', $countryId)->where('user_state', $stateId)->where('user_city', $cityId)->where('user_id', '<>', $driver_id)->get();

                if (isset($arrAggentUserAddressDetails) && count($arrAggentUserAddressDetails)) {
                    foreach ($arrAggentUserAddressDetails as $agent) {
                        $agentInfoDetails = UserInformation::where('user_id', $agent->user_id)->where('user_type', '4')->first();
                        if (isset($agentInfoDetails) && count($agentInfoDetails)) {
                            $arr_keyword_values = array();
                            $site_email = GlobalValues::get('site-email');
                            $site_title = GlobalValues::get('site-title');
                            $arr_keyword_values['FIRST_NAME'] = isset($arrDriverDetails->userInformation->first_name) ? $arrDriverDetails->userInformation->first_name : '';
                            $arr_keyword_values['LAST_NAME'] = isset($arrDriverDetails->userInformation->last_name) ? $arrDriverDetails->userInformation->last_name : '';
                            $arr_keyword_values['USER_UPDATE_LINK'] = url('admin/update-driver-user/' . $arrDriverDetails->id);
                            $arr_keyword_values['SITE_TITLE'] = $site_title;
                            $email_template_title = "emailtemplate::driver-profile-picture-update-en";
                            $email_template_subject = Lang::choice('messages.profile_picture_for_approval', "", [], $locale);
                            @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $agentInfoDetails, $site_email, $site_title) {
                                        if (isset($agentInfoDetails->user->email)) {
                                            $message->to($agentInfoDetails->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                        }
                                    });
                        }
                    }
                }

                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.driver_profile_update_image', "", [], $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_profile_not_found', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function addCustomerEmergencyContact(Request $request) {
        $arr_to_return = array();
        $customer_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $person_name = isset($request['person_name']) ? $request['person_name'] : '';
        $relation = isset($request['relation']) ? $request['relation'] : '';
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        \App::setLocale($locale);
        if (isset($customer_id) && $customer_id > 0) {

            $arrUserEmergencyDetails = UserEmergencyContactInformation::where("mobile_no", $mobile_no)->where("user_id", $customer_id)->first();
            if (count($arrUserEmergencyDetails) > 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_contact_duplicate', "", [], $locale));
            } else {
                $arrUserDetails = UserInformation::where('user_id', $customer_id)->first();
                if (count($arrUserDetails) > 0) {
                    $arr_userContactInformation["user_id"] = $customer_id;
                    $arr_userContactInformation["person_name"] = $person_name;
                    $arr_userContactInformation["relation"] = $relation;
                    $arr_userContactInformation["mobile_no"] = $mobile_no;
                    $arr_userContactInformation["mobile_code"] = $mobile_code;
                    $arr_userContactInformation["status"] = '1';
                    UserEmergencyContactInformation::create($arr_userContactInformation);
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_contact_added', "", [], $locale));
                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function listCustomerEmergencyContact(Request $request) {
        $arr_to_return = array();
        $customer_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (isset($customer_id) && $customer_id > 0) {

            $arrUserEmergencyDetails = UserEmergencyContactInformation::where("user_id", $customer_id)->get();

            $arr_to_return = array("error_code" => 0, "data" => $arrUserEmergencyDetails);
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
        }

        return response()->json($arr_to_return);
    }

    public function updateCustomerEmergencyContact(Request $request) {
        $arr_to_return = array();
        $contact_id = isset($request['contact_id']) ? $request['contact_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $person_name = isset($request['person_name']) ? $request['person_name'] : '';
        $relation = isset($request['relation']) ? $request['relation'] : '';
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        $arr_userContactInformation = UserEmergencyContactInformation::find($contact_id);
        \App::setLocale($locale);
        if (isset($arr_userContactInformation) && count($arr_userContactInformation) > 0) {

            $arr_userContactInformation->person_name = $person_name;
            $arr_userContactInformation->relation = $relation;
            $arr_userContactInformation->mobile_no = $mobile_no;
            $arr_userContactInformation->mobile_code = $mobile_code;
            $arr_userContactInformation->save();

            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_contact_update', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_contact_not_fount', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function deleteCustomerEmergencyContact(Request $request) {
        $contact_id = isset($request['contact_id']) ? $request['contact_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $arr_userContactInformation = UserEmergencyContactInformation::find($contact_id);
        \App::setLocale($locale);
        if (isset($arr_userContactInformation) && count($arr_userContactInformation) > 0) {

            $arr_userContactInformation->delete();

            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_contact_delete', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_contact_not_fount', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function changeCustomerPassword(Request $request) {
        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
        $current_password = isset($request['current_password']) ? $request['current_password'] : '';
        $new_password = isset($request['new_password']) ? $request['new_password'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($customer_id > 0 && $customer_id != '') {
            $arr_user_data = User::find($customer_id);
            $user_password_chk = Hash::check($current_password, $arr_user_data->password);
            if ($user_password_chk) {
                if ($current_password == $new_password) {
                    $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.same_password', "", [], $locale));
                } else {
                    //updating user Password
                    $arr_user_data->password = $new_password;
                    $arr_user_data->save();
                    if (isset($arr_user_data->email)) {

                        //sending email on password change
                        $arr_keyword_values = array();
                        $site_email = GlobalValues::get('site-email');
                        $site_title = GlobalValues::get('site-title');
                        //Assign values to all macros
                        $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                        $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        $arr_keyword_values['PASSWORD'] = $new_password;
                        // updating activation code                 
                        $email_subject = Lang::choice('messages.password_change', "", [], $locale);
                        $email_template = "emailtemplate::password-change-" . $locale;

//                    @Mail::send($email_template, $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title, $email_subject) {
//
//                        $message->to($arr_user_data->email)->subject($email_subject)->from($site_email, $site_title);
//                    });
                    }
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_change_password', "", [], $locale));
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.current_password_not_match', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function changeCustomerEmail(Request $request) {
        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
        $new_email = isset($request['new_email']) ? $request['new_email'] : '';
        $old_email = isset($request['old_email']) ? $request['old_email'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $arr_user_data = User::where('email', $new_email)->where('id', '!=', $customer_id)->get();
        $arr_user_data_existing = $arr_user_data->filter(function($user) {
            return $user->userInformation->user_type == 3;
        });

        if ($customer_id > 0 && $customer_id != '') {
            $arr_user_data = User::find($customer_id);
            if ($new_email == $old_email) {
                $arr_to_return = array("error_code" => 5, "msg" => Lang::choice('messages.same_email', "", [], $locale));
            } else {
                if (isset($arr_user_data->email) && ($old_email != '') && ($old_email != $arr_user_data->email)) {
                    $arr_to_return = array("error_code" => 4, "msg" => Lang::choice('messages.old_email_does_not_match', "", [], $locale));
                } else {
                    if (count($arr_user_data_existing) == 0) {
                        if (isset($arr_user_data) && $arr_user_data->userInformation->user_type == '3') {
                            $activation_code = $this->generateReferenceNumber();
                            // $arr_user_data->userInformation->temp_email = $new_email;
                            $arr_user_data->email = $new_email;
                            $arr_user_data->save();
                            $arr_user_data->userInformation->activation_code = $activation_code;
                            $arr_user_data->userInformation->save();

                            //sending email on email change
                            $arr_keyword_values = array();

                            $site_email = GlobalValues::get('site-email');
                            $site_title = GlobalValues::get('site-title');
                            //Assign values to all macros
                            $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                            $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                            $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                            $arr_keyword_values['SITE_TITLE'] = $site_title;

                            $email_subject = Lang::choice('messages.email_subject_for_change_email', "", [], $locale);
                            $email_template = "emailtemplate::user-email-change-" . $locale;
                            @Mail::send($email_template, $arr_keyword_values, function ($message) use ($new_email, $site_email, $site_title, $email_subject) {

                                        $message->to($new_email)->subject($email_subject)->from($site_email, $site_title);
                                    });

                            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_email_changed', "", [], $locale));
                        } else {
                            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_not_exist', "", [], $locale));
                        }
                    } else {
                        $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.customer_email_already_exist', "", [], $locale));
                    }
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }

        return response()->json($arr_to_return);
    }

//    public function changeCustomerEmail(Request $request) {
//        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
//        $new_email = isset($request['new_email']) ? $request['new_email'] : '';
//        $old_email = isset($request['old_email']) ? $request['old_email'] : '';
//        $locale = isset($request['locale']) ? $request['locale'] : 'en';
//        $arr_user_data_existing = User::where('email', $new_email)->where('id', '!=', $customer_id)->first();
//
//        if ((!isset($arr_user_data_existing->email) || empty($arr_user_data_existing->email))) {
//            if ($customer_id > 0 && $customer_id != '') {
//                $arr_user_data = User::find($customer_id);
//
//                if (isset($arr_user_data->email) && ($old_email != '') && ($old_email != $arr_user_data->email)) {
//                    $arr_to_return = array("error_code" => 4, "msg" => Lang::choice('messages.old_email_does_not_match',"",[],$locale));
//                } else {
//                    if (isset($arr_user_data) && $arr_user_data->userInformation->user_type == '3') {
//                        $activation_code = $this->generateReferenceNumber();
//                        $arr_user_data->userInformation->temp_email = $new_email;
//                        $arr_user_data->userInformation->activation_code = $activation_code;
//                        $arr_user_data->userInformation->save();
//
//                        //sending email on email change
//                        $arr_keyword_values = array();
//
//                        $site_email = GlobalValues::get('site-email');
//                        $site_title = GlobalValues::get('site-title');
//                        //Assign values to all macros
//                        $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
//                        $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
//                        $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
//                        $arr_keyword_values['SITE_TITLE'] = $site_title;
//                        // updating activation code                 
////                    $arr_user_data->userInformation->activation_code=$activation_code;
////                    $arr_user_data->userInformation->save();   
//                        $email_subject = Lang::choice('messages.email_subject_for_change_email',"",[],$locale);
//                        $email_template = "emailtemplate::user-email-change-" . $locale;
//                        @Mail::send($email_template, $arr_keyword_values, function ($message) use ($new_email, $site_email, $site_title, $email_subject) {
//
//                            $message->to($new_email)->subject($email_subject)->from($site_email, $site_title);
//                        });
//
//                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_email_changed',"",[],$locale));
//                    } else {
//                        $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_not_exist',"",[],$locale));
//                    }
//                }
//            } else {
//                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_invalid',"",[],$locale));
//            }
//        } else {
//            $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.customer_email_already_exist',"",[],$locale));
//        }
//        return response()->json($arr_to_return);
//    }

    protected function sendOtpForChangeMobile(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $rand = rand(1000, 9999);
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $old_mobile_no = isset($request['old_mobile_no']) ? $request['old_mobile_no'] : '';
        $old_mobile_code = isset($request['old_mobile_code']) ? $request['old_mobile_code'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        $type = isset($request['user_type']) ? $request['user_type'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $email = "";
        if ($mobile_no != '') {
            //checking if email or email is already register.
            // $arrUserEmail = User::where("email", $email)->first();
            //$arrUserName = User::where("username", $old_mobile_no)->first();
            $arrUserName = UserInformation::where("user_mobile", $old_mobile_no)->first();
            $old_mobile_code = str_replace("+", "", $old_mobile_code);

            $arrUserNew = UserInformation::where("user_mobile", $mobile_no)->where('user_type', $type)->first();
            if ((count($arrUserName) > 0) && (isset($arrUserName->mobile_code) && ($arrUserName->mobile_code == $old_mobile_code))) {

//                if ($arrUserName->userInformation->user_status == '0') {
//                    $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.user_is_inactive',"",[],$locale));
//                    return response()->json($arr_to_return);
//                    exit;
//                } else 
                if ($arrUserName->user_status == '2') {
                    $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.user_is_blocked', "", [], $locale));
                    return response()->json($arr_to_return);
                }

                if (count($arrUserNew) > 0) {
                    $arr_to_return = array("error_code" => 4, "msg" => Lang::choice('messages.mobile_already_exist', "", [], $locale));
                } else {
                    if ($mobile_code == '') {
                        $mobile_code = isset($arrUserName->mobile_code) ? $arrUserName->mobile_code : '91';
                    }
                    $mobile_code = str_replace("+", "", $mobile_code);
                    $mobile_number_to_send = "+" . $mobile_code . "" . $mobile_no;
                    $message = Lang::choice('messages.otp_for_change_mobile', "", [], $locale);
                    //$message.="Your otp code is ".$rand;
                    $rand_msg = ($rand);
                    if ($locale == 'ar') {
                        //  $rand_msg=strrev($rand);
                    }
                    $message .= " " . $rand_msg;
                    $obj_sms = new SendSms();
                    $res = $obj_sms->sendMessage($mobile_number_to_send, $message);

                    //inserting opt code to tabl
                    $arr_otp['mobile'] = $mobile_no;
                    $arr_otp['otp_code'] = $rand;
                    $arr_otp['mobile_code'] = $mobile_code;
                    $arr_otp['status'] = 1;
                    $arr_otp['otp_for'] = 2;
                    UserOtpCodes::create($arr_otp);
                    if ($email == '' && isset($arrUserName->user->email)) {
                        $email = isset($arrUserName->user->email) ? $arrUserName->user->email : '';
                    }


                    //seding email also if emails is provided   
                    /* if ($email != '' && $email != 'Null' && $email != NULL) {
                      $site_email = GlobalValues::get('site-email');
                      $site_title = GlobalValues::get('site-title');
                      $arr_keyword_values = array();
                      //Assign values to all macros
                      $arr_keyword_values['OTP_CODE'] = $rand;
                      $arr_keyword_values['SITE_TITLE'] = $site_title;
                      $email_subject = Lang::choice('messages.otp_sent_email_subject',"",[],$locale);
                      $tempate_name = "emailtemplate::send-otp-" . $locale;
                      @Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($email, $email_subject, $site_email, $site_title) {
                      $message->to($email)->subject($email_subject)->from($site_email, $site_title);
                      });
                      } */
                    if ($res == "true") {
                        $arr_to_return = array("error_code" => 0, "otp" => $rand, "msg" => Lang::choice('messages.otp_sent_successfully', "", [], $locale));
                    } else {
                        $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_mobile_number', "", [], $locale));
                    }
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.mobile_not_exist', "", [], $locale));
            }
            return response()->json($arr_to_return);
        }
    }

    protected function updateCustomerMobile(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        $otp = isset($request['otp']) ? $request['otp'] : '';
        $otp_for = isset($request['otp_for']) ? $request['otp_for'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : '';
        $arrVerifyOtp = UserOtpCodes::where("mobile", $mobile_no)->where('otp_for', $otp_for)->where('otp_code', $otp)->where("status", '1')->first();
        \App::setLocale($locale);
        if (count($arrVerifyOtp) > 0) {
            $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
            $arrCustomerDetails = User::find($customer_id);
            $mobile_code = str_replace("+", "", trim($mobile_code));
            $arrUserName = UserInformation::where("user_mobile", $mobile_no)->where('user_type', 3)->first();
            if (count($arrUserName) > 0 && ($arrUserName->mobile_code == $mobile_code)) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.mobile_already_exist', "", [], $locale));
            } else {
                if (isset($request["mobile_no"])) {
                    $arrCustomerDetails->username = $mobile_no;
                    $arrCustomerDetails->userInformation->user_mobile = $mobile_no;
                    $arrCustomerDetails->userInformation->mobile_code = $mobile_code;
                }
                $arrCustomerDetails->userInformation->save();
                $arrCustomerDetails->save();
            }
        }

        if (count($arrVerifyOtp) > 0) {
            $arrVerifyOtp->status = 0;
            $arrVerifyOtp->save();
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.mobile_has_been_changed', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.otp_is_not_valid_expired', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function addCustomerWalletBalance(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $wallet_amount = isset($request['wallet_amount']) ? $request['wallet_amount'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);

        $arrWalletAmt = array();
        $arr_to_return = array();
        $userBalance = GlobalValues::userBalance($user_id);
        $arrWalletAmt['user_id'] = $user_id;
        $arrWalletAmt['transaction_amount'] = $wallet_amount;
        $arrWalletAmt['final_amout'] = $wallet_amount;
        $arrWalletAmt['avl_balance'] = $userBalance + $wallet_amount;
        $arrWalletAmt['flag'] = 1;
        $arrWalletAmt['trans_desc'] = Lang::choice('messages.wallet_recharge', "", [], $locale);
        $arrWalletAmt['transaction_type'] = '0';
        $arrWalletAmt['user_id'] = $user_id;
        $customer_wallet_data = UserWalletDetail::create($arrWalletAmt);

        $sql = "SELECT user_id , SUM(COALESCE(CASE WHEN transaction_type = '1' THEN final_amout END,0)) total_debits , SUM(COALESCE(CASE WHEN transaction_type = '0' THEN final_amout END,0)) total_credits , (SUM(COALESCE(CASE WHEN transaction_type = '0' THEN final_amout END,0)) - SUM(COALESCE(CASE WHEN transaction_type = '1' THEN final_amout END,0))) balance FROM " . DB::getTablePrefix() . "user_wallet_details WHERE user_id=" . $user_id . " GROUP BY user_id HAVING balance <> 0";
        $user_wallet_data = DB::select(DB::raw($sql));
        $all_wallet_data = array();

        if (isset($user_wallet_data) && count($user_wallet_data)) {
            $all_wallet_data = (array) $user_wallet_data[0];
        }
        if (isset($customer_wallet_data->id)) {
            $arr_to_return = array("error_code" => 0, "final_amount" => $all_wallet_data, "msg" => Lang::choice('messages.wallet_recharge', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.recharge_fail', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getCustomerWalletBalance(Request $request) {
        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($customer_id > 0 && $customer_id != '') {
            //$customer_wallet_data = UserWalletDetail::where('user_id', $customer_id)->orderBy('id', 'desc')->first(['final_amout']);
            $sql = "SELECT user_id , SUM(COALESCE(CASE WHEN transaction_type = '1' THEN final_amout END,0)) total_debits , SUM(COALESCE(CASE WHEN transaction_type = '0' THEN final_amout END,0)) total_credits , (SUM(COALESCE(CASE WHEN transaction_type = '0' THEN final_amout END,0)) - SUM(COALESCE(CASE WHEN transaction_type = '1' THEN final_amout END,0))) balance FROM " . DB::getTablePrefix() . "user_wallet_details WHERE user_id=" . $customer_id . " GROUP BY user_id HAVING balance <> 0";
            $user_balance = DB::select(DB::raw($sql));
            //dd($user_balance);
            if (isset($user_balance) && count($user_balance) > 0) {
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_wallet_amount', "", [], $locale), "amount" => $user_balance[0]->balance);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_wallet_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getdeliveryuserWalletBalance(Request $request) {
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($driver_id > 0 && $driver_id != '') {

            $driver_wallet_data = DeliveryuserBalanceDetail::where('is_paid', '0')->where('user_id', $driver_id)->orderBy('id', 'desc')->get();
            $incentive_amount_data = DeliveryuserBalanceDetail::where('is_incentive', '1')->where('user_id', $driver_id)->orderBy('id', 'desc')->get();
            $total_driver_amount = 0.00;
            $incentive_amount = 0.00;
            if (count($driver_wallet_data) > 0) {
                $total_driver_amount = $driver_wallet_data->sum('total_amount');
                $driver_wallet_total = $driver_wallet_data->sum('driver_amount');
            }
            if (count($incentive_amount_data) > 0) {
                $incentive_amount = $incentive_amount_data->sum('driver_amount');
            }
            if ($total_driver_amount == '') {
                $total_driver_amount = '0.00';
                $driver_wallet_total = '0.00';
            }
            $total_driver_amount = sprintf('%0.3f', $total_driver_amount);
            $driver_wallet_total = sprintf('%0.3f', $driver_wallet_total);
            if (count($driver_wallet_data) > 0) {
                $arr_to_return = array("error_code" => 0, "incentive_amount" => $incentive_amount, "msg" => Lang::choice('messages.driver_wallet_amount', "", [], $locale), "amount" => ($driver_wallet_total), "total_amount" => $total_driver_amount);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_wallet_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getdeliveryuserTransactionHistory(Request $request) {
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $start_date = isset($request['start_date']) ? $request['start_date'] : '';
        $end_date = isset($request['end_date']) ? $request['end_date'] : '';
        $flag = isset($request['flag']) ? $request['flag'] : '';
        \App::setLocale($locale);
        $wallet_data = array();
        $arr_to_return = array();

        if ($driver_id > 0 && $driver_id != '') {
            $driver_wallet_data = UserWalletDetail::where('user_id', $driver_id)->orderBy('updated_at', 'desc')->get();
            if ($flag == 1) {
                //tadays
                $today_date = date("Y-m-d");
                $driver_wallet_data = $driver_wallet_data->filter(function ($order) use ($today_date) {
                    return date("Y-m-d", strtotime($order->created_at)) == $today_date;
                });
            } elseif ($flag == 2) {
                //weekly
                $monday = strtotime("last monday");
                $monday = date('w', $monday) == date('w') ? $monday + 7 * 86400 : $monday;
                $sunday = strtotime(date("Y-m-d", $monday) . " +6 days");
                $this_week_sd = date("Y-m-d", $monday);
                $this_week_ed = date("Y-m-d", $sunday);
                $driver_wallet_data = $driver_wallet_data->filter(function ($order) use ($this_week_sd, $this_week_ed) {
                    return date("Y-m-d", strtotime($order->created_at)) >= $this_week_sd && date("Y-m-d", strtotime($order->created_at)) <= $this_week_ed;
                });
            } elseif ($flag == 3) {
                //monthly
                $currentMonth = date('m');
                $driver_wallet_data = $driver_wallet_data->filter(function ($order) use ($currentMonth) {
                    return date("m", strtotime($order->created_at)) == $currentMonth;
                });
            } elseif ($flag == 4) {
                //annualy
                $currentYear = date('Y');
                $driver_wallet_data = $driver_wallet_data->filter(function ($order) use ($currentYear) {
                    return date("Y", strtotime($order->created_at)) == $currentYear;
                });
            }
            if ($start_date != '' && $end_date != '') {
                $driver_wallet_data = $driver_wallet_data->filter(function ($order) use ($start_date, $end_date) {
                    return date("Y-m-d", strtotime($order->created_at)) >= $start_date && date("Y-m-d", strtotime($order->created_at)) <= $end_date;
                });
            }
            $driver_wallet_data;
            $i = 0;
            if (isset($driver_wallet_data) && count($driver_wallet_data) > 0) {
                foreach ($driver_wallet_data as $wallet) {
                    $driver_wallet_data[$i]['order_id'] = isset($wallet->OrderDetails->order_unique_id) ? $wallet->OrderDetails->order_unique_id : 0;
                    $driver_wallet_data[$i]['credit'] = isset($wallet->transaction_type) && $wallet->transaction_type == '0' ? $wallet->transaction_amount : 0;
                    $driver_wallet_data[$i]['debit'] = isset($wallet->transaction_type) && $wallet->transaction_type == '1' ? $wallet->transaction_amount : 0;
                    $driver_wallet_data[$i]['balance'] = isset($wallet->avl_balance) && $wallet->avl_balance != '' ? $wallet->avl_balance : 0;
                    $objDeliveryUser = new FinanceController();
                    $user_balance = $objDeliveryUser->calUserWalletInfo($driver_id);
                    if (isset($user_balance) && count($user_balance) > 0) {
                        $driver_wallet_data[$i]['total_balance'] = number_format(round($user_balance['balance'], 2), 3, '.', '');
                        $driver_wallet_data[$i]['total_credit'] = number_format(round($user_balance['total_credits'], 2), 3, '.', '');
                        $driver_wallet_data[$i]['total_debit'] = number_format(round($user_balance['total_debits'], 2), 3, '.', '');
                    }
                    $i++;
                }
            }
            $wallet_data['wallet_data'] = $driver_wallet_data;
            $no_of_rides = $this->getCurrentMonthRides($driver_id);
            $percentage_target_achieve = $this->getPreviousMonthTargetAchieve($driver_id);

            if (count($wallet_data) > 0) {

                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.driver_wallet_amount', "", [], $locale), "data" => end($wallet_data), "no_of_ride" => $no_of_rides, "percentage_target_achieve" => $percentage_target_achieve);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_wallet_history_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getCustomerTransactionHistory(Request $request) {
        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($customer_id > 0 && $customer_id != '') {
            $customer_wallet_data = UserWalletDetail::where('user_id', $customer_id)->orderBy('id', 'created_at')->get();
            if (count($customer_wallet_data) > 0) {
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_wallet_amount', "", [], $locale), "data" => $customer_wallet_data);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_wallet_history_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getUserSupportTicketList(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $flag = isset($request['flag']) ? $request['flag'] : '';
        $country_id = isset($request['country_id']) ? $request['country_id'] : 'en';
        $support_number = "";
        \App::setLocale($locale);
        $support_chat_id = 0;
        if ($user_id > 0 && $user_id != '') {
            $ticketData = array();
            $user_all_SupportTicket = null;

            $user_all_SupportTicket = SupportTicket::where('added_by', $user_id)->orderBy('id', 'desc')->get();
            if (isset($user_all_SupportTicket)) {
                $i = 0;
                foreach ($user_all_SupportTicket as $ticket) {
                    $ticketData[$i] = $ticket;
                    $ticket_coversation = TicketDescription::where('ticket_id', $ticket->id)->orderBy('id', 'desc')->get();
                    $ticketData[$i]['ticket_conversation'] = $ticket_coversation;
                    $ticketData[$i]['unread_count'] = count($ticket_coversation);
                }
            }
            //get support number
            $user_info = UserInformation::where('user_id', $user_id)->first();
            if (isset($user_info)) {
                $support_number = GlobalValues::get('driver_support_number');
                if ($user_info->user_type == 3) {
                    $support_number = GlobalValues::get('passenger_support_number');
                }
            }
            if (isset($user_all_SupportTicket)) {
                $arr_to_return = array("support_id" => $support_chat_id, "error_code" => 0, "msg" => Lang::choice('messages.user_ticket_list', "", [], $locale), "data" => $user_all_SupportTicket, "support_number" => $support_number);
            } else {
                $arr_to_return = array("support_id" => $support_chat_id, "error_code" => 1, "msg" => Lang::choice('messages.user_ticket_not_exist', "", [], $locale), "support_number" => $support_number);
            }
        } else {
            $arr_to_return = array("support_id" => $support_chat_id, "error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function addUserSupportTicket(Request $request) {

        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '';
        $category_id = isset($request['category_id']) ? $request['category_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $subject = isset($request['subject']) ? $request['subject'] : '';
        $description = isset($request['description']) ? $request['description'] : '';
        $country_id = isset($request['country_id']) ? $request['country_id'] : '0';
        $flag = 0;
        $hourdiff = 0;
        $complaint_duration = GlobalValues::get('complaint_duration');
        \App::setLocale($locale);
        if ($user_id != '') {
            if ($order_id != "") {
                $arr_assignedticket = SupportTicket::where("added_by", $user_id)->where("order_id", $order_id)->whereNotIn('status', [2])->first();
                if (isset($arr_assignedticket) && count($arr_assignedticket) > 0) {
                    $flag = 1;
                    $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.ticket_already_assigned', "", [], $locale));
                    return response()->json($arr_to_return);
                }
            }
            if ($flag == 0) {
                $order_details = Order::find($order_id);
                if (isset($order_details) && count($order_details) > 0) {
                    $current_date_time = date('Y-m-d H:i:s');
                    $order_placed_date_time = $order_details->order_place_date_time;
                    $hourdiff = round((strtotime($current_date_time) - strtotime($order_placed_date_time)) / 3600, 1);
                }
                if ($hourdiff > $complaint_duration) {

                    $msg = Lang::choice('messages.ticket_can_assign_only_within_given_hours', "", [], $locale);
                    $msg = str_replace("%%COMPLAINT_DURATION%%", $complaint_duration, $msg);

                    $arr_to_return = array("error_code" => 3, "msg" => $msg);
                    return response()->json($arr_to_return);
                }
                $new_file_name = "";
                $arr_userTicketInformation = array();
                $arr_userTicketInformation["added_by"] = $user_id;
                $arr_userTicketInformation["order_id"] = $order_id;
                $arr_userTicketInformation["category_id"] = $category_id;
                $arr_userTicketInformation["support_subject"] = $subject;
                $arr_userTicketInformation["ticket_unique_id"] = rand();
                //$arr_userTicketInformation["is_read"] = '1';
                $arr_userTicketInformation = SupportTicket::create($arr_userTicketInformation);
                $last_ticket_id = $arr_userTicketInformation->id;

                $arr_userTicketDescription = array();
                $arr_userTicketDescription["ticket_id"] = $last_ticket_id;
                $arr_userTicketDescription["posted_by"] = $user_id;
                $arr_userTicketDescription["description"] = $description;
                $arr_userTicketDescription["is_read"] = '1';
                TicketDescription::create($arr_userTicketDescription);


                //sendimg emails to admin
                $siteAdmin = GlobalValues::get('site-email');
                $adminusers = UserInformation::where('user_type', 1)->get();
                if ($country_id > 0) {
                    $adminusers = $adminusers->reject(function ($user_details) use ($country_id) {
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
                }
                $user_info = UserInformation::where('user_id', $user_id)->first();
                if (!isset($user_info)) {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
                    return response()->json($arr_to_return);
                }
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
                $email_template_subject = Lang::choice('messages.new_ticket_opened', "", [], $locale);
//                if (count($adminusers) > 0) {
//                    foreach ($adminusers as $admin) {
//                        $admin_email = $admin->user->email;
//                        if (isset($admin_email)) {
//                            @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin_email, $site_email, $site_title) {
//                                $message->to($admin_email)->subject($email_template_subject)->from($site_email, $site_title);
//                            });
//                        }
//                    }
//                }

                if ($order_id != "") {
                    $order_details = Order::find($order_id);
                    if (isset($order_details) && count($order_details) > 0) {
                        if ($order_details->customer_id == $user_info->user_id) {
                            //send email to added support ticket user
                            if (isset($order_details->getUserDriverInformation) && count($order_details->getUserDriverInformation) > 0) {
                                $arr_keyword_values['FIRST_NAME'] = $order_details->getUserDriverInformation->first_name;
                                $arr_keyword_values['LAST_NAME'] = $order_details->getUserDriverInformation->last_name;
                                $arr_keyword_values['TICKET_ADDED_LAST_NAME'] = $user_info->last_name;
                                $arr_keyword_values['TICKET_ADDED_LAST_NAME'] = $user_info->last_name;
                                $arr_keyword_values['SUBJECT'] = $subject;
                                $arr_keyword_values['DESCRIPTION'] = $description;
                                $arr_keyword_values['ORDER_ID'] = $order_details->order_unique_id;
                                $arr_keyword_values['SITE_TITLE'] = $site_title;
                                $email_template_title = "emailtemplate::new-ticket-added-againt-ride-" . $locale;
                                $email_template_subject = Lang::choice('messages.new_ticket_opened', "", [], $locale);
                                $driver_email = $order_details->getUserDriverInformation->user->email;
                                if (isset($driver_email)) {
                                    $send = @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title, $driver_email) {
                                                $message->to($driver_email)->subject($email_template_subject)->from($site_email, $site_title);
                                            });
                                }
                            }
                        } else {
                            if (isset($order_details->getUserCustomerInformation) && count($order_details->getUserCustomerInformation) > 0) {
                                $arr_keyword_values['FIRST_NAME'] = $order_details->getUserCustomerInformation->first_name;
                                $arr_keyword_values['LAST_NAME'] = $order_details->getUserCustomerInformation->last_name;
                                $arr_keyword_values['TICKET_ADDED_LAST_NAME'] = $user_info->last_name;
                                $arr_keyword_values['TICKET_ADDED_LAST_NAME'] = $user_info->last_name;
                                $arr_keyword_values['SUBJECT'] = $subject;
                                $arr_keyword_values['DESCRIPTION'] = $description;
                                $arr_keyword_values['ORDER_ID'] = $order_details->order_unique_id;
                                $arr_keyword_values['SITE_TITLE'] = $site_title;
                                $email_template_title = "emailtemplate::new-ticket-added-againt-ride-" . $locale;
                                $email_template_subject = Lang::choice('messages.new_ticket_opened', "", [], $locale);
                                $customer_email = $order_details->getUserCustomerInformation->user->email;
                                if (isset($customer_email)) {
                                    $send = @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title, $customer_email) {
                                                $message->to($customer_email)->subject($email_template_subject)->from($site_email, $site_title);
                                            });
                                }
                            }
                        }


                        //send email to added support ticket user
                        $arr_keyword_values['FIRST_NAME'] = $user_info->first_name;
                        $arr_keyword_values['LAST_NAME'] = $user_info->last_name;
                        $arr_keyword_values['SUBJECT'] = $subject;
                        $arr_keyword_values['DESCRIPTION'] = $description;
                        $arr_keyword_values['ORDER_ID'] = $order_details->order_unique_id;
                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        $email_template_title = "emailtemplate::ticket-added-" . $locale;
                        $email_template_subject = Lang::choice('messages.opened_ticket', "", [], $locale);
                        $user_email = $user_info->user->email;
                        if (isset($user_email)) {
                            @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title, $user_email) {
                                        $message->to($user_email)->subject($email_template_subject)->from($site_email, $site_title);
                                    });
                        }
                    }
                }
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.user_ticket_aded', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getTicketHistory(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $ticket_id = isset($request['ticket_id']) ? $request['ticket_id'] : '';
        \App::setLocale($locale);
        $is_closed = 0;
        if ($ticket_id != '') {
            $arrUserTicketDescription = TicketDescription::where('ticket_id', $ticket_id)->get();
            if (count($arrUserTicketDescription) > 0) {
                foreach ($arrUserTicketDescription as $userTicketDesc) {
                    $userTicketDesc->is_read = 1;
                    $userTicketDesc->save();
                }
            }
            //cloase if $is_closed is 1
            $support_ticket = SupportTicket::where('id', $ticket_id)->first();
            if (count($support_ticket) > 0) {
                if ($support_ticket->status == 2) {
                    $is_closed = 1;
                }
            }
            $arr_to_return = array("error_code" => 0, "is_closed" => $is_closed, "data" => $arrUserTicketDescription, "msg" => Lang::choice('messages.user_ticket_comment_aded', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "is_closed" => $is_closed, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function postCommentOnTicket(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $is_closed = isset($request['is_closed']) ? $request['is_closed'] : '0';
        $description = isset($request['description']) ? $request['description'] : '';
        $ticket_id = isset($request['ticket_id']) ? $request['ticket_id'] : '';
        \App::setLocale($locale);
        if ($user_id > 0 && $user_id != '' && $ticket_id != '') {
            $arr_userTicketDescription = array();
            $arr_userTicketDescription["ticket_id"] = $ticket_id;
            $arr_userTicketDescription["posted_by"] = $user_id;
            $arr_userTicketDescription["description"] = $description;
            //cloase if $is_closed is 1
            $support_ticket = SupportTicket::where('id', $ticket_id)->first();

            if ($is_closed == 1) {
                $support_ticket->status = 2;
                $support_ticket->save();
            } else {
                TicketDescription::create($arr_userTicketDescription);
                if ($support_ticket->status == 0) {
                    $support_ticket->status = 1;
                    $support_ticket->save();
                }
            }
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.user_ticket_comment_aded', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getUserSpokenLanguages(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($user_id > 0 && $user_id != '') {

            $user_all_Spoken_laguages = UserSpokenlanguageinformation::where('user_id', $user_id)->with('languageDetails')->get();
            if (count($user_all_Spoken_laguages) > 0 && isset($user_all_Spoken_laguages)) {
                $arr_to_return = array("error_code" => 0, "data" => $user_all_Spoken_laguages);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.spoken_language_not_found', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getSpokenLanguages(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';

        $user_all_Spoken_laguages = SpokenLanguage::translatedIn($locale)->get();
        if (count($user_all_Spoken_laguages) > 0 && isset($user_all_Spoken_laguages)) {

            $arr_to_return = array("error_code" => 0, "data" => $user_all_Spoken_laguages);
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.language_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function addUserSpokenLanguages(Request $request) {

        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $languages = isset($request['languages']) ? $request['languages'] : '0';
        $language_ids = json_decode($languages);
        \App::setLocale($locale);
        if ($user_id > 0 && $user_id != '') {
            UserSpokenlanguageinformation::where('user_id', $user_id)->delete();
            for ($k = 0; $k < count($language_ids); $k++) {
                $arr_spoken_languages = array();
                $arr_spoken_languages["spoken_language_id"] = $language_ids[$k];
                $arr_spoken_languages["user_id"] = $user_id;
                UserSpokenlanguageinformation::create($arr_spoken_languages);
            }
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.preferred_language', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
    }

    public function sendSosSMS(Request $request) {
        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '';
        $current_long = isset($request['current_long']) ? $request['current_long'] : '';
        \App::setLocale($locale);
        if ($customer_id > 0 && $customer_id != '') {
            $arr_user_data = User::find($customer_id);
            $userEmergencyContacts = UserEmergencyContactInformation::where('user_id', $customer_id)->get();
            if (count($userEmergencyContacts) > 0) {
                foreach ($userEmergencyContacts as $contact) { {
                        $location_link = "http://www.google.com/maps/place/" . $current_lat . "," . $current_long;
                        $msg_emergeny = Lang::choice('messages.emergency_msg', "", [], $locale);
                        $message = $arr_user_data->userInformation->first_name . "  " . $arr_user_data->userInformation->last_name . " " . $msg_emergeny . "     " . $location_link;
                        $mobile_code = str_replace("+", "", $contact->mobile_code);
                        $mobile_no = $contact->mobile_no;
                        $mobile_no = "+" . $mobile_code . "" . $mobile_no;


                        if ($res) {
                            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.send_sos_success', "", [], $locale));
                        } else {
                            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.sms_sent_fail', "", [], $locale));
                        }
                    }
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.send_sos_not_exist', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getCustomerUserActiveOrder(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';

        \App::setLocale($locale);
        $arrOrderDetails = array();
        if ($user_id > 0 && $user_id != '') {
            $arr_orders = Order::where('customer_id', $user_id)->where('status', 1)->orderBy('id', 'desc')->first();
            $city_id = isset($arr_orders->city_id) ? $arr_orders->city_id : '0';
            $cityInfo = City::where('id', $arr_orders->city_id)->first();
            if (isset($arr_orders) && count($arr_orders) > 0) {
                //storing all order details
                $driver_user_details = UserInformation::where('user_id', $arr_orders->driver_id)->first();
                $driveruserDetails['order_id'] = $arr_orders->id;
                $driveruserDetails['status_by_driver'] = $arr_orders->status_by_driver;
                $driveruserDetails['order_details'] = $arr_orders->getOrderTransInformation;
                $driveruserDetails['driver_first_name'] = $driver_user_details->first_name;
                $driveruserDetails['driver_last_name'] = $driver_user_details->last_name;
                $driveruserDetails['driver_mobile'] = "+" . str_replace("+", "", $driver_user_details->mobile_code) . "" . $driver_user_details->user_mobile;
                if (isset($driver_user_details->profile_picture)) {
                    $driveruserDetails['driver_image'] = asset("/storageasset/user-images/" . $driver_user_details->profile_picture);
                } else {
                    $driveruserDetails['driver_image'] = "";
                }
                if (isset($cityInfo->support_number)) {
                    $driveruserDetails['support_number'] = $cityInfo->support_number;
                } else {
                    $driveruserDetails['support_number'] = '';
                }
                $userAddress = UserAddress::where('user_id', $arr_orders->driver_id)->first();
                if (count($userAddress) > 0) {
                    $driveruserDetails['driver_current_lat'] = $userAddress->user_current_latitude;
                    $driveruserDetails['driver_current_long'] = $userAddress->user_current_longtitude;
                } else {
                    $driveruserDetails['driver_current_lat'] = 0;
                    $driveruserDetails['driver_current_long'] = 0;
                }
                //getting avarage rating
                $userRating = UserRatingInformation::where('to_id', $arr_orders->driver_id)->where('status', '1')->avg('rating');
                $driveruserDetails['driver_rating'] = $userRating;

                //getting vehicle inforamtion
                $userDriverDetails = DriverAssignedDetail::where('user_id', $arr_orders->driver_id)->first();
                $vehicleDetails = "";
                if (count($userDriverDetails) > 0) {
                    $vehcile_make = isset($userDriverDetails->vehicleInformation->vehicle_name) ? $userDriverDetails->vehicleInformation->vehicle_name : '';
                    $vehicleDetails = $vehcile_make . "- " . isset($userDriverDetails->vehicleInformation->plate_number) ? $userDriverDetails->vehicleInformation->plate_number : '';
                }
                $driveruserDetails['driver_vehicle'] = $vehicleDetails;
                $driveruserDetails['vehicle_name'] = isset($userDriverDetails->vehicleInformation->vehicle_desc) ? $userDriverDetails->vehicleInformation->vehicle_desc : '';
                if (isset($userDriverDetails->vehicleInformation->vehicle_image) && $userDriverDetails->vehicleInformation->vehicle_image != '') {
                    $driveruserDetails['vehicle_image'] = asset("/storageasset/vehicle-images/" . $userDriverDetails->vehicleInformation->vehicle_image);
                } else {
                    $driveruserDetails['vehicle_image'] = "";
                }
                $driveruserDetails['fare_amount'] = isset($arr_orders->fare_amount) ? $arr_orders->fare_amount : '';
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_listing_success', "", [], $locale), "order_details" => $driveruserDetails);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.current_orders_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getCustomerUserCurrentOrder(Request $request) {
        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $arrOrderDetails = array();
        if ($customer_id > 0 && $customer_id != '') {
            $arr_orders = Order::where('customer_id', $customer_id)->whereIn('status', [1, 0])->get();
            $arr_orders = $arr_orders->sortByDesc('created_at');
            if (isset($arr_orders) && count($arr_orders) > 0) {
                //storing all order details
                $i = 0;
                foreach ($arr_orders as $order) {
                    $arrOrderDetails[$i]['order'] = $order;
                    // getting order details.
                    $arrOrderDetails[$i]['order_details'] = $order->getOrderTransInformation;
                    $arrOrderDetails[$i]['service'] = $order->getServicesDetails->name;
                    if (isset($order->getServicesDetails)) {
                        $service = $order->getServicesDetails;
                        $arrOrderDetails[$i]['category'] = $service->categoryInfo->name;
                        //get status by driver text
                        $catgeoryMsgDetails = CategoryStatusMsg::where('category_id', $service->categoryInfo->id)->where('status_value', $order->status_by_driver)->first();
                        $arrOrderDetails[$i]['status_by_driver_text'] = isset($catgeoryMsgDetails->status_description) ? $catgeoryMsgDetails->status_description : '';
                    }
                    $arrOrderDetails[$i]['driver_first_name'] = "";
                    $arrOrderDetails[$i]['driver_last_name'] = "";
                    if (isset($order->driver_id)) {
                        $driver_user_details = UserInformation::where('user_id', $order->driver_id)->first();
                        $arrOrderDetails[$i]['driver_first_name'] = $driver_user_details->first_name;
                        $arrOrderDetails[$i]['driver_last_name'] = $driver_user_details->last_name;
                        $arrOrderDetails[$i]['driver_mobile'] = "+" . str_replace("+", "", $driver_user_details->mobile_code) . "" . $driver_user_details->user_mobile;
                        if (isset($driver_user_details->profile_picture)) {
                            $arrOrderDetails[$i]['driver_image'] = asset("/storageasset/user-images/" . $driver_user_details->profile_picture);
                        } else {
                            $arrOrderDetails[$i]['driver_image'] = "";
                        }
                        // $arrOrderDetails[$i]['driver_image']=$driver_user_details->user_mobile;
                    }
                    $i++;
                }
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_listing_success', "", [], $locale), "order_details" => $arrOrderDetails);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.current_orders_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getChatUsers(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '0';
        $type = isset($request['type']) ? $request['type'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';


        $chatUsers = array();
        $chatUsersIds = array();

        if ($user_id > 0) {
            $total_count = 0;
            if ($type == 0) {

                $current_orders = Order::where('customer_id', $user_id)->whereIn('status', [1, 2])->get();
                if (count($current_orders) > 0) {
                    $i = 0;
                    foreach ($current_orders as $orders) {
                        if ($orders->status == 1) {
                            // $chatUsers[$i]['user_info']=$orders->driver_id;
                            //get User Information

                            $userInfo = UserInformation::where('user_id', $orders->driver_id)->first(['user_id', 'first_name', 'last_name', 'profile_picture']);
                            if (!(in_array($orders->driver_id, $chatUsersIds))) {
                                $chatUsers[$i]['user_info'] = $userInfo;
                                $i++;
                                $chatUsersIds[] = $orders->driver_id;
                            }
                        } else if ($orders->status == 2 && $orders->order_complete_date_time != '0000-00-00 00:00:00') {
                            $dt = new DateTime(date('Y-m-d H:i:s'));

                            //get timezone as per country
                            $countryInfo = Country::where('id', $orders->country_id)->first();
                            if (count($countryInfo) > 0) {
                                $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                $dt->setTimezone($tz);
                            }
                            $date2_val = $dt->format('Y-m-d H:i:s');
                            $date2 = new DateTime($date2_val);
                            $date1 = new DateTime($orders->order_complete_date_time);
                            $diffdate = date_diff($date1, $date2);


                            if (!(in_array($orders->driver_id, $chatUsersIds))) {

                                $userInfo = UserInformation::where('user_id', $orders->driver_id)->first(['user_id', 'first_name', 'last_name', 'profile_picture']);
                                $chatUsers[$i]['user_info'] = $userInfo;
                                $i++;
                                $chatUsersIds[] = $orders->driver_id;
                            }
                        }
                    }
                }
                $arr_to_return = array("error_code" => 0, "count" => count($chatUsers), "users" => $chatUsers);
            } else {
                $current_orders = Order::where('driver_id', $user_id)->whereIn('status', [1, 2])->get();
                if (count($current_orders) > 0) {
                    $i = 0;
                    foreach ($current_orders as $orders) {
                        if ($orders->status == 1) {
                            if (!(in_array($orders->customer_id, $chatUsersIds))) {
                                $userInfo = UserInformation::where('user_id', $orders->customer_id)->first();
                                $chatUsers[$i]['user_info'] = $userInfo;
                                $i++;
                                $chatUsersIds[] = $orders->customer_id;
                            }
                        } else if ($orders->status == 2 && $orders->order_complete_date_time != '0000-00-00 00:00:00') {
                            $dt = new DateTime(date('Y-m-d H:i:s'));

                            //get timezone as per country
                            $countryInfo = Country::where('id', $orders->country_id)->first();
                            if (count($countryInfo) > 0) {
                                $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                $dt->setTimezone($tz);
                            }

                            $date2_val = $dt->format('Y-m-d H:i:s');
                            $date2 = new DateTime($date2_val);
                            $date1 = new DateTime($orders->order_complete_date_time);
                            $diffdate = date_diff($date1, $date2);

                            if (!(in_array($orders->customer_id, $chatUsersIds))) {
                                $userInfo = UserInformation::where('user_id', $orders->customer_id)->first();
                                $chatUsers[$i]['user_info'] = $userInfo;
                                $i++;
                                $chatUsersIds[] = $orders->customer_id;
                            }
                        }
                    }
                }
                $arr_to_return = array("error_code" => 0, "count" => count($chatUsers), "users" => $chatUsers);
            }
        } else {
            $arr_to_return = array("error_code" => 1, "count" => '0');
        }

        return response()->json($arr_to_return);
    }

    public function getCustomerUserOrderHistory(Request $request) {
        $customer_id = isset($request['customer_id']) ? $request['customer_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);

        if ($customer_id > 0 && $customer_id != '') {
            $arr_orders = Order::where('customer_id', $customer_id)->whereIn('status', [2, 3, 4])->get();
            $arr_orders = $arr_orders->sortByDesc('created_at');
            if (isset($arr_orders) && count($arr_orders) > 0) {
                //storing all order details
                $i = 0;
                foreach ($arr_orders as $order) {
                    $arrOrderDetails[$i]['order'] = $order;
                    // getting order details.
                    $arrOrderDetails[$i]['order_details'] = $order->getOrderTransInformation;
                    $arrOrderDetails[$i]['service'] = $order->getServicesDetails->name;
                    if (isset($order->getServicesDetails)) {
                        $service = $order->getServicesDetails;
                        $arrOrderDetails[$i]['category'] = $service->categoryInfo->name;
                        //get status by driver text
                        $catgeoryMsgDetails = CategoryStatusMsg::where('category_id', $service->categoryInfo->id)->where('status_value', $order->status_by_driver)->first();
                        $arrOrderDetails[$i]['status_by_driver_text'] = isset($catgeoryMsgDetails->status_description) ? $catgeoryMsgDetails->status_description : '';
                    }
                    $customerRating = UserRatingInformation::where('to_id', $order->customer_id)->where('order_id', $order->id)->first();
                    $arrOrderDetails[$i]['order_rating'] = isset($customerRating->rating) ? $customerRating->rating : '0';

                    $order_fare_calculation = OrderFareCalculation::where('order_id', $order->id)->first();
                    $order_fare_details = [
                        'fixed_fees' => isset($order_fare_calculation) ? $order_fare_calculation->fixed_fees : 0.00,
                        'ride_starting_fees' => isset($order_fare_calculation) ? $order_fare_calculation->ride_starting_fees : 0.00,
                        'pre_ride_driving_fees' => isset($order_fare_calculation) ? $order_fare_calculation->pre_ride_driving_fees : 0.00,
                        'pre_ride_waiting_fees' => isset($order_fare_calculation) ? $order_fare_calculation->pre_ride_waiting_fees : 0.00,
                        'ride_driving_rate' => isset($order_fare_calculation) ? $order_fare_calculation->ride_driving_rate : 0.00,
                        'ride_waiting_rate' => isset($order_fare_calculation) ? $order_fare_calculation->ride_waiting_rate : 0.00,
                        'no_show_fee_passenger' => isset($order_fare_calculation) ? $order_fare_calculation->no_show_fee_passenger : 0.00,
                        'no_show_fee_driver' => isset($order_fare_calculation) ? $order_fare_calculation->no_show_fee_driver : 0.00,
                        'cancellation_fee' => isset($order_fare_calculation) ? $order_fare_calculation->cancellation_fee : 0.00,
                        'total_fare_estimation' => isset($order_fare_calculation) ? $order_fare_calculation->total_fare_estimation : 0.00,
                    ];
                    $arrOrderDetails[$i]['fare_details'] = $order_fare_details;

                    $i++;
                }
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_listing_success', "", [], $locale), "order_details" => $arrOrderDetails);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.history_orders_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getdeliveryuserUserOrderHistory(Request $request) {
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($driver_id > 0 && $driver_id != '') {
            $arr_orders = Order::where('driver_id', $driver_id)->whereIn('status', [2, 3, 4, 5, 6, 7])->get();
            $arr_orders = $arr_orders->sortByDesc('created_at');
            $arr_orders = $arr_orders->take(10);
            if (isset($arr_orders) && count($arr_orders) > 0) {
                //storing all order details
                $i = 0;
                foreach ($arr_orders as $order) {
                    $arrOrderDetails[$i]['order'] = $order;
                    // getting order details.
                    $arrOrderDetails[$i]['order_details'] = $order->getOrderTransInformation;
                    $arrOrderDetails[$i]['service'] = $order->getServicesDetails->name;
                    if (isset($order->getServicesDetails)) {
                        $service = $order->getServicesDetails;
                        $arrOrderDetails[$i]['category'] = $service->categoryInfo->name;
                        //get status by driver text
                        $catgeoryMsgDetails = CategoryStatusMsg::where('category_id', $service->categoryInfo->id)->where('status_value', $order->status_by_driver)->first();
                        $arrOrderDetails[$i]['status_by_driver_text'] = isset($catgeoryMsgDetails->status_description) ? $catgeoryMsgDetails->status_description : '';
                    }
                    $driverRating = UserRatingInformation::where('to_id', $order->driver_id)->where('order_id', $order->id)->first();

                    $arrOrderDetails[$i]['order_rating'] = isset($driverRating->rating) ? $driverRating->rating : '0';
                    $i++;
                }
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_listing_success', "", [], $locale), "order_details" => $arrOrderDetails);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.history_orders_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.driver_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getdeliveryuserUserCurrentOrder(Request $request) {
        $user_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $arrOrderDetails = array();
        $arrOrderDetailDesc = array();
        $arr_logout_response = array();
        $device_id = $request['device_id'];
        //dd($locale);
        $userData = DB::table('user_informations')
                ->select('user_id', 'device_id')
                ->where(['user_id' => $user_id, 'device_id' => $device_id])
                ->first();
        if ($userData) {
            $arr_logout_response = array("logout_error_code" => 0, "logout_msg" => Lang::choice('', "", [], $locale));
        } else {
            $arr_logout_response = array("logout_error_code" => 1, "logout_msg" => Lang::choice('messages.you_have_logout_from_other_devices', "", [], $locale));
        }
        if ($user_id > 0 && $user_id != '') {
            $arr_orders = Order::where('driver_id', $user_id)->where('status', '1')->get();

            $arr_assigned_orders = OrderNotification::where('user_id', $user_id)->get();
            $arr_orders = $arr_orders->sortByDesc('created_at');
            if ((count($arr_assigned_orders) > 0) || count($arr_orders) > 0) {
                //storing all order details
                $i = 0;
                if (count($arr_orders)) {
                    foreach ($arr_orders as $order) {
                        if ($order->status_by_driver < 6) {
                            $arrOrderDetails[$i]['order'] = $order;
                            // getting order details.
                            $arrOrderDetails[$i]['order_details'] = $order->getOrderTransInformation;
                            $arrOrderDetails[$i]['service'] = $order->getServicesDetails->name;
                            $service_image = "";
                            if (isset($order->getServicesDetails->service_image)) {
                                $service_image = asset("/storageasset/service-image/" . $order->getServicesDetails->service_image);
                                $arrOrderDetails[$i]['service_car_image'] = $service_image;
                            }
                            $arrOrderDetails[$i]['middle_location'] = $order->getMiddleLocation;
                            $region_info = CountryZoneService::where(['service_id' => $order->service_id, 'zone_id' => $order->zone_id])->first();
                            if (isset($region_info) && count($region_info) > 0) {
                                $arrOrderDetails[$i]['region_details'] = $region_info;
                            }
                            $userDriverDetails = DriverAssignedDetail::where('user_id', $order->driver_id)->first();
                            $vehcile_make = "";
                            $plate_number = "";
                            if (count($userDriverDetails) > 0) {
                                $vehcile_make = isset($userDriverDetails->vehicleInformation->vehicle_name) ? $userDriverDetails->vehicleInformation->vehicle_name : '';
                                $plate_number = isset($userDriverDetails->vehicleInformation->plate_number) ? $userDriverDetails->vehicleInformation->plate_number : '';
                            }
                            $arrOrderDetails[$i]['driver_car_name'] = $vehcile_make;
                            $arrOrderDetails[$i]['driver_car_plate_number'] = $plate_number;
                            $coutnryServices = CountryServices::where('city_id', $order->city_id)->where('service_id', $order->service_id)->first();
                            $arrOrderDetails[$i]['service_fare_details'] = $coutnryServices;
                            //get fare-amount
                            $order_fare_calculation = OrderFareCalculation::where('order_id', $order->id)->first();

                            $arrDataService = CountryZoneService::where('zone_id', $order->zone_id)->first();
                            $order_fare_details = [
                                'fixed_fees' => isset($arrDataService) ? $arrDataService->fixed_fees : 0.00,
                                'ride_starting_fees' => isset($arrDataService) ? $arrDataService->ride_starting_fees : 0.00,
                                'pre_ride_driving_fees' => isset($order_fare_calculation) ? $order_fare_calculation->pre_ride_driving_fees : 0.00,
                                'pre_ride_waiting_fees' => isset($order_fare_calculation) ? $order_fare_calculation->pre_ride_waiting_fees : 0.00,
                                'ride_driving_rate' => isset($order_fare_calculation) ? $order_fare_calculation->ride_driving_rate : 0.00,
                                'ride_waiting_rate' => isset($order_fare_calculation) ? $order_fare_calculation->ride_waiting_rate : 0.00,
                                'discount_amount' => isset($discount_amount) ? $discount_amount : 0.00,
                                'total_fare_estimation' => isset($order_fare_calculation) ? $order_fare_calculation->total_fare_estimation : 0.00,
                            ];
                            $arrOrderDetails[$i]['fare_details'] = $order_fare_details;
                            //get driver details
                            if (isset($order->driver_id)) {
                                $driver_user_details = UserInformation::where('user_id', $order->driver_id)->first();
                                $arrOrderDetailDesc[$i]['driver_first_name'] = $driver_user_details->first_name;
                                $arrOrderDetails[$i]['driver_last_name'] = $driver_user_details->last_name;
                                $arrOrderDetails[$i]['driver_mobile'] = "+" . str_replace("+", "", $driver_user_details->mobile_code) . "" . $driver_user_details->user_mobile;
                                if (isset($driver_user_details->profile_picture)) {
                                    $arrOrderDetails[$i]['driver_image'] = asset("/storageasset/user-images/" . $driver_user_details->profile_picture);
                                } else {
                                    $arrOrderDetails[$i]['driver_image'] = "";
                                }
                            } else {
                                $arrOrderDetails[$i]['driver_first_name'] = "";
                                $arrOrderDetails[$i]['driver_last_name'] = "";
                                $arrOrderDetails[$i]['driver_mobile'] = "";
                                $arrOrderDetails[$i]['driver_image'] = "";
                            }
                            $userAddress = UserAddress::where('user_id', $order->driver_id)->first();
                            if (count($userAddress) > 0) {
                                $arrOrderDetails[$i]['driver_current_lat'] = $userAddress->user_current_latitude;
                                $arrOrderDetails[$i]['driver_current_long'] = $userAddress->user_current_longtitude;
                            } else {
                                $arrOrderDetails[$i]['driver_current_lat'] = 0;
                                $arrOrderDetails[$i]['driver_current_long'] = 0;
                            }
                            $paymentMethod = PaymentMethod::where('id', $order->payment_type)->first();
                            $arrOrderDetails[$i]['payment_method'] = isset($paymentMethod->title) ? $paymentMethod->title : '';


                            if (isset($order->getServicesDetails)) {
                                $service = $order->getServicesDetails;
                                $arrOrderDetails[$i]['category'] = $service->categoryInfo->name;
                                //get status by driver text
                                $catgeoryMsgDetails = CategoryStatusMsg::where('category_id', $service->categoryInfo->id)->where('status_value', $order->status_by_driver)->first();
                                $arrOrderDetails[$i]['status_by_driver_text'] = isset($catgeoryMsgDetails->status_description) ? $catgeoryMsgDetails->status_description : '';
                            }
                            $i++;
                        }
                    }
                }

                //getting driver assigned order
                $driver_reject_time = 2; //mints
                $arr_orders_noti = DB::table('order_notifications')
                        ->join('orders', function($join) use($user_id) {
                            $join->on('order_notifications.order_id', '=', 'orders.id');
                            $join->where('order_notifications.user_id', '=', $user_id);
                        })
                        ->where('orders.status', '=', '0')
                        ->select('orders.*', 'order_notifications.user_id as driver_id', 'order_notifications.created_at as notification_date_time')
                        ->groupBy('order_notifications.order_id')
                        ->get();

                $arr_new_noti_data = [];
                $j = 0;
                $service_type = "0"; //instant service type
                if (isset($arr_orders_noti) && count($arr_orders_noti)) {
                    foreach ($arr_orders_noti as $noti_data) {
                        $zone = CountryZoneService::where('zone_id', $noti_data->zone_id)->where('service_id', $noti_data->service_id)->first();
                        if (isset($zone) && count($zone)) {
                            $driver_reject_time = $zone->accepting_limit / 60; // to convert it into minutes                            
                        }
                        $serviceInfo = Service::where('id', $noti_data->service_id)->first();
                        if (isset($serviceInfo) && count($serviceInfo) > 0) {
                            $service_type = $serviceInfo->service_type;
                        }
                        $dt = new DateTime(date('Y-m-d H:i:s'));
                        $countryInfo = Country::where('id', $noti_data->country_id)->first();
                        if (isset($countryInfo) && count($countryInfo) > 0) {
                            $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                            $dt->setTimezone($tz);
                        }

                        $date2_val = $dt->format('Y-m-d H:i:s');
                        $date2 = new DateTime($date2_val);
                        $date1 = new DateTime($noti_data->notification_date_time);
                        $diffdate = date_diff($date1, $date2);

                        if ($diffdate->i < $driver_reject_time) {
                            $isOrderCanceled = OrderCancelationDetail::where(['order_id' => $noti_data->id, 'user_id' => $user_id])->first();

                            if (!isset($isOrderCanceled)) {
                                $order_inforamtion = OrdersInformation::where('order_id', '=', $noti_data->id)->first();
                                $arr_new_noti_data[$j] = [
                                    'service' => $service_type,
                                    'accepting_limit' => $driver_reject_time * 60, // mobile team need it in sec     
                                    'order_detail' => $noti_data,
                                    'order_info_detail' => $order_inforamtion
                                ];
                                $j++;
                            }
                        }
                    }
                }
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_listing_success', "", [], $locale), "order_details" => $arrOrderDetails, "assigned_orders" => $arr_new_noti_data, "arrOrderDetailDesc" => $arrOrderDetailDesc, "logout_data" => $arr_logout_response);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.current_orders_not_exist', "", [], $locale), "logout_data" => $arr_logout_response);
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.driver_invalid', "", [], $locale), "logout_data" => $arr_logout_response);
        }
        return response()->json($arr_to_return);
    }

    public function getdeliveryuserUserAssignedOrder(Request $request) {
        $user_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (empty($user_id)) {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.driver_invalid', "", [], $locale));
            return response()->json($arr_to_return);
        }
        //Check driver have active order or not.
        $driverActiveOrder = DB::table('orders')
                ->where(['status' => '1', 'driver_id' => $user_id])
                ->first();
        if (isset($driverActiveOrder)) {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.current_orders_not_exist', "", [], $locale));
            return response()->json($arr_to_return);
        }
        $driver_reject_time = 2; //miniutes

        $arr_orders_noti = DB::table('order_notifications')
                ->join('orders', function($join) {
                    $join->on('order_notifications.order_id', '=', 'orders.id');
                    $join->where('orders.status', '=', '0');
                })
                ->where('order_notifications.user_id', '=', $user_id)
                ->select('orders.*', 'order_notifications.user_id as driver_id', 'order_notifications.created_at as notification_date_time')
                ->groupBy('order_notifications.order_id')
                ->get();

        $arr_new_noti_data = [];
        $i = 0;
        if (!isset($arr_orders_noti)) {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.current_orders_not_exist', "", [], $locale));
            return response()->json($arr_to_return);
        }
        foreach ($arr_orders_noti as $noti_data) {
            $zone = CountryZoneService::where('zone_id', $noti_data->zone_id)->where('service_id', $noti_data->service_id)->first();
            if (isset($zone) && count($zone)) {
                $driver_reject_time = $zone->accepting_limit / 60; // to make it in minutes
            }
            $countryInfo = Country::where('id', $noti_data->country_id)->first();
            $dt = new DateTime(date('Y-m-d H:i:s'));
            if (isset($countryInfo) && count($countryInfo) > 0) {
                $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                $dt->setTimezone($tz);
            }

            $date2_val = $dt->format('Y-m-d H:i:s');
            $date2 = new DateTime($date2_val);
            $date1 = new DateTime($noti_data->notification_date_time);
            $diffdate = date_diff($date1, $date2);

            if ($diffdate->i < $driver_reject_time) {
                $isOrderCanceled = OrderCancelationDetail::where(['order_id' => $noti_data->id, 'user_id' => $user_id])->first();

                if (!isset($isOrderCanceled)) {
                    $order_inforamtion = OrdersInformation::where('order_id', '=', $noti_data->id)->first();
                    $arr_new_noti_data[$i] = [
                        'accepting_limit' => $driver_reject_time * 60,
                        'order_detail' => $noti_data,
                        'order_info_detail' => $order_inforamtion
                    ];
                    $i++;
                }
            }
        }
        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_listing_success', "", [], $locale), "assigned_orders" => $arr_new_noti_data);
        return response()->json($arr_to_return);
    }

    public function listUserAddresses(Request $request) {
        $arr_to_return = array();
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (isset($user_id) && $user_id > 0) {
            $arrUserAddress = UserAddress::where("user_id", $user_id)->orderBy('id', 'desc')->get();
            $arrUserAddress = $arrUserAddress->reject(function($address) {
                        return ($address->address_type == '1');
                    })->values();
            $arr_to_return = array("error_code" => 0, "data" => $arrUserAddress);
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function getAllCustomerNotifications(Request $request) {
        $arr_to_return = array();
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (isset($user_id) && $user_id > 0) {
            $date_campare = date("Y-m-d 00:00:00", strtotime('-15 Days', strtotime(date('Y-m-d'))));
            $arrAllNotifications = Notification::where("user_id", $user_id)->whereDate('notification_date', '>=', $date_campare)->get();
            if (count($arrAllNotifications) > 0) {
                foreach ($arrAllNotifications as $notification) {
                    $notification->read_status = 1;
                    $notification->save();
                }
            }
            $arrAllNotifications = $arrAllNotifications->sortByDesc('id');

            $arrAppNotification = $arrAllNotifications->reject(function($notification) {
                        return ($notification->type != '0');
                    })->values();
            $arrSystemUpdatesNotification = $arrAllNotifications->reject(function($notification) {
                        return ($notification->type != '1');
                    })->values();
            $arrOffersNotification = $arrAllNotifications->reject(function($notification) {
                        return ($notification->type != '2');
                    })->values();
            $arrDriverDocumentNotifications = Notification::where("user_id", $user_id)->get();

            $arr_to_return = array("error_code" => 0, "app_notifications" => $arrAppNotification, "app_system_notifications" => $arrSystemUpdatesNotification, "app_offers_notifications" => $arrOffersNotification);
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_profile_not_found', "", [], $locale));
        }

        return response()->json($arr_to_return);
    }

    public function getOrderDetails(Request $request) {
        $order_id = isset($request['order_id']) ? $request['order_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $email_verified_count = GlobalValues::get('email_verify_alert_count');
        $ticket_status = 0;
        $discount_amount = 0;
        \App::setLocale($locale);
        if ($order_id > 0 && $order_id != '') {
            $arrUserName = UserInformation::where("user_mobile", $user_id)->where('user_type', 2)->first();

            //$order = Order::where('id', $order_id)->where('status', '!=', 6)->first(); 
            $order = Order::where('id', $order_id)->first();
            if (isset($order) && count($order)) {
                //storing all order details
                $arrOrderDetails['order'] = $order;
                // getting order details.
                if (isset($order->getOrderTransInformation->pickup_person_contact_no) && ($order->getOrderTransInformation->pickup_person_contact_no) != "") {
                    $mobile_number = "+";
                    $mobile_number .= $order->getOrderTransInformation->onbehalf_country_code . ' ' . $order->getOrderTransInformation->pickup_person_contact_no;
//                    $mob = "+";
//                    for ($i = 0; $i < strlen($mobile_number); $i++) {
//                        if ($i == 2) {
//                            $mob .= ' ' . $mobile_number[$i];
//                        } else {
//                            $mob .= $mobile_number[$i];
//                        }
//                    }
                    $order->getOrderTransInformation->pickup_person_contact_no = $mobile_number;
                }
                $arrOrderDetails['order_details'] = $order->getOrderTransInformation;

                $arr_assignedticket = SupportTicket::where("added_by", $user_id)->where("order_id", $order_id)->first();
                if (isset($arr_assignedticket) && count($arr_assignedticket) > 0) {
                    $ticket_status = 1;
                }
                $arrOrderDetails['ticket_status'] = $ticket_status;
                $arrOrderDetails['order_details'] = $order->getOrderTransInformation;
                $region_info = CountryZoneService::where(['service_id' => $order->service_id, 'zone_id' => $order->zone_id])->first();
                if (isset($region_info) && count($region_info) > 0) {
                    $arrOrderDetails['region_details'] = $region_info;
                }
                $arrOrderDetails['user_information'] = $order->getUserCustomerInformation;

                $serviceDetails = DB::table('services')
                        ->join('service_translations', function($join) use($locale) {
                            $join->on('service_translations.service_id', '=', 'services.id');
                            $join->where('service_translations.locale', '=', $locale);
                        })
                        ->where('services.id', $order->service_id)
                        ->first();
                $arrOrderDetails['service'] = $serviceDetails->name;
                $service_image = "";
                if (isset($order->getServicesDetails->service_image)) {
                    $service_image = asset("/storageasset/service-image/" . $order->getServicesDetails->service_image);
                    $arrOrderDetails['service_car_image'] = $service_image;
                }

                if (isset($order->getServicesDetails)) {
                    $service = $order->getServicesDetails;
                    $arrOrderDetails['category'] = $service->categoryInfo->name;
                }
                $arrOrderDetails['image_path'] = asset("storageasset/item-images/");
                if (isset($order->getOrderImages)) {
                    $k = 0;
                    foreach ($order->getOrderImages as $images) {
                        $arrOrderDetails['order_images'][$k] = $images;
                        $k++;
                    }
                }
                if (isset($order->getMiddleLocation)) {
                    
                }
                //getting average rating
                $userRating = UserRatingInformation::where('to_id', $order->driver_id)->where('status', '1')->avg('rating');
                $arrOrderDetails['avg_driver_rating'] = isset($userRating) ? round($userRating, 2) : '0';
                //getting driver rating
                $driverRating = UserRatingInformation::where('to_id', $order->driver_id)->where('order_id', $order->id)->first();
                $arrOrderDetails['passenger_to_driver_rating'] = isset($driverRating->rating) ? $driverRating->rating : '0';
                $arrOrderDetails['passenger_to_driver_review'] = isset($driverRating->review) ? $driverRating->review : '';
                //getting vehicle information
                $userDriverDetails = DriverAssignedDetail::where('user_id', $order->driver_id)->first();
                $vehcile_make = "";
                $vehcile_color = "";
                $plate_number = "";
                if (count($userDriverDetails) > 0) {

                    $model_name = DB::table('car_models')
                            ->join('car_model_translations', function ($join) use ($locale) {
                                $join->on('car_model_translations.car_model_id', '=', 'car_models.id');
                                $join->where('car_model_translations.locale', '=', $locale);
                            })
                            ->where('car_models.id', $userDriverDetails->vehicleInformation->vehicle_name)
                            ->first();

                    $model_color = DB::table('car_colors')
                            ->join('car_color_translations', function ($join) use ($locale) {
                                $join->on('car_color_translations.car_color_id', '=', 'car_colors.id');
                                $join->where('car_color_translations.locale', '=', $locale);
                            })
                            ->where('car_colors.id', $userDriverDetails->vehicleInformation->vehicle_color)
                            ->first();
                    $vehcile_make = isset($model_name->name) ? $model_name->name : '';
                    $vehcile_color = isset($model_color->name) ? $model_color->name : '';
                    $plate_number = isset($userDriverDetails->vehicleInformation->plate_number) ? $userDriverDetails->vehicleInformation->plate_number : '';
                }
                $arrOrderDetails['driver_car_model'] = $vehcile_make;
                $arrOrderDetails['driver_car_color'] = $vehcile_color;
                $arrOrderDetails['driver_car_plate_number'] = $plate_number;
                //getting customer rating
                $customerRating = UserRatingInformation::where('to_id', $order->customer_id)->where('order_id', $order->id)->first();
                $arrOrderDetails['driver_to_passenger_rating'] = isset($customerRating->rating) ? $customerRating->rating : '0';
                $arrOrderDetails['driver_to_passenger_review'] = isset($customerRating->review) ? $customerRating->review : '';

                $customerAvgRating = UserRatingInformation::where('to_id', $order->customer_id)->avg('rating');
                $arrOrderDetails['avg_customer_rating'] = isset($customerAvgRating) ? $customerAvgRating : '0';

                $user_order_notification = OrderNotification::where('order_id', $order_id)->where('user_id', $user_id)->first();
                //check is user has notifcaton of order
                if (count($user_order_notification) > 0) {
                    $arrOrderDetails['has_notification'] = "1";
                } else {
                    $arrOrderDetails['has_notification'] = "0";
                }
                //check email is verified or not
                $passenger_completed_order = Order::where('customer_id', $user_id)->where('status', 2)->get();
                $driver_completed_order = Order::where('driver_id', $user_id)->where('status', 2)->get();
                $arrOrderDetails['has_email_verified'] = "0";
                if (($email_verified_count <= count($passenger_completed_order)) || ($email_verified_count <= count($driver_completed_order))) {
                    $arrUserName = UserInformation::where("user_id", $user_id)->first();
                    if (isset($arrUserName) && count($arrUserName) > 0) {
                        if ($arrUserName->activation_code != "") {
                            $arrOrderDetails['has_email_verified'] = "1";
                        }
                    }
                }
                $coutnryServices = CountryServices::where('city_id', $order->city_id)->where('service_id', $order->service_id)->first();
                $arrOrderDetails['service_fare_details'] = $coutnryServices;
                //get fare-amount
                $order_fare_calculation = OrderFareCalculation::where('order_id', $order_id)->first();

                $arrDataService = CountryZoneService::where('zone_id', $order->zone_id)->first();
                $order_fare_details = [
                    'fixed_fees' => isset($arrDataService) ? $arrDataService->fixed_fees : 0.00,
                    'ride_starting_fees' => isset($arrDataService) ? $arrDataService->ride_starting_fees : 0.00,
                    'pre_ride_driving_fees' => isset($order_fare_calculation) ? $order_fare_calculation->pre_ride_driving_fees : 0.00,
                    'pre_ride_waiting_fees' => isset($order_fare_calculation) ? $order_fare_calculation->pre_ride_waiting_fees : 0.00,
                    'ride_driving_rate' => isset($order_fare_calculation) ? $order_fare_calculation->ride_driving_rate : 0.00,
                    'ride_waiting_rate' => isset($order_fare_calculation) ? $order_fare_calculation->ride_waiting_rate : 0.00,
                    'no_show_fee_passenger' => isset($order_fare_calculation) ? $order_fare_calculation->no_show_fee_passenger : 0.00,
                    'no_show_fee_driver' => isset($order_fare_calculation) ? $order_fare_calculation->no_show_fee_driver : 0.00,
                    'cancellation_fee' => isset($order_fare_calculation) ? $order_fare_calculation->cancellation_fee : 0.00,
                    'discount_amount' => isset($discount_amount) ? $discount_amount : 0.00,
                    'total_fare_estimation' => isset($order_fare_calculation) ? $order_fare_calculation->total_fare_estimation : 0.00,
                ];
                $arrOrderDetails['fare_details'] = $order_fare_details;
                //get driver details
                if (isset($order->driver_id)) {
                    $driver_user_details = UserInformation::where('user_id', $order->driver_id)->first();
                    $arrOrderDetails['driver_first_name'] = $driver_user_details->first_name;
                    $arrOrderDetails['driver_last_name'] = $driver_user_details->last_name;
                    $arrOrderDetails['driver_mobile'] = "+" . str_replace("+", "", $driver_user_details->mobile_code) . "" . $driver_user_details->user_mobile;
                    if (isset($driver_user_details->profile_picture)) {
                        $arrOrderDetails ['driver_image'] = asset("/storageasset/user-images/" . $driver_user_details->profile_picture);
                    } else {
                        $arrOrderDetails ['driver_image'] = "";
                    }
                    // $arrOrderDetails[$i]['driver_image']=$driver_user_details->user_mobile;
                } else {
                    $arrOrderDetails['driver_first_name'] = "";
                    $arrOrderDetails['driver_last_name'] = "";
                    $arrOrderDetails['driver_mobile'] = "";
                    $arrOrderDetails['driver_image'] = "";
                }
                $userAddress = UserAddress::where('user_id', $order->driver_id)->first();
                if (count($userAddress) > 0) {
                    $arrOrderDetails['driver_current_lat'] = $userAddress->user_current_latitude;
                    $arrOrderDetails['driver_current_long'] = $userAddress->user_current_longtitude;
                } else {
                    $arrOrderDetails['driver_current_lat'] = 0;
                    $arrOrderDetails['driver_current_long'] = 0;
                }
                $paymentMethod = DB::table('payment_methods')
                        ->join('payment_method_translations', function($join) use($locale) {
                            $join->on('payment_method_translations.payment_method_id', '=', 'payment_methods.id');
                            $join->where('payment_method_translations.locale', '=', $locale);
                        })
                        ->where('payment_methods.id', $order->payment_type)
                        ->first();
                $arrOrderDetails['payment_method'] = isset($paymentMethod->title) ? $paymentMethod->title : '';
                $arr_to_return = array("error_code" => 0, "order_details" => $arrOrderDetails);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_order_link', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.invalid_order_link', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function addUserAddress(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $address = isset($request['address']) ? $request['address'] : '';
        $zipcode = isset($request['zipcode']) ? $request['zipcode'] : '';
        $latitude = isset($request['latitude']) ? $request['latitude'] : '';
        $longitude = isset($request['longitude']) ? $request['longitude'] : '';
        $address_type = isset($request['address_type']) ? $request['address_type'] : '1';
        $address_name = isset($request['address_name']) ? $request['address_name'] : '';
        \App::setLocale($locale);
        if ($user_id > 0 && $user_id != '') {
            $already_address = UserAddress::where('user_id', $user_id)->get();
            $exist_already_address = $already_address->filter(function ($user) use($address) {
                if (strpos(strtolower($user->address), strtolower($address)) !== false) {
                    return "exist";
                }
            });
            $already_address_name = $already_address->filter(function ($user) use($address_name) {
                if (strpos(strtolower($user->address_name), strtolower($address_name)) !== false) {
                    return "exist";
                }
            });
            if (count($exist_already_address) > 0 || count($already_address_name) > 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.already_added_address', "", [], $locale));
            } else {
                $user_address = array("user_id" => $user_id, "address_name" => $address_name, "address" => $address, "zipcode" => $zipcode, "latitude" => $latitude, "longitude" => $longitude, "address_type" => $address_type);
                $address = UserAddress::create($user_address);
                $arr_to_return = array("error_code" => 0, "data" => $address, "msg" => Lang::choice('messages.address_created', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function updateUserAddress(Request $request) {
        //
        $address_id = isset($request['address_id']) ? $request['address_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $address = isset($request['address']) ? $request['address'] : '';
        $zipcode = isset($request['zipcode']) ? $request['zipcode'] : '';
        $latitude = isset($request['latitude']) ? $request['latitude'] : '';
        $longitude = isset($request['longitude']) ? $request['longitude'] : '';
        $address_type = isset($request['address_type']) ? $request['address_type'] : '';
        $address_name = isset($request['address_name']) ? $request['address_name'] : '';
        \App::setLocale($locale);

        if ($address_id > 0 && $address_id != '') {
            $user_address = UserAddress::find($address_id);
            $exist_user_adddres = UserAddress::where('id', $address_id)->where('address', '!=', $address)->where('address_name', '!=', $address_name)->get();
            $exist_already_address = $exist_user_adddres->filter(function ($user) use($address) {
                if (strpos(strtolower($user->address), strtolower($address)) !== false) {
                    return "exist";
                }
            });
            $already_address_name = $exist_user_adddres->filter(function ($user) use($address_name) {
                if (strpos(strtolower($user->address_name), strtolower($address_name)) !== false) {
                    return "exist";
                }
            });
            if (count($exist_already_address) > 0 || count($already_address_name) > 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.already_added_address', "", [], $locale));
            } else {
                $user_address->address = $address;
                $user_address->zipcode = $zipcode;
                $user_address->latitude = $latitude;
                $user_address->longitude = $longitude;
                $user_address->address_type = $address_type;
                $user_address->address_name = $address_name;
                $user_address->save();
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.address_updated', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function deleteUserAddress(Request $request) {
        //
        $address_id = isset($request['address_id']) ? $request['address_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $data = array();
        if ($address_id > 0 && $address_id != '') {
            $user_address = UserAddress::where('id', $address_id)->first();
            $data['address'] = $user_address->address;
            $data['address_name'] = $user_address->address_name;
            $user_address->delete();
            $arr_to_return = array("error_code" => 0, "data" => $data, "msg" => Lang::choice('messages.address_deleted', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function deleteOrder(Request $request) {
        //
        $order_id = isset($request['order_id']) ? $request['order_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $site_title = GlobalValues::get('site-title');
        $cancel_flag = isset($request['cancel_flag']) ? $request['cancel_flag'] : '0';

        $cancellation_time = GlobalValues::get('cancelation_time');
        $cancelation_limit = GlobalValues::get('cancelation_limit');
        $driver_cancel_percentage = GlobalValues::get('driver_cancel_percentage');
        $fare = 0;
        \App::setLocale($locale);
        if ($order_id > 0 && $order_id != '') {

            $order = Order::where('id', $order_id)->first();
            $user_id = $order->customer_id;

            $country_details = Country::where('id', $order->country_id)->first();
            $cancellation_charge = isset($country_details->cancellation_charge) ? $country_details->cancellation_charge : '0';
            $currency_code = isset($country_details->currency_code) ? $country_details->currency_code : '';

            //get cancellintion count
            $cancelled_orders = Order::where('cancelled_by', $user_id)->get();

            /* Calculate distance and duration of return */
            $duration_in_minutes = 0;
            $distance_in_km = 0;
            $distance = 0;

            if ($order->status_by_driver >= 2 && (count($cancelation_limit) > 3)) {
                if ($order->service_id != 20 && $order->service_id != 28) {
//            $order->getOrderTransInformation->pickup_lat;
//            $order->getOrderTransInformation->pickup_long;
                    $path = realpath(dirname(__FILE__) . '/../../../public');
                    $file_name = $path . "/order_tracking/order_distance_" . $order->id;
                    $path = $file_name . ".txt";
                    $i = 0;
                    if (file_exists($path)) {
                        $location_resource = fopen($path, "r");
                        while (!feof($location_resource)) {
                            $line = fgets($location_resource);
                            if ($line != 'NaN' && $line != 'nan' && $line != 0) {
                                $distance = $distance + (double) $line;
                            }
                        }
                    }
                    $distance = (float) $distance;
                    if ($distance == 0 || $distance == '0.00') {
                        $lat1 = $order->getOrderTransInformation->pickup_lat;
                        $lon1 = $order->getOrderTransInformation->pickup_long;
                        $path = realpath(dirname(__FILE__) . '/../../../public');
                        $file_name = $path . "/order_tracking/order_" . $order->id;
                        $file_path = $file_name . ".txt";
                        $lat2 = 0;
                        $lon2 = 0;
                        $file_last_lat_long = escapeshellarg($file_path); // for the security concious (should be everyone!)
                        $last_lat_long = `tail -n 1 $file_last_lat_long`;
                        if (isset($last_lat_long) && $last_lat_long != '') {
                            $arr_last_lat_long = explode(",", $last_lat_long);

                            if (count($arr_last_lat_long) > 0) {
                                $lat2 = $arr_last_lat_long[0];
                                $lon2 = $arr_last_lat_long[1];
                                // $distance= $this->getDistanceBetweenPointsNew($current_lat,$current_long,$last_lat,$last_long,'Km');
                            }
                        }
                        $theta = $lon1 - $lon2;
                        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                        $dist = acos($dist);
                        $dist = rad2deg($dist);
                        $distance = ($dist * 60 * 1.1515) * (1.609344);
                    }

                    $arrDataService = CountryServices::where('country_id', $order->country_id)->where('service_id', $order->service_id)->first();
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

                                //                            $fare=(double)((($distance)*($arrDataService->price_per_km)));
                                //                            $fare=$fare + (double)$arrDataService->base_price;
                            } else {
                                $fare = (double) $arrDataService->base_price;
                            }
                        }
                    }
                    $cancellation_charge = $cancellation_charge + $fare;
                }
            }
            /* end calculate distance and duration of return */

            if ($cancel_flag == 1) {
                //remove amount from wallet
                //get calcellation amount

                if ($cancellation_charge > 0) {
                    if ($order->payment_type == 2 || $order->payment_type == 3) {
                        $user_wallet_amt = UserWalletDetail::where('user_id', $user_id)->orderBy('id', 'desc')->first(['final_amout']);
                        $final_amt = isset($user_wallet_amt->final_amout) ? $user_wallet_amt->final_amout : '0';
                        if ($final_amt < $cancellation_charge) {
                            $final_amt_update = (double) ($final_amt - $cancellation_charge);
                            $arrWalletAmt = array();
                            $arrWalletAmt['user_id'] = $order->customer_id;
                            $arrWalletAmt['order_id'] = $order->id;
                            $arrWalletAmt['transaction_amount'] = $cancellation_charge;
                            $arrWalletAmt['final_amout'] = $final_amt_update;
                            $arrWalletAmt['flag'] = '3';
                            $arrWalletAmt['trans_desc'] = Lang::choice('messages.cancel_order_msg', "", [], $locale);
                            $arrWalletAmt['transaction_type'] = '1';
                            $customer_wallet_data = UserWalletDetail::create($arrWalletAmt);
                        } else {
                            $final_amt_update = (double) ($final_amt - $cancellation_charge);
                            $arrWalletAmt = array();
                            $arrWalletAmt['user_id'] = $order->customer_id;
                            $arrWalletAmt['order_id'] = $order->id;
                            $arrWalletAmt['transaction_amount'] = $cancellation_charge;
                            $arrWalletAmt['final_amout'] = $final_amt_update;
                            $arrWalletAmt['flag'] = '3';
                            $arrWalletAmt['trans_desc'] = Lang::choice('messages.cancel_order_msg', "", [], $locale);
                            $arrWalletAmt['transaction_type'] = '1';
                            $customer_wallet_data = UserWalletDetail::create($arrWalletAmt);
                        }
                    }
                    $order->cancelled_date = date('Y-m-d H:i:s');
                    $order->cancellation_charge = $cancellation_charge;
                    if ($order->status_by_driver == 3) {
                        $order->status_by_driver = 7;
                    } else {
                        $order->status = 3;
                    }
                    if ($cancellation_charge > 0) {
                        $order->cancelled_by = $order->customer_id;
                    }
                    $order->save();
                    //if any driver is assigned to this user so need
                    $driver_id = isset($order->driver_id) ? $order->driver_id : '0';

                    if ($driver_id > 0 && ($order->payment_type == 2 || $order->payment_type == 3)) { // if COD and Wallet percent amount will credit to driver wallet
                        $driver_wallet_data = UserWalletDetail::where('user_id', $driver_id)->orderBy('id', 'desc')->first(['final_amout']);
                        $prev_file_amount = isset($driver_wallet_data->final_amout) ? $driver_wallet_data->final_amout : '0';
                        $commision_driver = (($cancellation_charge) * ($driver_cancel_percentage / 100));
                        $walletAmount = array();
                        $walletAmount['user_id'] = $driver_id;
                        $walletAmount['order_id'] = $order->id;
                        $walletAmount['transaction_amount'] = (($cancellation_charge) * ($driver_cancel_percentage / 100));
                        $walletAmount['final_amout'] = (double) ($prev_file_amount + $commision_driver);
                        $walletAmount['trans_desc'] = Lang::choice('messages.trans_desc', "", [], $locale);
                        $walletAmount['transaction_type'] = 0;
                        $walletAmount['flag'] = '3';
                        $walletAmount['payment_type'] = 2;
                        UserWalletDetail::create($walletAmount);
                        //check for same order
                        $walletAmountDetails = array();
                        $walletAmount['user_id'] = $driver_id;
                        $walletAmount['driver_amount'] = (($cancellation_charge) * ($driver_cancel_percentage / 100));
                        $walletAmount['total_amount'] = (double) ($cancellation_charge);
                        $walletAmount['pay_type'] = '1';
                        $walletAmount['type'] = '1';
                        $walletAmount['order_id'] = $order->id;
                        DeliveryuserBalanceDetail::create($walletAmountDetails);
                    }
                    $site_email = GlobalValues::get('site-email');
                    $site_title = GlobalValues::get('site-title');
                    /* send notification to driver if customer has cancel the order in-between travling status */
                    if ($order->status_by_driver == 3) {
                        $driver_details = User::where('id', $order->driver_id)->first();
                        //sending push notification to driver
                        $cancel_message = Lang::choice('messages.order_cancel', "", [], $locale);
                        $cancel_message = $cancel_message . "" . $order->order_unique_id;
                        $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $cancel_message, "flag" => 'order_cancel', 'message' => $cancel_message, 'order_id' => $order->id);
                        $arr_push_message_ios = array();
                        if (isset($driver_details->userInformation->device_id) && $driver_details->userInformation->device_id != '') {
                            $obj_send_push_notification = new SendPushNotification();
                            if ($driver_details->userInformation->device_type == '0') {

                                //sending push notification driver user.

                                $arr_push_message_android = ["data" => ['body' => $cancel_message, 'title' => $site_title, "flag" => 'order_cancel', 'order_id' => $order->id], 'notification' => ['body' => $cancel_message, 'title' => $site_title]];
                                $obj_send_push_notification->androidPushNotification($arr_push_message_android, $driver_details->userInformation->device_id, $driver_details->userInformation->user_type);
                            } else {
                                $user_type = $driver_details->userInformation->user_type;
                                $arr_push_message_ios['to'] = $driver_details->userInformation->device_id;
                                $arr_push_message_ios['priority'] = "high";
                                $arr_push_message_ios['sound'] = "iOSSound.wav";
                                $arr_push_message_ios['notification'] = $arr_push_message;
                                $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                            }
                        }
                        if (count($driver_details) > 0) {
                            $customerDetails = UserInformation::where('user_id', $user_id)->first();
                            //saving
                            $notiMsg = Lang::choice('messages.order_has_been_canceled_msg', "", [], $locale);
                            $notiMsg = str_replace("%%CUSTOMER_NAME%%", $customerDetails->first_name . " " . $customerDetails->last_name, $notiMsg);
                            $notiMsg = str_replace("%%ORDER_NUMBER%%", $order->order_unique_id, $notiMsg);
                            $saveNotification = new AppNotification();
                            $saveNotification->saveNotification($driver_details->id, $order->id, Lang::choice('messages.order_has_been_cancelled', "", [], $locale), $notiMsg, date("Y-m-d"), 0, 'order');
                        }
                    }

                    /* send notification to driver if customer has cancel the order in-between travling status */
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_has_been_deleted', "", [], $locale));
                }
            } else {

                $created_date_time = $order->picked_up_time;

                $dt = new DateTime(date('Y-m-d H:i:s'));

                //get timezone as per country
                $countryInfo = Country::where('id', $order->country_id)->first();
                if (count($countryInfo) > 0) {
                    $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                    $dt->setTimezone($tz);
                }

                $date1_val = $dt->format('Y-m-d H:i:s');
                $date1 = new DateTime($date1_val);
                $date2 = new DateTime($created_date_time);
                $diffdate = date_diff($date1, $date2);
                $total_minutes = $diffdate->i;

                if (($total_minutes >= $cancellation_time) && ($order->status > 0) && (count($cancelled_orders) > 3)) {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.time_overhead1', "", [], $locale) . "-" . $currency_code . " " . $cancellation_charge . " " . Lang::choice('messages.time_overhead2', "", [], $locale));
                } else {

                    $order->cancelled_date = date('Y-m-d H:i:s');
//                 $order->cancellation_charge=$cancellation_charge;
                    if ($order->status_by_driver == 3) {
                        $order->status_by_driver = 7;
                    } else {

                        $order->status = 3;
                    }
                    $order->is_customer_canceled = 1;
                    $order->cancellation_charge = 0;
                    if ($cancellation_charge > 0) {
                        $order->cancelled_by = $order->customer_id;
                    }

                    //$order->status=3;
                    $order->save();
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_has_been_deleted', "", [], $locale));
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_invalid', "", [], $locale));
        }

        return response()->json($arr_to_return);
    }

    public function setUserAvailability(Request $request) {
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $today_date = date('Y-m-d H:i:s');
        $elapsed = "";
        $availability = isset($request['availability']) ? $request['availability'] : '0';
        \App::setLocale($locale);
        if ($user_id > 0 && $user_id != '' && $availability != '') {
            $flag = 0;
            if ($availability == '0') {
                $order_check = Order::where('driver_id', $user_id)->where('status', '1')->first();
                if (isset($order_check) && count($order_check) > 0) {
                    $flag = 1;
                }
            }
            if ($flag == 0) {
                $userDetails = User::find($user_id);
                if (isset($userDetails) && count($userDetails) > 0) {
                    $user_driver = DriverUserInformation::where('user_id', $user_id)->first();
                    $user_driver->user_id = $user_id;
                    $user_driver->availability = $availability;
                    $user_driver->save();
                    //get difference from last entry
                    $last_diff = DriverUserAvailability::where('user_id', $user_id)->orderBy('id', 'desc')->first();
                    if (isset($last_diff) && count($last_diff) > 0) {
                        $datetime1 = new DateTime($last_diff->availibility_date_time);
                        $datetime2 = new DateTime($today_date);
                        $interval = $datetime1->diff($datetime2);
                        $elapsed = $interval->format('H:i:s');
//                    $elapsed = strtotime($elapsed);
                    }
                    //get mobile code
                    $user_details = UserInformation::where('user_id', $user_id)->first();
                    if (isset($user_details) && count($user_details) > 0) {
                        $countryInfo = Country::where('country_code', $user_details->mobile_code)->first();
                        $dt = new DateTime(date('Y-m-d H:i:s'));
                        if (count($countryInfo) > 0) {
                            $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                            $dt->setTimezone($tz);
                        }

                        $date2_val = $dt->format('Y-m-d H:i:s');
                        $date2 = new DateTime($date2_val);
                    }
                    $driver_user_availability = new DriverUserAvailability();
                    $driver_user_availability->user_id = $user_id;
                    $driver_user_availability->availibility_date_time = $date2;
                    $driver_user_availability->time_difference = $elapsed;
                    $driver_user_availability->status = $availability;
                    $driver_user_availability->save();
                    if ($availability == '0') {
                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.user_availability_updated_offline', "", [], $locale));
                    } else {
                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.user_availability_updated', "", [], $locale));
                    }
                } else {
                    $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_assigned_can_set_availibily', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function deliveryuserAcceptOrder(Request $request) {

        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $current_lat = isset($request['current_lat']) ? $request['current_lat'] : '0';
        $current_lng = isset($request['current_lng']) ? $request['current_lng'] : '0';
        \App::setLocale($locale);
        $user_details = UserInformation::where('user_id', $user_id)->first();
        if ($order_id > 0 && $user_id > 0) {
            $order_details = Order::where('id', $order_id)->first();

            $order_locale = isset($order_details->locale) ? $order_details->locale : 'en';
            $country_id = $order_details->country_id;
            $order_notification = OrderNotification::where('order_id', $order_id)->where('user_id', $user_id)->first();
            if (isset($order_notification) && count($order_notification) > 0) {
                if ($order_details->status == 0) {
                    if (!($order_details->driver_id > 0)) {
                        $order_details->driver_id = $user_id;
                        $order_details->status = 1;
                        $order_details->status_by_driver = 0;
                        $order_details->save();

                        //re calculate the fare esticustomer if drop are was not there
                        if ($current_lat != '' && $current_lat != '0' && $current_lng != '0' && $current_lng != '') {
                            if ($order_details->getOrderTransInformation->drop_area == '') {
                                $distance = $this->getDistanceBetweenPointsNew($current_lat, $current_lng, $order_details->getOrderTransInformation->selected_pickup_lat, $order_details->getOrderTransInformation->selected_pickup_long, 'Km');
                                $distance = (float) $distance;
                                $fare = 0;
                                if ($distance > 0) {
                                    $arrDataService = CountryServices::where('country_id', $order_details->country_id)->where('service_id', $order_details->service_id)->first();

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
                                                } else {
                                                    $fare = (double) $arrDataService->base_price;
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($fare > 0) {
                                    $order_details->fare_amount = $fare;
                                    $order_details->save();
                                }
                            }
                        }
                        //saving this information to status transaction
                        $statusTransaction = array();
                        $statusTransaction['user_id'] = $user_id;
                        $statusTransaction['order_id'] = $order_id;
                        $statusTransaction['transaction_content'] = Lang::choice('messages.order_is_accepted', "", [], $locale);
                        OrdersTransactionStatus::create($statusTransaction);

                        //Reject other order for this driver
                        $otherOrderNoti = OrderNotification::where('order_id', '<>', $order_id)->where('user_id', $user_id)->get();
                        if (isset($otherOrderNoti)) {
                            foreach ($otherOrderNoti as $driverNoti) {
                                $order_cancellation_status = array();
                                $order_cancellation_status['order_id'] = $driverNoti->order_id;
                                $order_cancellation_status['user_id'] = $user_id;
                                $order_cancellation_status['canceled_by'] = '2';
                                $order_cancellation_status['reason_text'] = Lang::choice('messages.order_is_accepted_by_other_driver', "", [], $locale);
                                //storing cancel reason
                                OrderCancelationDetail::create($order_cancellation_status);
                            }
                        }
                        //get notification time
                        $dt = new DateTime(date('Y-m-d H:i:s'));

                        //get timezone as per country
                        $countryInfo = Country::where('id', $order_details->country_id)->first();
                        if (isset($countryInfo) && count($countryInfo) > 0) {
                            $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                            $dt->setTimezone($tz);
                        }

                        $date2_val = $dt->format('Y-m-d H:i:s');


                        /**  12-2-19  * */
                        //OrderNotification::where('user_id', $user_id)->delete();
                        //OrderNotification::where('order_id', $order_id)->delete();
                        //OrderCancelationDetail::where('user_id', $user_id)->delete();
                        //OrderAssignedDetail::where(['user_id' => $user_id])->delete();
                        //sending email to customer about order accepted and details of driver                        
                        $customer_details = User::where('id', $order_details->customer_id)->first();
                        $site_email = GlobalValues::get('site-email');
                        $site_title = GlobalValues::get('site-title');
                        $arr_keyword_values = array();
                        //Assign values to all macros
                        if (isset($customer_details->userInformation->first_name)) {
                            $arr_keyword_values['CUSTOMER_FIRST_NAME'] = $customer_details->userInformation->first_name;
                        }
                        $arr_keyword_values['DRIVER_FIRST_NAME'] = $user_details->first_name;
                        $arr_keyword_values['DRIVER_LAST_NAME'] = $user_details->last_name;
                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        $arr_keyword_values['ORDER_ID'] = $order_id;

                        $mobile_code = str_replace("+", "", $user_details->mobile_code);
                        //$arr_keyword_values['DRIVER_MOBILE'] = "+" . $mobile_code . "" . $user_details->user_mobile;
                        $email_subject = Lang::choice('messages.driver_order_accepted_notify_to_customer', $order_locale);
                        $tempate_name = "emailtemplate::order-accepted-notify-to-customer-driver-details-" . $order_locale;
//                        if (isset($customer_details->email)) {
//                            $customer_email = $customer_details->email;
//                            @Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($customer_email, $email_subject, $site_email, $site_title) {
//                                        $message->to($customer_email)->subject($email_subject)->from($site_email, $site_title);
//                                    });
//                        }
                        if (isset($customer_details->userInformation->user_mobile) && (isset($user_details->mobile_code))) {
                            $mobile_code = str_replace("+", "", $customer_details->userInformation->mobile_code);
                            $mobile_number_to_send = "+" . $mobile_code . "" . $customer_details->userInformation->user_mobile;
                            $message = "";
                            $order_accept_msg_ar = Lang::choice('messages.customer_sms_order_accepted', "", [], $locale);
                            $order_driver_number_msg_ar = Lang::choice('messages.driver_number', "", [], $locale);

                            //sending sms to customer
                            $message = Lang::choice('messages.customer_sms_order_accepted', $order_locale) . " " . $user_details->first_name . " " . $user_details->last_name;
                            $message .= "\n";
                            $message .= Lang::choice('messages.driver_number_msg', $order_locale) . " +" . str_replace("+", "", trim($user_details->mobile_code)) . "" . $user_details->user_mobile . "";

                            $notiMsg = Lang::choice('messages.order_has_been_accepted_msg', $order_locale);
                            $notiMsg = str_replace("%%DRIVER_NAME%%", $user_details->first_name . " " . $user_details->last_name, $notiMsg);
                            $notiMsg = str_replace("%%ORDER_NUMBER%%", $order_details->order_unique_id, $notiMsg);
                            $notiMsg = str_replace("%%DATE_TIME%%", date("Y-m-d H:i:s"), $notiMsg);
                            $saveNotification = new AppNotification();
                            $saveNotification->saveNotification($customer_details->id, $order_details->id, Lang::choice('messages.order_has_been_accepted', $order_locale), $notiMsg, $date2_val, 0, 'order');
                        }

                        //Send SMS to on behalf of user
                        if (($order_details->getOrderTransInformation->pickup_person_contact_no) != "") {

                            //check if on behalf of user have passenger app or not
                            $onBehalfUser = DB::table('user_informations')
                                    ->select('user_id', 'user_type', 'device_id', 'device_type')
                                    ->where('user_mobile', $order_details->getOrderTransInformation->pickup_person_contact_no)
                                    ->first();
                            if (isset($onBehalfUser) && $onBehalfUser->user_type == 3) {
                                $notiMsg = Lang::choice('messages.message_on_behalf_of_order', "", [], $locale);
                                $notiMsg = str_replace("%%PASSENGER%%", $customer_details->userInformation->first_name . " " . $customer_details->userInformation->last_name, $notiMsg);
                                $notiMsg = str_replace("%%DRIVER%%", $user_details->first_name . " " . $user_details->last_name, $notiMsg);

                                $subjctNotiMsg = Lang::choice('messages.subject_on_behalf_of_order', "", [], $locale);
                                $subjctNotiMsg = str_replace("%%SITE_TITLE%%", $site_title, $subjctNotiMsg);
                                $saveNotification = new AppNotification();
                                $saveNotification->saveNotification($onBehalfUser->user_id, $order_details->id, $subjctNotiMsg, $notiMsg, $date2_val, 0, 'track_order');

                                //sending push notification to on behalf of customer
                                $pushNotiMsg = Lang::choice('messages.push_on_behalf_of_order', "", [], $locale);
                                $pushNotiMsg = str_replace("%%SITE_TITLE%%", $site_title, $pushNotiMsg);
                                $arr_push_message_ios = array("sound" => "iOSSound.wav", 'title' => $site_title, "text" => $pushNotiMsg, "flag" => 'track_order', 'message' => $pushNotiMsg, 'order_id' => $order_details->id);
                                $arr_push_message_ios = array();
                                $obj_send_push_notification = new SendPushNotification();
                                if (isset($onBehalfUser->device_id) && $onBehalfUser->device_id != '') {
                                    if ($onBehalfUser->device_type == '0') {
                                        //sending push notification customer user.                                
                                        $arr_push_message_android = [];
                                        $arr_push_message_android = ["data" => ['body' => $pushNotiMsg, 'title' => $site_title, "flag" => 'track_order', 'order_id' => $order_details->id], 'notification' => ['body' => $pushNotiMsg, 'title' => $site_title]];
                                        $obj_send_push_notification->androidPushNotification($arr_push_message_android, $onBehalfUser->device_id, $onBehalfUser->user_type);
                                    } else {
                                        $arr_push_message_ios['to'] = $onBehalfUser->device_id;
                                        $arr_push_message_ios['priority'] = "high";
                                        $arr_push_message_ios['sound'] = "iOSSound.wav";
                                        $arr_push_message_ios['notification'] = $arr_push_message_ios;
                                        $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                                    }
                                }
                            } else {
                                $mobile_number_to_send = "+" . $order_details->getOrderTransInformation->onbehalf_country_code . $order_details->getOrderTransInformation->pickup_person_contact_no;
                                $message = "";
                                //sending sms to customer
                                $trackURL = url('/') . '/track-web-order/' . $order_details->id;
                                $message = Lang::choice('messages.customer_sms_order_accepted', $order_locale) . " " . $user_details->first_name . " " . $user_details->last_name;
                                // $message .= "\n";
                                //$message .= Lang::choice('messages.driver_number_msg', $order_locale) . " +" . $user_details->mobile_code . $user_details->user_mobile;
                                $message .= "\n";
                                $message .= "Track your order: " . $trackURL;

                                $obj_sms = new SendSms();
                                $obj_sms->sendMessage($mobile_number_to_send, $message);
                            }
                        }

                        //sending push notification to customer
                        $arr_push_message_ios = array("sound" => "iOSSound.wav", 'title' => $site_title, "text" => Lang::choice('messages.order_accepted', $order_locale), "flag" => 'order_accepted', 'message' => Lang::choice('messages.order_accepted', $order_locale), 'order_id' => $order_details->id);
                        $arr_push_message_ios = array();
                        $obj_send_push_notification = new SendPushNotification();
                        if (isset($customer_details->userInformation->device_id) && $customer_details->userInformation->device_id != '') {
                            if ($customer_details->userInformation->device_type == '0') {
                                //sending push notification customer user.                                
                                $arr_push_message_android = [];
                                $arr_push_message_android = ["data" => ['body' => Lang::choice('messages.order_accepted', "", [], $locale), 'title' => $site_title, "flag" => 'order_accepted', 'order_id' => $order_details->id], 'notification' => ['body' => Lang::choice('messages.order_accepted', "", [], $locale), 'title' => $site_title]];
                                $obj_send_push_notification->androidPushNotification($arr_push_message_android, $customer_details->userInformation->device_id, $customer_details->userInformation->user_type);
                            } else {
                                $arr_push_message_ios['to'] = $customer_details->userInformation->device_id;
                                $arr_push_message_ios['priority'] = "high";
                                $arr_push_message_ios['sound'] = "iOSSound.wav";
                                $arr_push_message_ios['notification'] = $arr_push_message_ios;
                                $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                            }
                        }

                        //Send SMS to on behalf of user order information & order tracking link.
                        //sending emails to user

                        /* $adminusers = UserInformation::where('user_type', 1)->get();
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

                          //Assign values to all macros
                          $arr_keyword_values['DRIVER_FIRST_NAME'] = $user_details->first_name;
                          $arr_keyword_values['DRIVER_LAST_NAME'] = $user_details->last_name;
                          $arr_keyword_values['DRIVER_ID'] = $user_id;
                          $arr_keyword_values['ORDER_ID'] = $order_id;
                          $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;
                          $arr_keyword_values['SITE_TITLE'] = $site_title;
                          $email_template_title = "emailtemplate::order-accepted-to-admin";
                          $email_template_subject = "Order has been accepted by driver";
                          if (isset($adminusers) && count($adminusers) > 0) {
                          foreach ($adminusers as $admin) {
                          @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin, $site_email, $site_title) {
                          if (isset($admin->user->email)) {
                          $message->to($admin->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                          }
                          });
                          }
                          } */

                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.driver_has_been_assigned', "", [], $locale));
                    } else {

                        $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_already_accepted', "", [], $locale));
                    }
                } else {

                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_already_accepted', "", [], $locale));
                }
            } else {
                //$order_notification->delete();
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function deliveryuserRejectOrder(Request $request) {        //
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $reason_text = isset($request['reason']) ? $request['reason'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $order_details = Order::where('id', $order_id)->first();
        if (isset($order_details) && count($order_details) && $reason_text != '') {
            $order_notification = OrderNotification::where('order_id', $order_id)->where('user_id', $user_id)->first();
            if (isset($order_notification) && count($order_notification)) {
                // rejecting order by driver                
                if ($order_details->status == '0') {
                    OrderNotification::where('order_id', $order_id)->where('user_id', $user_id)->delete();

                    $order_cancellation_status = array();
                    $order_cancellation_status['order_id'] = $order_id;
                    $order_cancellation_status['user_id'] = $user_id;
                    $order_cancellation_status['canceled_by'] = '2';
                    $order_cancellation_status['reason_text'] = $reason_text;
                    //storing cancel reason
                    OrderCancelationDetail::create($order_cancellation_status);

                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_has_been_rejected', "", [], $locale));
                } else {
                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.reject_not_allowed', "", [], $locale));
                }
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.reject_not_allowed', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.reject_not_allowed', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function rejectDeliveryuserAndReassign(Request $request) {

        $objDelivery = new DeliveryController();
        $objDelivery->makeAnScheduleOrder();
        //get all order which are pending
        $all_orders = Order::where('status', '0')->where('order_type', '1')->orderBy('id', 'DESC')->get();

        if (isset($all_orders) && count($all_orders) > 0) {
            $this->cronRejectAssignInstantScheduleOrder($all_orders);
        }

        $all_schedule_orders = Order::where('status', '0')->where('order_type', '2')->where('is_cron_execute', '1')->get();
        if (isset($all_schedule_orders) && count($all_schedule_orders) > 0) {
            $this->cronRejectAssignInstantScheduleOrder($all_schedule_orders);
        }
    }

    protected function cronRejectAssignInstantScheduleOrder($order_data) {

        $radious = 5;
        $driver_reject_time = 2;
        $avalale_driver_id = 0;
        $country_id = 0;
        $user_id = 0;

        foreach ($order_data as $order_detail) {
            $locale = isset($order_detail->locale) ? $order_detail->locale : 'en';
            \App::setLocale($locale);
            $countryInfo = Country::where('id', $order_detail->country_id)->first();

            if (isset($countryInfo) && count($countryInfo) > 0) {
                Config::set('app.timezone', $countryInfo->time_zone);
                $dt = new DateTime(date('Y-m-d H:i:s'));
                $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                $dt->setTimezone($tz);
                $country_id = $countryInfo->id;
            }

            $service_details_radious = CountryZoneService::where(['service_id' => $order_detail->service_id, 'zone_id' => $order_detail->zone_id])->first();
            if (isset($service_details_radious) && (($service_details_radious->range_for_finding_taxi) > 0)) {
                $radious = $service_details_radious->range_for_finding_taxi;
                $driver_reject_time = $service_details_radious->accepting_limit;
            }

            $order_notification_data = OrderNotification::where('order_id', $order_detail->id)->get();
            $site_email = GlobalValues::get('site-email');
            $site_title = GlobalValues::get('site-title');

            if (isset($order_notification_data) && count($order_notification_data) > 0) {
                $user_ids = "";
                foreach ($order_notification_data as $order_notification) {
                    $user_id = $order_notification->user_id;
                    $user_ids .= $user_id . ",";

                    $dt = new DateTime(date('Y-m-d H:i:s'));
                    //get timezone as per country
                    if (isset($countryInfo) && count($countryInfo) > 0) {
                        $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                        $dt->setTimezone($tz);
                    }
                    $date2_val = $dt->format('Y-m-d H:i:s');
                    $date2 = new DateTime($date2_val);
                    $date1 = new DateTime($order_notification->created_at);
                    $diffdate = date_diff($date2, $date1);
                    $driver_reject_time_sec = ($driver_reject_time / 60);
                    if ((($diffdate->h) > 0) || (($diffdate->i) >= $driver_reject_time)) {
                        if (isset($order_notification->id)) {
                            // rejecting order by driver
                            $country_id = $order_detail->country_id;
                            if ($order_detail->status == '0') {
                                $order_cancellation_status = array();
                                $order_cancellation_status['order_id'] = $order_detail->id;
                                $order_cancellation_status['user_id'] = $user_id;
                                $order_cancellation_status['canceled_by'] = '1';
                                $order_cancellation_status['reason_text'] = "No response-cron";
                                //storing cancel reason
                                OrderCancelationDetail::create($order_cancellation_status);
                            }
                        }
                    }
                }

                //checking for other user driver to send notification
                $user_ids = trim($user_ids, ",");
                $ret_driver_array = $this->getAllAciveDriverByServiceByDistance($order_detail->getOrderTransInformation->selected_pickup_lat, $order_detail->getOrderTransInformation->selected_pickup_long, $radious, $order_detail->service_id, $order_detail->id, $user_ids);
                //Reject driver who have already order
                $ret_driver_array = $ret_driver_array->reject(function ($drivers) {
                    $active_order_driver = DB::table('orders')->where(['driver_id' => $drivers->user_id, 'status' => '1'])->first();
                    if ($active_order_driver) {
                        return $drivers;
                    }
                });

                $ret_driver_array = $ret_driver_array->sort(function($a, $b) {
                    if ($a->distance == $b->distance) {
                        if ($a->rating == $b->rating) {
                            $a_yr_man = is_null($a->year_manufacture) ? $a->year_manufacture : 0;
                            $b_yr_man = is_null($b->year_manufacture) ? $b->year_manufacture : 0;
                            return $a->year_manufacture < $b->year_manufacture ? 1 : -1;
                        }
                        return $a->rating < $b->rating ? 1 : -1;
                    } else {
                        return $a->distance > $b->distance ? 1 : -1;
                    }
                });
                if (isset($ret_driver_array) && count($ret_driver_array) > 0) {

//                    usort($ret_driver_array, function ($a, $b) {
//                        if ($a->distance == $b->distance) {
//                            if ($a->rating == $b->rating) {
//                                $a_yr_man = is_null($a->year_manufacture) ? $a->year_manufacture : 0;
//                                $b_yr_man = is_null($b->year_manufacture) ? $b->year_manufacture : 0;
//                                return $a->year_manufacture < $b->year_manufacture ? 1 : -1;
//                            }
//                            return $a->rating < $b->rating ? 1 : -1;
//                        } else {
//                            return $a->distance > $b->distance ? 1 : -1;
//                        }
//                    });

                    foreach ($ret_driver_array as $driver_user_item) {
                        $avalale_driver_id = $driver_user_item->user_id;
                        //sending email about rejection only and assign driver user an order.

                        $order_notification_count_chk = OrderNotification::where(['order_id' => $order_detail->id, 'user_id' => $avalale_driver_id])->first();
                        if (!isset($order_notification_count_chk)) {

                            $date2_val = $dt->format('Y-m-d H:i:s');
                            $date2 = new DateTime($date2_val);
                            $avalale_driver_details = UserInformation::where('user_id', $avalale_driver_id)->first();
                            //check if driver has not active order
                            $is_active_order = Order::where(['driver_id' => $avalale_driver_id, 'status' => '1'])->first();
                            if (!isset($is_active_order) && !count($is_active_order)) {
                                //storing that user in notification table.
                                $arrOrderNotificationDetails['order_id'] = $order_detail->id;
                                $arrOrderNotificationDetails['user_id'] = $avalale_driver_id;
                                $arrOrderNotificationDetails['created_at'] = $date2;
                                $arrOrderNotificationDetails['updated_at'] = $date2;
                                $arrOrderNotificationDetails['message'] = Lang::choice('messages.order_assigned', "", [], $locale);
                                OrderNotification::create($arrOrderNotificationDetails);

                                $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => Lang::choice('messages.order_assign_driver', "", [], $locale), "flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'order_id' => $order_detail->id);
                                $arr_push_message_ios = array();
                                if (isset($avalale_driver_details->device_id) && $avalale_driver_details->device_id != '') {
                                    $obj_send_push_notification = new SendPushNotification();
                                    if ($avalale_driver_details->device_type == '0') {
                                        //sending push notification driver user.
                                        $arr_push_message_android = [];
                                        $arr_push_message_android = ["data" => ['body' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'title' => $site_title, "flag" => 'order_post', 'order_id' => $order_detail->id], 'notification' => ['body' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'title' => $site_title]];
                                        $obj_send_push_notification->androidPushNotification($arr_push_message_android, $avalale_driver_details->device_id, $avalale_driver_details->user_type);
                                    } else {
                                        $user_type = $avalale_driver_details->user_type;
                                        $arr_push_message_ios['to'] = $avalale_driver_details->device_id;
                                        $arr_push_message_ios['priority'] = "high";
                                        $arr_push_message_ios['sound'] = "iOSSound.wav";
                                        $arr_push_message_ios['notification'] = $arr_push_message;
                                        $obj_send_push_notification->IOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    //Check if new driver is available                                    
                    $this->checkNewAvailableDriver($order_detail, $site_email, $site_title, $locale, $radious);
                }
            } else {
                $this->checkNewAvailableDriver($order_detail, $site_email, $site_title, $locale, $radious);
            }
        }
    }

    public function checkNewAvailableDriver($order_detail, $site_email, $site_title, $locale, $radious) {

        if ($order_detail->status == '0') {
            $user_id = 0;
            $country_id = $order_detail->country_id;
            //checking for other user driver to send notification
            //get all user who has only 5 km range
            $ret_driver_array = $this->getAvailableDriver($order_detail->getOrderTransInformation->selected_pickup_lat, $order_detail->getOrderTransInformation->selected_pickup_long, $radious, $order_detail->id, $order_detail->service_id, "");

            //Reject driver who have already order
            $ret_driver_array = $ret_driver_array->reject(function ($drivers) {
                $active_order_driver = DB::table('orders')->where(['driver_id' => $drivers->user_id, 'status' => '1'])->first();
                if ($active_order_driver) {
                    return $drivers;
                }
            });

            $ret_driver_array = $ret_driver_array->sort(function($a, $b) {
                if ($a->distance == $b->distance) {
                    if ($a->rating == $b->rating) {
                        $a_yr_man = is_null($a->year_manufacture) ? $a->year_manufacture : 0;
                        $b_yr_man = is_null($b->year_manufacture) ? $b->year_manufacture : 0;
                        return $a->year_manufacture < $b->year_manufacture ? 1 : -1;
                    }
                    return $a->rating < $b->rating ? 1 : -1;
                } else {
                    return $a->distance > $b->distance ? 1 : -1;
                }
            });
            if (isset($ret_driver_array) && count($ret_driver_array) > 0) {
//                usort($ret_driver_array, function ($a, $b) {
//                    if ($a->distance == $b->distance) {
//                        if ($a->rating == $b->rating) {
//                            $a_yr_man = is_null($a->year_manufacture) ? $a->year_manufacture : 0;
//                            $b_yr_man = is_null($b->year_manufacture) ? $b->year_manufacture : 0;
//                            return $a->year_manufacture < $b->year_manufacture ? 1 : -1;
//                        }
//                        return $a->rating < $b->rating ? 1 : -1;
//                    } else {
//                        return $a->distance > $b->distance ? 1 : -1;
//                    }
//                });

                foreach ($ret_driver_array as $driver_user_item) {
                    $order_notification_count_chk = OrderNotification::where('order_id', $order_detail->id)->first();

                    $avalale_driver_id = $driver_user_item->user_id;
                    //sending email about rejection only and assign driver user an order.
//                                    if ($driver_user_item['avalale_driver_id'] != '' && count($order_notification_count_chk) <= 0) {
                    //if (isset($order_notification_count_chk) && count($order_notification_count_chk)) {


                    $countryInfo = Country::where('id', $order_detail->country_id)->first();
                    $dt = new DateTime(date('Y-m-d H:i:s'));
                    if (isset($countryInfo) && count($countryInfo)) {
                        $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                        $dt->setTimezone($tz);
                    }

                    $date2_val = $dt->format('Y-m-d H:i:s');
                    $date2 = new DateTime($date2_val);


                    //check if driver has not active order
                    $is_active_order = Order::where(['driver_id' => $avalale_driver_id, 'status' => '1'])->first();
                    $is_noti_already_sent = OrderNotification::where(['user_id' => $avalale_driver_id, 'order_id' => $order_detail->id])->first();

                    if (!isset($is_noti_already_sent)) {
                        if (!isset($is_active_order) && !count($is_active_order)) {
                            //storing that user in notification table.
                            $arrOrderNotificationDetails['order_id'] = $order_detail->id;
                            $arrOrderNotificationDetails['user_id'] = $avalale_driver_id;
                            $arrOrderNotificationDetails['created_at'] = $date2;
                            $arrOrderNotificationDetails['updated_at'] = $date2;
                            $arrOrderNotificationDetails['message'] = Lang::choice('messages.order_assigned', "", [], $locale);
                            OrderNotification::create($arrOrderNotificationDetails);

                            $order_assigned_status = array();
                            $order_assigned_status['order_id'] = $order_detail->id;
                            $order_assigned_status['user_id'] = $avalale_driver_id;
                            $order_assigned_status['reason_text'] = "Assigned to this user";
                            //storing cancel reason
                            OrderAssignedDetail::create($order_assigned_status);
                            $avalale_driver_details = UserInformation::where('user_id', $avalale_driver_id)->first();
                            if (isset($avalale_driver_details) && count($avalale_driver_details)) {
                                $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => Lang::choice('messages.order_assign_driver', "", [], $locale), "flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'order_id' => $order_detail->id);
                                $arr_push_message_ios = array();
                                if (isset($avalale_driver_details->device_id) && $avalale_driver_details->device_id != '') {
                                    $obj_send_push_notification = new SendPushNotification();
                                    if ($avalale_driver_details->device_type == '0') {
                                        $arr_push_message_android = ["data" => ['body' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'title' => $site_title, "flag" => 'order_post', 'order_id' => $order_detail->id], 'notification' => ['body' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'title' => $site_title]];
                                        $obj_send_push_notification->androidPushNotification($arr_push_message_android, $avalale_driver_details->device_id, $avalale_driver_details->user_type);
                                    } else {
                                        $user_type = $avalale_driver_details->user_type;
                                        $arr_push_message_ios['to'] = $avalale_driver_details->device_id;
                                        $arr_push_message_ios['priority'] = "high";
                                        $arr_push_message_ios['sound'] = "iOSSound.wav";
                                        $arr_push_message_ios['notification'] = $arr_push_message;
                                        $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                    }
                                }
                            }
                        }
                    }
                    //}
                }
                return true;
            } else {
                return false;
            }
        }
    }

    public function checkNewAvailableDriverPlaceOrder($order_detail, $site_email, $site_title, $locale, $radious) {

        if ($order_detail->status == '0') {
            $user_id = 0;
            $country_id = $order_detail->country_id;
            //checking for other user driver to send notification
            //get all user who has only 5 km range
            $ret_driver_array = $this->getAllAciveDriverByServiceByDistance($order_detail->getOrderTransInformation->selected_pickup_lat, $order_detail->getOrderTransInformation->selected_pickup_long, $radious, $order_detail->id, $order_detail->service_id, "");
            //print_r($ret_driver_array);
            //Reject driver who have already order
            $ret_driver_array = $ret_driver_array->reject(function ($drivers) {
                $active_order_driver = DB::table('orders')->where(['driver_id' => $drivers->user_id, 'status' => '1'])->first();
                if ($active_order_driver) {
                    return $drivers;
                }
            });
            //dd($ret_driver_array);
            $ret_driver_array = $ret_driver_array->sort(function($a, $b) {
                if ($a->distance == $b->distance) {
                    if ($a->rating == $b->rating) {
                        $a_yr_man = is_null($a->year_manufacture) ? $a->year_manufacture : 0;
                        $b_yr_man = is_null($b->year_manufacture) ? $b->year_manufacture : 0;
                        return $a->year_manufacture < $b->year_manufacture ? 1 : -1;
                    }
                    return $a->rating < $b->rating ? 1 : -1;
                } else {
                    return $a->distance > $b->distance ? 1 : -1;
                }
            });
            $ret_driver_array = $ret_driver_array->reject(function ($drivers) {
                $timer = $this->noShowSuspensionTimer($drivers->user_id);
                if (isset($timer) && count($timer) > 0) {
                    return $drivers;
                }
            });
            if (isset($ret_driver_array) && count($ret_driver_array) > 0) {

                foreach ($ret_driver_array as $driver_user_item) {

                    $avalale_driver_id = $driver_user_item->user_id;

                    $countryInfo = Country::where('id', $order_detail->country_id)->first();
                    $dt = new DateTime(date('Y-m-d H:i:s'));
                    if (isset($countryInfo) && count($countryInfo)) {
                        $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                        $dt->setTimezone($tz);
                    }

                    $date2_val = $dt->format('Y-m-d H:i:s');
                    $date2 = new DateTime($date2_val);

                    //check if driver has not active order
                    $is_canceled_order = OrderCancelationDetail::where(['user_id' => $avalale_driver_id, 'order_id' => $order_detail->id, 'canceled_by' => '2'])->first();
                    if (!isset($is_canceled_order) && !count($is_canceled_order)) {
                        //storing that user in notification table.
                        $arrOrderNotificationDetails['order_id'] = $order_detail->id;
                        $arrOrderNotificationDetails['user_id'] = $avalale_driver_id;
                        $arrOrderNotificationDetails['created_at'] = $date2_val;
                        $arrOrderNotificationDetails['updated_at'] = $date2_val;
                        $arrOrderNotificationDetails['message'] = Lang::choice('messages.order_assigned', "", [], $locale);
                        OrderNotification::create($arrOrderNotificationDetails);

                        $order_assigned_status = array();
                        $order_assigned_status['order_id'] = $order_detail->id;
                        $order_assigned_status['user_id'] = $avalale_driver_id;
                        $order_assigned_status['reason_text'] = "Assigned to this user";
                        OrderAssignedDetail::create($order_assigned_status);

                        $avalale_driver_details = UserInformation::where('user_id', $avalale_driver_id)->first();
                        if (isset($avalale_driver_details) && count($avalale_driver_details)) {
                            $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => Lang::choice('messages.order_assign_driver', "", [], $locale), "flag" => 'order_post', 'message' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'order_id' => $order_detail->id);
                            $arr_push_message_ios = array();
                            if (isset($avalale_driver_details->device_id) && $avalale_driver_details->device_id != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($avalale_driver_details->device_type == '0') {
                                    $arr_push_message_android = ["data" => ['body' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'title' => $site_title, "flag" => 'order_post', 'order_id' => $order_detail->id], 'notification' => ['body' => Lang::choice('messages.order_assign_driver', "", [], $locale), 'title' => $site_title]];
                                    $obj_send_push_notification->androidPushNotification($arr_push_message_android, $avalale_driver_details->device_id, $avalale_driver_details->user_type);
                                } else {
                                    $user_type = $avalale_driver_details->user_type;
                                    $arr_push_message_ios['to'] = $avalale_driver_details->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                        }
                    }
                }
                return true;
            } else {
                return false;
            }
        }
    }

    public function getAllAciveDriverByServiceByDistance($lat, $long, $distance, $order_id, $service_id, $user_ids) {

        if ($user_ids == "") {
            //$users = DB::select("call getActiveDriverByServiceDistanceWithoutUserIds(" . $lat . "," . $long . ",'" . $distance . "'," . $order_id . "," . $service_id . ")");
        } else {
            //$users = DB::select("call getActiveDriverByServiceDistance(" . $lat . "," . $long . ",'" . $distance . "'," . $order_id . "," . $service_id . ",'" . $user_ids . "')");
        }
        $users = $this->getAvailableDriver($lat, $long, $distance, $order_id, $service_id, $user_ids);
        return $users;
    }

    public function getAvailableDriver($latitude_in, $longtitude, $radious, $order_id, $service_id, $user_ids) {
//        $latitude_in = 18.515800;
//        $longtitude = 73.927200;
//        $radious = 5;
//        $service_id = 38;
//        $user_ids = "";
        $sql = 'SELECT GAUA.user_id,GAUVI.year_manufacture,GAUA.user_current_latitude,GAUA.user_current_longtitude,GAUA.latitude,GAUA.longitude,ROUND((((acos(sin((' . $latitude_in . '*pi()/180))*sin((GAUA.latitude*pi()/180))+cos((' . $latitude_in . '*pi()/180)) * cos((GAUA.latitude*pi()/180))*cos(((' . $longtitude . '-longitude)*pi()/180))))*180/pi())*60*1.1515*1.609344),4) AS distance,
        GADUI.driver_rating as rating FROM baggi_app_user_addresses as GAUA 
        INNER JOIN baggi_app_driver_user_informations AS GADUI ON GAUA.user_id = GADUI.user_id 
        INNER JOIN baggi_app_user_informations AS GAUI ON GADUI.user_id = GAUI.user_id and GAUI.user_status = "1" 
        INNER JOIN baggi_app_user_vehicle_informations AS GAUVI ON GADUI.user_id = GAUVI.user_id
        INNER JOIN baggi_app_user_service_informations AS GAUSI ON GAUSI.user_id = GAUVI.user_id        
        WHERE GADUI.availability = "1" AND ';
        if ($user_ids != "") {
            $sql .= 'GADUI.user_id NOT IN(' . $user_ids . ') AND ';
        }
        $sql .= ' GAUSI.service_id = ' . $service_id . ' GROUP BY GAUA.user_id having distance <= ' . $radious . ' order by distance asc limit 5';
//        echo $sql;
        $arr_driver = DB::select(DB::raw($sql));
        return collect($arr_driver);
    }

    public function getAllPaymentMethod(Request $request) {

        //get all payment methods
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $paymentMethods = DB::table('payment_methods')
                ->join('payment_method_translations', function ($join) use ($locale) {
                    $join->on('payment_method_translations.payment_method_id', '=', 'payment_methods.id');
                    $join->where('payment_method_translations.locale', '=', $locale);
                })
                ->where('payment_methods.status', '=', '1')
                ->get();
        $arr_to_return = array("error_code" => 0, "data" => $paymentMethods);
        return response()->json($arr_to_return);
    }

    public function getAllUserPaymentMethod(Request $request) {

        //get all payment methods
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $user_id = isset($request['user_id']) ? $request['user_id'] : '0';
        \App::setLocale($locale);
        $paymentMethods = UserPaymentMethod::where('user_id', $user_id)->get();
        $arr_to_return = array("error_code" => 0, "data" => $paymentMethods);
        return response()->json($arr_to_return);
    }

    public function updateDeliveryuserPaymentMethods(Request $request) {

        //get all payment methods
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $method_ids = isset($request['method_ids']) ? $request['method_ids'] : '';
        $user_id = isset($request['user_id']) ? $request['user_id'] : '0';
        \App::setLocale($locale);
        $method_ids = json_decode($method_ids);
        if ($user_id > 0 && count($method_ids) > 0) {
            UserPaymentMethod::where('user_id', $user_id)->delete();
            for ($k = 0; $k < count($method_ids); $k++) {
                $arr_methods = array();
                $arr_methods["payment_method_id"] = $method_ids[$k];
                $arr_methods["status"] = 1;
                $arr_methods["user_id"] = $user_id;
                UserPaymentMethod::create($arr_methods);
            }
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.method_updated', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
        }

        return response()->json($arr_to_return);
    }

    private function generateReferenceNumber() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    /* web-services added on 28-11-2016 */

    public function driverQuoteOnOrder(Request $request) {
        //
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $quotation_amount = isset($request['quotation_amount']) ? $request['quotation_amount'] : '0';
        $pickup_location = isset($request['pickup_location']) ? $request['pickup_location'] : '0';
        $description = isset($request['description']) ? $request['description'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($order_id > 0 && $user_id > 0 && $quotation_amount > 0) {
            $order_details = Order::where('id', $order_id)->first();
            $country_id = $order_details->country_id;
            $order_notification = OrderNotification::where('order_id', $order_id)->where('user_id', $user_id)->first();
            if (count($order_notification) > 0) {
                if ($order_details->status == 0) {
                    if (!($order_details->driver_id > 0)) {
                        $order_notification->delete();
                        $checkalreadyQuote = UserServiceQuotation::where('order_id', $order_id)->where('user_id', $user_id)->first();
                        if (count($checkalreadyQuote) <= 0) {
                            //saving user quotation
                            $userQuotation = array();
                            $userQuotation['user_id'] = $user_id;
                            $userQuotation['order_id'] = $order_id;
                            $userQuotation['qutation_amount'] = $quotation_amount;
                            $userQuotation['pickup_location'] = $pickup_location;
                            $userQuotation['description'] = $description;
                            $userQuotation['status'] = '0';
                            UserServiceQuotation::create($userQuotation);
                            //removing order notification of that user
                            $order_notification = OrderNotification::where('order_id', $order_id)->where('user_id', $user_id)->first();
                            if (isset($order_notification->id)) {
                                //  $order_notification->delete();
                            }
                            //sending email to customer about a new quotation

                            $user_details = UserInformation::where('user_id', $user_id)->first();
                            $customer_details = User::where('id', $order_details->customer_id)->first();
                            $site_email = GlobalValues::get('site-email');
                            $site_title = GlobalValues::get('site-title');
                            $arr_keyword_values = array();
                            //Assign values to all macros
                            $arr_keyword_values['CUSTOMER_FIRST_NAME'] = $customer_details->userInformation->first_name;
                            $arr_keyword_values['DRIVER_FIRST_NAME'] = $user_details->first_name;
                            $arr_keyword_values['DRIVER_LAST_NAME'] = $user_details->last_name;
                            $arr_keyword_values['MOBILE_NUMBER'] = "+" . $user_details->mobile_code . "" . $user_details->user_mobile;
                            $arr_keyword_values['SITE_TITLE'] = $site_title;
                            $arr_keyword_values['ORDER_ID'] = $order_id;
                            $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;

                            $mobile_code = str_replace("+", "", $user_details->mobile_code);
                            $arr_keyword_values['DRIVER_MOBILE'] = "+" . $mobile_code . "" . $user_details->user_mobile;
                            $email_subject = Lang::choice('messages.driver_order_quote_to_customer', "", [], $locale);
                            $tempate_name = "emailtemplate::order-quote-notify-to-customer-driver-details-" . $locale;
                            if (isset($customer_details->email)) {
                                $customer_email = $customer_details->email;
                                @Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($customer_email, $email_subject, $site_email, $site_title) {

                                            $message->to($customer_email)->subject($email_subject)->from($site_email, $site_title);
                                        });
                            }

                            //sending push notification to customer
                            $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => Lang::choice('messages.driver_order_quote_to_customer', "", [], $locale), "flag" => 'order_new_quotation_placed', 'message' => Lang::choice('messages.driver_order_quote_to_customer', "", [], $locale), 'order_id' => $order_details->id);
                            $arr_push_message_ios = array();
                            if (isset($customer_details->userInformation->device_id) && $customer_details->userInformation->device_id != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($customer_details->userInformation->device_type == '0') {
                                    //sending push notification customer user.
                                    $arr_push_message_android = array();
                                    $arr_push_message_android['to'] = $customer_details->userInformation->device_id;
                                    $arr_push_message_android['priority'] = "high";
                                    $arr_push_message_android['sound'] = "default";
                                    $arr_push_message_android['notification'] = $arr_push_message;
                                    $obj_send_push_notification->androidPushNotification(json_encode($arr_push_message_android), $customer_details->userInformation->device_id, $customer_details->userInformation->user_type);
                                } else {
                                    $user_type = $customer_details->userInformation->user_type;
                                    $arr_push_message_ios['to'] = $customer_details->userInformation->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                            //sending emails to user
                            //sending email to admin users
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
                            $arr_keyword_values['DRIVER_FIRST_NAME'] = $user_details->first_name;
                            $arr_keyword_values['DRIVER_LAST_NAME'] = $user_details->last_name;
                            $arr_keyword_values['DRIVER_ID'] = $user_id;
                            $arr_keyword_values['ORDER_ID'] = $order_id;
                            $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;
                            $arr_keyword_values['SITE_TITLE'] = $site_title;
                            $email_template_title = "emailtemplate::order-quation-by-driver-to-admin";
                            $email_template_subject = "A new quote posted by a driver";
                            if (count($adminusers) > 0) {
                                foreach ($adminusers as $admin) {

                                    @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin, $site_email, $site_title) {
                                                if (isset($admin->user->email)) {
                                                    $message->to($admin->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                                }
                                            });
                                }
                            }
                            //sending email to site admin              
                            @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title) {
                                        if (isset($site_email)) {
                                            $message->to($site_email)->subject($email_template_subject)->from($site_email, $site_title);
                                        }
                                    });
                            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.quote_posted_successfully', "", [], $locale));
                        } else {
                            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.already_quote', "", [], $locale));
                        }
                    } else {

                        $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_already_accepted', "", [], $locale));
                    }
                } else {

                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_already_accepted', "", [], $locale));
                }
            } else {
                //$order_notification->delete();
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function customerAcceptQuotation(Request $request) {
        //
        $quotation_id = isset($request['quotation_id']) ? $request['quotation_id'] : '0';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $user_id = isset($request['user_id']) ? $request['user_id'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($order_id > 0 && $quotation_id > 0) {
            $checkalreadyQuote = UserServiceQuotation::where('id', $quotation_id)->first();
            if (count($checkalreadyQuote) > 0) {
                //get all quotation on this order
                $allOrderQuotes = UserServiceQuotation::where('order_id', $order_id)->get();
                if (count($allOrderQuotes) > 0) {
                    foreach ($allOrderQuotes as $quote) {
                        //update each quote as rejected
                        $quote->status = 2;
                        $quote->save();

                        $order_notification = OrderNotification::where('order_id', $quote->order_id)->first();
                        if (count($order_notification) > 0) {
                            $order_notification->delete();
                        }
                        $site_email = GlobalValues::get('site-email');
                        $site_title = GlobalValues::get('site-title');
                        //sending push notification to other drivers about their rejection.
                        //sending push notification to driver
                        if ($checkalreadyQuote->user_id != $quote->user_id) {
                            $user_details_rejected = UserInformation::where('user_id', $quote->user_id)->first();
                            $arr_push_message_reject = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => Lang::choice('messages.quote_has_been_rejected', "", [], $locale), "flag" => 'order_rejected', 'message' => Lang::choice('messages.quote_has_been_rejected', "", [], $locale), 'order_id' => $quote->order_id);
                            $arr_push_message_ios = array();
                            if (isset($user_details_rejected->device_type) && $user_details_rejected->device_type != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($user_details_rejected->device_type == '0' && $user_details_rejected->device_id != '') {
                                    //sending push notification customer user.
                                    $arr_push_message_android = array();
                                    $arr_push_message_android['to'] = $user_details_rejected->device_id;
                                    $arr_push_message_android['priority'] = "high";
                                    $arr_push_message_android['sound'] = "default";
                                    $arr_push_message_android['notification'] = $arr_push_message_reject;
                                    $obj_send_push_notification->androidPushNotification(json_encode($arr_push_message_android), $user_details_rejected->device_id, $user_details_rejected->user_type);
                                } else {
                                    $user_type = $user_details_rejected->user_type;
                                    $arr_push_message_ios['to'] = $user_details_rejected->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message_reject;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                        }
                    }
                    //update select quote a accepted
                    $checkalreadyQuote->status = 1;
                    $checkalreadyQuote->save();
                    //update order status as active and driver_id of select user
                    $order_details = Order::where('id', $checkalreadyQuote->order_id)->first();
                    $country_id = $order_details->country_id;
                    $order_details->driver_id = $checkalreadyQuote->user_id;
                    $order_details->total_amount = $checkalreadyQuote->qutation_amount;
                    $order_details->status = 1;
                    $order_details->status_by_driver = 1;
                    $order_details->save();
                    //saving this information to status transaction
                    $statusTransaction = array();
                    $statusTransaction['user_id'] = $user_id;
                    $statusTransaction['order_id'] = $order_details->id;
                    $statusTransaction['transaction_content'] = "Quote has been accepted by customer user";
                    OrdersTransactionStatus::create($statusTransaction);
                    //sending push notification to driver user about his quote acceptance

                    $user_details = UserInformation::where('user_id', $checkalreadyQuote->user_id)->first();
                    $customer_details = UserInformation::where('user_id', $order_details->customer_id)->first();
                    $site_email = GlobalValues::get('site-email');
                    $site_title = GlobalValues::get('site-title');
                    $arr_keyword_values = array();
                    //Assign values to all macros
                    $arr_keyword_values['CUSTOMER_FIRST_NAME'] = $customer_details->first_name;
                    $arr_keyword_values['CUSTOMER_LAST_NAME'] = $customer_details->first_name;
                    $arr_keyword_values['DRIVER_FIRST_NAME'] = $user_details->first_name;
                    $arr_keyword_values['DRIVER_LAST_NAME'] = $user_details->last_name;
                    $arr_keyword_values['MOBILE_NUMBER'] = "+" . $user_details->mobile_code . "" . $user_details->user_mobile;
                    $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $arr_keyword_values['ORDER_ID'] = $order_id;
                    $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;


                    $email_subject = Lang::choice('messages.quote_has_been_accepted', "", [], $locale);
                    $tempate_name = "emailtemplate::quote_has_been-accepted-" . $locale;
                    if (isset($customer_details->user->email)) {
                        $customer_email = $customer_details->user->email;
                        @Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($customer_email, $email_subject, $site_email, $site_title) {

                                    $message->to($customer_email)->subject($email_subject)->from($site_email, $site_title);
                                });
                    }
                    //sending push notification to driver
                    $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => Lang::choice('messages.quote_has_been_accepted', "", [], $locale), "flag" => 'order_quote_accepted', 'message' => Lang::choice('messages.quote_has_been_accepted', "", [], $locale), 'order_id' => $order_id);
                    $arr_push_message_ios = array();
                    if (isset($user_details->device_type)) {
                        $obj_send_push_notification = new SendPushNotification();
                        if ($user_details->device_type == '0') {
                            //sending push notification driver user.
                            $arr_push_message_android = array();
                            $arr_push_message_android['to'] = $user_details->device_id;
                            $arr_push_message_android['priority'] = "high";
                            $arr_push_message_android['sound'] = "default";
                            $arr_push_message_android['notification'] = $arr_push_message;
                            $obj_send_push_notification->androidPushNotification(json_encode($arr_push_message_android), $user_details->device_id, $user_details->user_type);
                        } else {
                            $user_type = $user_details->user_type;
                            $arr_push_message_ios['to'] = $user_details->device_id;
                            $arr_push_message_ios['priority'] = "high";
                            $arr_push_message_ios['sound'] = "iOSSound.wav";
                            $arr_push_message_ios['notification'] = $arr_push_message;
                            $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                        }
                    }

                    //sending emails to user
                    //sending email to admin users
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
                    $arr_keyword_values['DRIVER_FIRST_NAME'] = $user_details->first_name;
                    $arr_keyword_values['DRIVER_LAST_NAME'] = $user_details->last_name;
                    $arr_keyword_values['DRIVER_ID'] = $user_id;
                    $arr_keyword_values['ORDER_ID'] = $order_id;
                    $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;
                    $arr_keyword_values['SITE_TITLE'] = $site_title;
                    $email_template_title = "emailtemplate::order-quation-by-driver-to-admin";
                    $email_template_subject = "A new quote posted by a driver";
                    if (count($adminusers) > 0) {
                        foreach ($adminusers as $admin) {

                            @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin, $site_email, $site_title) {
                                        if (isset($admin->user->email)) {
                                            $message->to($admin->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                        }
                                    });
                        }
                    }
                    //sending email to site admin              
                    @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title) {
                                if (isset($site_email)) {
                                    $message->to($site_email)->subject($email_template_subject)->from($site_email, $site_title);
                                }
                            });
                }

                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.quote_accepted_succesffully', "", [], $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.quotation_accpet_invalid', "", [], $locale));
            }
        } else {
            //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.quotation_accpet_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function driverRejectQuotation(Request $request) {
        //
        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $reason_text = isset($request['reason_text']) ? $request['reason_text'] : 'en';
        \App::setLocale($locale);
        if ($order_id > 0 && $user_id > 0 && $reason_text != '') {
            $order_details = Order::where('id', $order_id)->first();
            $country_id = $order_details->country_id;
            $user_details = UserInformation::where('user_id', $user_id)->first();
            $order_notification = OrderNotification::where('order_id', $order_id)->where('user_id', $user_id)->first();
            if (count($order_notification) > 0) {
                if ($order_details->status == 0) {
                    if (!($order_details->driver_id > 0)) {

                        $order_notification->delete();
                        $order_cancellation_status = array();
                        $order_cancellation_status['order_id'] = $order_id;
                        $order_cancellation_status['user_id'] = $user_id;
                        $order_cancellation_status['reason_text'] = $reason_text;
                        //storing cancel reason
                        OrderCancelationDetail::create($order_cancellation_status);

                        //sending emails to user
                        //sending email to admin users
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
                        $arr_keyword_values['DRIVER_FIRST_NAME'] = $user_details->first_name;
                        $arr_keyword_values['DRIVER_LAST_NAME'] = $user_details->last_name;
                        $arr_keyword_values['DRIVER_ID'] = $user_id;
                        $arr_keyword_values['ORDER_ID'] = $order_id;
                        $arr_keyword_values['ORDER_NUMBER'] = $order_details->order_unique_id;
                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        $email_template_title = "emailtemplate::driver-reject-quotation-request-admin";
                        $email_template_subject = "A driver has reject the quotation request";
                        if (count($adminusers) > 0) {
                            foreach ($adminusers as $admin) {

                                @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $admin, $site_email, $site_title) {
                                            if (isset($admin->user->email)) {
                                                $message->to($admin->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                                            }
                                        });
                            }
                        }
                        //sending email to site admin              
                        @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $site_email, $site_title) {
                                    if (isset($site_email)) {
                                        $message->to($site_email)->subject($email_template_subject)->from($site_email, $site_title);
                                    }
                                });
                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.quote_rejected_successfully', "", [], $locale));
                    } else {

                        $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_already_accepted', "", [], $locale));
                    }
                } else {

                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_already_accepted', "", [], $locale));
                }
            } else {
                //$order_notification->delete();
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function customerRejectQuotation(Request $request) {
        //
        $quotation_id = isset($request['quotation_id']) ? $request['quotation_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if ($quotation_id > 0) {

            // 
            $quotation_details = UserServiceQuotation::where('id', $quotation_id)->first();

            if (count($quotation_details) > 0) {
                $quotation_details->status = 2;
                $quotation_details->save();
                $order_details = Order::where('id', $quotation_details->order_id)->first();
                $country_id = $order_details->country_id;
                $user_details = UserInformation::where('user_id', $quotation_details->user_id)->first();
                if (isset($user_details->user_id)) {
                    $site_email = GlobalValues::get('site-email');
                    $site_title = GlobalValues::get('site-title');
                    $reject_message = Lang::choice('messages.order_quote_reject', "", [], $locale);
                    $reject_message = $reject_message . "" . $order_details->order_unique_id;
                    $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $reject_message, "flag" => 'quotation_reject_customer', 'message' => $reject_message, 'order_id' => $order_details->id);
                    $arr_push_message_ios = array();
                    if (isset($user_details->device_id) && $user_details->device_id != '') {
                        $obj_send_push_notification = new SendPushNotification();
                        if ($user_details->device_type == '0') {
                            //sending push notification driver user.
                            $arr_push_message_android = array();
                            $arr_push_message_android['to'] = $user_details->device_id;
                            $arr_push_message_android['priority'] = "high";
                            $arr_push_message_android['sound'] = "default";
                            $arr_push_message_android['notification'] = $arr_push_message;
                            $obj_send_push_notification->androidPushNotification(json_encode($arr_push_message_android), $user_details->device_id, $user_details->user_type);
                        } else {
                            $user_type = $user_details->user_type;
                            $arr_push_message_ios['to'] = $user_details->device_id;
                            $arr_push_message_ios['priority'] = "high";
                            $arr_push_message_ios['sound'] = "iOSSound.wav";
                            $arr_push_message_ios['notification'] = $arr_push_message;
                            $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                        }
                    }
                }
                if ($order_details->status == 0) {

                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.quote_rejected_successfully', "", [], $locale));
                } else {

                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_operation', "", [], $locale));
                }
            } else {

                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_operation', "", [], $locale));
            }
        } else {
            //$order_notification->delete();
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.invalid_operation', "", [], $locale));
        }

        return response()->json($arr_to_return);
    }

    public function orderAllQuotation(Request $request) {
        //

        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $arrAllQuotaion = array();
        \App::setLocale($locale);
        if ($order_id > 0) {
            $order_details = Order::where('id', $order_id)->first();

            if (count($order_details) > 0) {
                if ($order_details->status == 0) {

                    $userAllQuotations = UserServiceQuotation::where('order_id', $order_id)->where('status', '0')->get();

                    if (count($userAllQuotations) > 0) {
                        $i = 0;
                        foreach ($userAllQuotations as $quotation) {
                            $arrAllQuotaion[$i] = $quotation;
                            $arrAllQuotaion[$i]['order_unique_id'] = $order_details->order_unique_id;

                            if (isset($quotation->user_id)) {
                                //get user information
                                $driverUserInformations = UserInformation::where('user_id', $quotation->user_id)->first();
                                if (isset($driverUserInformations->first_name)) {
                                    $arrAllQuotaion[$i]['driver_first_name'] = $driverUserInformations->first_name;
                                    $arrAllQuotaion[$i]['driver_last_name'] = $driverUserInformations->last_name;
                                    $arrAllQuotaion[$i]['driver_mobile'] = "+" . $driverUserInformations->mobile_code . "" . $driverUserInformations->user_mobile;
                                }
                            }
                        }
                    }
                    $arr_to_return = array("error_code" => 0, "data" => $userAllQuotations);
                } else {

                    $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_already_accepted', "", [], $locale));
                }
            } else {
                //$order_notification->delete();
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_accpet_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    /* get distance and traval time by google API */

    function GetDrivingDistance($lat1, $lat2, $long1, $long2) {
//        echo " source lat ".$lat1." source long ". $lat2." dis lat ". $long1." dis long ". $long2; exit;
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&language=pl-PL";
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
        $dist = isset($response_a['rows'][0]['elements'][0]['distance']['text']) ? $response_a['rows'][0]['elements'][0]['distance']['text'] : '0,0';
        $time = isset($response_a['rows'][0]['elements'][0]['duration']['text']) ? $response_a['rows'][0]['elements'][0]['duration']['text'] : '0 min';
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
        $distance = $distance * 60 * 1.1515;
        switch ($unit) {
            case 'Mi': break;
            case 'Km' : $distance = $distance * 1.609344;
        }
        return ((float) ($distance));
    }

    public function getOrderList(Request $request) {
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $i = 0;
        $arrOrderDetails = array();
        if ($driver_id > 0 && $driver_id != '') {
            $arr_orders = Record::where('driver_id', $driver_id)->where('order_status', 1)->get();
//            $arr_orders = $arr_orders->sortByDesc('created_at');
//            dd($arr_orders);
            if (isset($arr_orders) && count($arr_orders) > 0) {
                //storing all order details
                $i = 0;
                foreach ($arr_orders as $order) {

//                    $arrOrderDetails[$i]['order'] = $order;
//                    // getting order details.
//                    $arrOrderDetails[$i]['order_details'] = $order->getOrderTransInformation;
//                    $arrOrderDetails[$i]['service'] = $order->getServicesDetails->name;
//                    if (isset($order->getServicesDetails)) {
//                        $service = $order->getServicesDetails;
//                        $arrOrderDetails[$i]['category'] = $service->categoryInfo->name;
//                        //get status by driver text
//                        $catgeoryMsgDetails = CategoryStatusMsg::where('category_id', $service->categoryInfo->id)->where('status_value', $order->status_by_driver)->first();
//                        $arrOrderDetails[$i]['status_by_driver_text'] = isset($catgeoryMsgDetails->status_description) ? $catgeoryMsgDetails->status_description : '';
//                    }
//                    $arrOrderDetails[$i]['driver_first_name'] = "";
//                    $arrOrderDetails[$i]['driver_last_name'] = "";
//                    if (isset($order->driver_id)) {
//                        $driver_user_details = UserInformation::where('user_id', $order->driver_id)->first();
//                        $arrOrderDetails[$i]['driver_first_name'] = $driver_user_details->first_name;
//                        $arrOrderDetails[$i]['driver_last_name'] = $driver_user_details->last_name;
//                        $arrOrderDetails[$i]['driver_mobile'] = "+" . str_replace("+", "", $driver_user_details->mobile_code) . "" . $driver_user_details->user_mobile;
//                        if (isset($driver_user_details->profile_picture)) {
//                            $arrOrderDetails[$i]['driver_image'] = asset("/storageasset/user-images/" . $driver_user_details->profile_picture);
//                        } else {
//                            $arrOrderDetails[$i]['driver_image'] = "";
//                        }
                    // $arrOrderDetails[$i]['driver_image']=$driver_user_details->user_mobile;
                }

//                    $i++;
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('Order List', "", [], $locale), "order_details" => $arr_orders);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('Current order not exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('Driver not found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function updateDriverUser(Request $request) {
        $arr_to_return = array();
        $flag = isset($request['flag']);
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        if (isset($driver_id) && $driver_id > 0) {
            $arrDriverDetails = User::find($driver_id);
            if (count($arrDriverDetails) > 0) {

                if (isset($request["first_name_en"]) && $flag == 1) {
                    $arrDriverDetails->userInformation->first_name = $request["first_name_en"];
                    $arrDriverDetails->userInformation->last_name = $request["last_name_en"];
                }

                if (isset($request["first_name_ar"]) && $flag == 2) {
                    $arrDriverDetails->userInformation->first_name_ar = $request["first_name_ar"];
                    $arrDriverDetails->userInformation->last_name_ar = $request["last_name_ar"];
                }
                if (isset($request["civil_id"]) && $flag == 3) {
                    $arrDriverDetails->userInformation->civil_id = $request["civil_id"];
                }
                if (isset($request["nationality"]) && $flag == 4) {
                    $arrDriverDetails->userInformation->nationality = $request["nationality"];
                }

                $arrDriverDetails->userInformation->save();
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.driver_profile_update', "", [], $locale));
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_profile_not_found', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.driver_profile_not_found', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function changeDriverEmail(Request $request) {
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $new_email = isset($request['new_email']) ? $request['new_email'] : '';
        $old_email = isset($request['old_email']) ? $request['old_email'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $arr_user_data_existing = User::where('email', $new_email)->where('id', '!=', $driver_id)->get();
        $arr_user_data_existing = $arr_user_data_existing->filter(function($user) {
            return $user->userInformation->user_type == 2;
        });


        if ($driver_id > 0 && $driver_id != '') {
            $arr_user_data = User::find($driver_id);
            if ($new_email == $old_email) {
                $arr_to_return = array("error_code" => 5, "msg" => Lang::choice('messages.same_email', "", [], $locale));
            } else {
                if (isset($arr_user_data->email) && ($old_email != '') && ($old_email != $arr_user_data->email)) {
                    $arr_to_return = array("error_code" => 4, "msg" => Lang::choice('messages.old_email_does_not_match', "", [], $locale));
                } else {
                    if (count($arr_user_data_existing) == 0) {
                        if (isset($arr_user_data) && $arr_user_data->userInformation->user_type == '2') {
                            $activation_code = $this->generateReferenceNumber();
                            // $arr_user_data->userInformation->temp_email = $new_email;
                            $arr_user_data->email = $new_email;
                            $arr_user_data->save();
                            $arr_user_data->userInformation->activation_code = $activation_code;
                            $arr_user_data->userInformation->save();

                            //sending email on email change
                            $arr_keyword_values = array();

                            $site_email = GlobalValues::get('site-email');
                            $site_title = GlobalValues::get('site-title');
                            //Assign values to all macros
                            $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                            $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                            $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                            $arr_keyword_values['SITE_TITLE'] = $site_title;

                            $email_subject = Lang::choice('messages.email_subject_for_change_email', "", [], $locale);
                            $email_template = "emailtemplate::user-email-change-" . $locale;
                            @Mail::send($email_template, $arr_keyword_values, function ($message) use ($new_email, $site_email, $site_title, $email_subject) {

                                        $message->to($new_email)->subject($email_subject)->from($site_email, $site_title);
                                    });

                            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.customer_email_changed', "", [], $locale));
                        } else {
                            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.customer_not_exist', "", [], $locale));
                        }
                    } else {
                        $arr_to_return = array("error_code" => 3, "msg" => Lang::choice('messages.customer_email_already_exist', "", [], $locale));
                    }
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.customer_invalid', "", [], $locale));
        }

        return response()->json($arr_to_return);
    }

    protected function updateDriverMobile(Request $request) {
        $arr_to_return = array();
        //getting mobile number
        $mobile_no = isset($request['mobile_no']) ? $request['mobile_no'] : '';
        $old_mobile_no = isset($request['old_mobile_no']) ? $request['old_mobile_no'] : '';
        //$old_mobile_code = isset($request['old_mobile_code']) ? $request['old_mobile_code'] : '';
        $mobile_code = isset($request['mobile_code']) ? $request['mobile_code'] : '';
        $otp = isset($request['otp']) ? $request['otp'] : '';
        $otp_for = isset($request['otp_for']) ? $request['otp_for'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : '';
        \App::setLocale($locale);
        $arrVerifyOtp = UserOtpCodes::where("mobile", $mobile_no)->where('otp_for', $otp_for)->where('otp_code', $otp)->where("status", '1')->first();

        if (count($arrVerifyOtp) > 0) {
            $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
            $arrCustomerDetails = User::find($driver_id);
            $mobile_code = str_replace("+", "", trim($mobile_code));
            $arrUserName = UserInformation::where("user_mobile", $mobile_no)->where('user_type', 2)->first();
            if (count($arrUserName) > 0 && ($arrUserName->mobile_code == $mobile_code)) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.mobile_already_exist', "", [], $locale));
            } else {
                if (isset($request["mobile_no"])) {
                    $arrCustomerDetails->username = $mobile_no;
                    $arrCustomerDetails->userInformation->user_mobile = $mobile_no;
                    $arrCustomerDetails->userInformation->mobile_code = $mobile_code;
                }
                $arrCustomerDetails->userInformation->save();
                $arrCustomerDetails->save();

                $driver_mobile_history = new DriverMobileNumberHistory();
                $driver_mobile_history->driver_id = $arrCustomerDetails->id;
                $driver_mobile_history->old_user_mobile = $old_mobile_no;
                $driver_mobile_history->save();
            }
        }

        if (count($arrVerifyOtp) > 0) {
            $arrVerifyOtp->status = 0;
            $arrVerifyOtp->save();
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.mobile_has_been_changed', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.otp_is_not_valid_expired', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function updateDeliveryuserTripStatus(Request $request) {

        $user_id = isset($request['user_id']) ? $request['user_id'] : '';
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $status = isset($request['status']) ? $request['status'] : '';
        $current_latitude = isset($request['current_latitude']) ? $request['current_latitude'] : '';
        $current_address = isset($request['current_address']) ? $request['current_address'] : '';
        $current_longtitude = isset($request['current_longtitude']) ? $request['current_longtitude'] : '';
        $required_time_for_pickup = isset($request['required_time_for_pickup']) ? $request['required_time_for_pickup'] : '';
        $distance = isset($request['distance']) ? $request['distance'] : '0';
        $temp_distance = isset($request['temp_distance']) ? $request['temp_distance'] : '0'; //distance from driver current location to pick up location
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $flag = 0;
        $fare_amount = 0;
        $pick_up_waiting_time = 0;
        $type = '';
        $middel_location_wating_time = 0;
        $middle_location_address = array();
        $middle_location_pickup_waiting_time = array();
        $arr_to_return = array();
        if ($order_id != '' && $status != '') {
            $order_details = Order::where('id', $order_id)->where('status', 1)->first();
            if (!isset($order_details)) {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_invalid', "", [], $locale));
                return response()->json($arr_to_return);
            }

            $customer_details = User::where('id', $order_details->customer_id)->first();
            //create mail for driver
            $driver_details = User::where('id', $order_details->driver_id)->first();
            if (!isset($customer_details)) {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
                return response()->json($arr_to_return);
            }
            if (!isset($driver_details)) {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.user_invalid', "", [], $locale));
                return response()->json($arr_to_return);
            }
            $order_locale = isset($order_details->locale) ? $order_details->locale : 'en';
            $category_id_main = isset($order_details->getServicesDetails->category_id) ? $order_details->getServicesDetails->category_id : '0';
            //Get category status details
            $categorystatus = CategoryStatusMsg::where('category_id', $category_id_main)->where('status_value', $status)->where('locale', $order_locale)->first();
            $calculate_flag = isset($categorystatus->calculate_flag) ? $categorystatus->calculate_flag : '';
            $status_text = isset($categorystatus->status_msg) ? $categorystatus->status_msg : '';
            if ((isset($order_details) && count($order_details) > 0) && (count($categorystatus) > 0)) {

                $dt = new DateTime(date('Y-m-d H:i:s'));

                //get timezone as per country
                $countryInfo = Country::where('id', $order_details->country_id)->first();
                if (isset($countryInfo) && count($countryInfo) > 0) {
                    $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                    $dt->setTimezone($tz);
                }

                $date2_val = $dt->format('Y-m-d H:i:s');
                $date2 = new DateTime($date2_val);

                $driver_details = User::where('id', $order_details->driver_id)->first();

                $arrDataService = CountryZoneService::where('zone_id', $order_details->zone_id)->first();
                if (isset($arrDataService) && count($arrDataService) > 0) {
                    //$passenger_pickup_waiting_time = $arrDataService->passenger_pickup_waiting_time / 60;
                    $passenger_pickup_waiting_time = $arrDataService->passenger_pickup_waiting_time;
                } else {
                    $passenger_pickup_waiting_time = 2; // 2 minutes
                }
                if ($calculate_flag == '0') {
                    $order_details->status_by_driver = $status;
                    $order_details->on_my_way_date_time = $date2;
                    $order_details->save();
                    $order_details->getOrderTransInformation->required_time_for_pickup = $required_time_for_pickup;
                    $order_details->getOrderTransInformation->driver_on_way_time = $date2;
                    $order_details->getOrderTransInformation->save();
                    //notification for customer
                    $notification_customer_time = $date2_val;
                    $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . $passenger_pickup_waiting_time . ' min'));

                    $order_message = Lang::choice('messages.on_the_way_status_updated', "", [], $locale);
                    $order_message = str_replace("%%DRIVER_NAME%%", $driver_details->userInformation->first_name . ' ' . $driver_details->userInformation->last_name, $order_message);
                    $order_message = str_replace("%%CAR_NAME%%", $driver_details->userVehicleInformation->CarModel->name, $order_message);
                    $order_message = str_replace("%%PLATE_NUMBER%%", $driver_details->userVehicleInformation->plate_number, $order_message);

                    $data = ['notification_customer_time' => $notification_customer_time, 'notification_end_time' => $notification_end_time, 'order_message' => $order_message, 'order_details' => $order_details, 'date2' => $date2];
                    $this->sendNoificationForOrderStatusChange($data);
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.status_updated', "", [], $locale));
                } elseif ($calculate_flag == '1') {
                    $order_details->status_by_driver = $status;
                    $order_details->arrived_for_pickup_date_time = $date2;
                    $order_details->save();
                    $order_waiting_time_detail = new OrderWaitingTimeDetail();
                    $order_waiting_time_detail->order_id = $order_details->id;
                    $order_waiting_time_detail->waiting_type = 1;
                    $order_waiting_time_detail->start_date_time = $date2;
                    $order_waiting_time_detail->save();

                    $distance = ($distance / 1000);
                    if ($distance > 0) {
                        $order_details->getOrderTransInformation->pre_ride_driving_distance = $distance;
                        $order_details->getOrderTransInformation->required_time_for_pickup = $required_time_for_pickup;
                    }
                    $order_details->getOrderTransInformation->start_pickup_no_show_up_time = $date2;
                    $order_details->getOrderTransInformation->save();
                    //notification for customer
                    $notification_customer_time = $date2_val;
                    $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . $passenger_pickup_waiting_time . ' min'));
                    $order_message = Lang::choice('messages.pickup_status_updated', "", [], $locale);
                    $data = ['notification_customer_time' => $notification_customer_time, 'notification_end_time' => $notification_end_time, 'order_message' => $order_message, 'order_details' => $order_details, 'date2' => $date2];
                    $this->sendNoificationForOrderStatusChange($data);

                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.status_updated', "", [], $locale));
                } elseif ($calculate_flag == '2') {
                    $order_details->status_by_driver = $status;
                    $order_details->save();

                    $order_details->getOrderTransInformation->end_date_time = $date2;
                    $order_details->getOrderTransInformation->save();

                    $order_wait_time = OrderWaitingTimeDetail::where('order_id', $order_details->id)->orderBy('id', 'desc')->first();
                    $order_wait_time->end_date_time = $date2;
                    $order_wait_time->save();
                    //notification for customer
                    $notification_customer_time = $date2_val;
                    $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . $passenger_pickup_waiting_time . ' min'));
                    $order_message = Lang::choice('messages.start_ride_status_updated', "", [], $locale);
                    $data = ['notification_customer_time' => $notification_customer_time, 'notification_end_time' => $notification_end_time, 'order_message' => $order_message, 'order_details' => $order_details, 'date2' => $date2];
                    // $this->sendNoificationForOrderStatusChange($data);
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.status_updated', "", [], $locale));
                } elseif ($calculate_flag == '3') {

                    $middle_location_detail = MiddleLocation::where('order_id', $order_details->id)->where('status', 0)->orderBy('id', 'asc')->first();
                    if (isset($middle_location_detail) && count($middle_location_detail) > 0) {
                        $middle_location_detail->status = 1;
                        $middle_location_detail->reached_at = $date2;
                        $middle_location_detail->save();

                        $order_wait_time_detail = new OrderWaitingTimeDetail();
                        $order_wait_time_detail->order_id = $order_details->id;
                        $order_wait_time_detail->middle_location_id = $middle_location_detail->id;
                        $order_wait_time_detail->waiting_type = 2;
                        $order_wait_time_detail->start_date_time = $date2;
                        $order_wait_time_detail->save();
                    }


                    //notification for customer
                    $notification_customer_time = $date2_val;
                    $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . $passenger_pickup_waiting_time . ' min'));
                    $order_message = Lang::choice('messages.middle_destination_status_updated', "", [], $locale);
                    $data = ['notification_customer_time' => $notification_customer_time, 'notification_end_time' => $notification_end_time, 'order_message' => $order_message, 'order_details' => $order_details, 'date2' => $date2];
                    //  $this->sendNoificationForOrderStatusChange($data);
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.status_updated', "", [], $locale));
                } elseif ($calculate_flag == '4') {
                    $order_details->status_by_driver = $status;
                    $order_details->status = 6; //no show off for customer from driver     
                    $order_details->save();
                    //no show added time
                    $arrUser = UserInformation::where('user_id', $order_details->driver_id)->first();
                    $arrUser->no_show_date_time = $date2_val;
                    $arrUser->save();



                    //notification for customer
                    $notification_customer_time = $date2_val;
                    $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . $passenger_pickup_waiting_time . ' min'));

                    $order_message = Lang::choice('messages.no_show_for_driver', "", [], $locale);
                    $order_message = str_replace("%%DRIVER_NAME%%", $order_details->getUserDriverInformation->first_name . " " . $order_details->getUserDriverInformation->last_name, $order_message);
                    $order_message = str_replace("%%ORDER_NUMBER%%", $order_details->order_unique_id, $order_message);

                    //$order_message = Lang::choice('messages.no_show_status_updated',"",[],$locale);
                    $data = ['notification_customer_time' => $notification_customer_time, 'notification_end_time' => $notification_end_time, 'order_message' => $order_message, 'order_details' => $order_details, 'date2' => $date2];
                    // $this->sendNoificationForOrderStatusChange($data);                 

                    $arrDataService = CountryZoneService::where('zone_id', $order_details->zone_id)->first();
                    $charge_amount = 0.000;
                    $fixed_fees = 0.000;
                    $no_show_fees = 0.000;

                    if (isset($arrDataService) && count($arrDataService) > 0) {
                        $fixed_fees = (double) $arrDataService->fixed_fees;
                        $no_show_fees = (double) $arrDataService->no_show_fees_for_driver;
                        $charge_amount = (double) $arrDataService->fixed_fees + (double) $arrDataService->no_show_fees_for_driver;
                    }
                    //update CUSTOMER wallet details   

                    $order_fare_calculation = new OrderFareCalculation();
                    $order_fare_calculation->order_id = $order_details->id;
                    $order_fare_calculation->fixed_fees = $fixed_fees;
                    $order_fare_calculation->no_show_fee_driver = $no_show_fees;
                    $order_fare_calculation->total_fare_estimation = $charge_amount;
                    $order_fare_calculation->save();

                    $order_details->total_amount = $charge_amount;
                    $order_details->save();


                    $arrCustWalletAmt = array();
                    $userBalance = GlobalValues::userBalance($order_details->driver_id);
                    $arrCustWalletAmt['user_id'] = $order_details->driver_id;
                    $arrCustWalletAmt['order_id'] = $order_details->id;
                    $arrCustWalletAmt['transaction_amount'] = $charge_amount;
                    $arrCustWalletAmt['final_amout'] = $charge_amount;
                    $arrCustWalletAmt['avl_balance'] = $userBalance - $charge_amount;
                    $arrCustWalletAmt['trans_desc'] = $order_message;
                    $arrCustWalletAmt['flag'] = '6';
                    $arrCustWalletAmt['transaction_type'] = '1';
                    $arrCustWalletAmt['payment_type'] = '2';
                    UserWalletDetail::create($arrCustWalletAmt);
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.status_updated', "", [], $locale));
                    //do to calcuation for no show off
                } elseif ($calculate_flag == '5') {
                    $order_details->status_by_driver = $status;
                    $order_details->save();
                    $distance = ((double) $distance / 1000);
                    if (($order_details->arrived_for_pickup_date_time != "") && ($order_details->getOrderTransInformation->end_date_time != "")) {
                        $arrvied_date_time = new DateTime($order_details->arrived_for_pickup_date_time);
                        $end_Date_time = new DateTime($order_details->getOrderTransInformation->end_date_time);
                        $diffdate = date_diff($arrvied_date_time, $end_Date_time);
                        $pick_up_waiting_time = (($diffdate->i) + (($diffdate->s) / 60));
                    }
                    $order_wait_time_diff2 = MiddleLocation::where('order_id', $order_details->id)->where('status', 2)->get();
                    if (isset($order_wait_time_diff2) && count($order_wait_time_diff2) > 0) {
                        foreach ($order_wait_time_diff2 as $diff2) {
                            $date2 = new DateTime($diff2->reached_at);
                            $date1 = new DateTime($diff2->leave_at);
                            $diffdate = date_diff($date1, $date2);
                            $middel_location_wating_time += (($diffdate->i) + (($diffdate->s) / 60));
                            $middle_location_address[] = $diff2->address;
                            $middle_location_pickup_waiting_time[] = $date1->diff($date2)->format('%H:%I:%S');
                        }
                    }
                    $pre_ride_driving_distance = $order_details->getOrderTransInformation->pre_ride_driving_distance;
                    $arrDataService = CountryZoneService::where('zone_id', $order_details->zone_id)->first();
                    $final_net_amount = 0.000;
                    $discount_amount = 0.000;
                    if (isset($arrDataService) && count($arrDataService) > 0) {
                        $fare_amount = (double) $arrDataService->fixed_fees + (double) $arrDataService->ride_starting_fees;
                        $pre_ride_driving_rate = 0.000;
                        $pre_ride_waiting_fee = 0.000;
                        $ride_driver_rate = 0.000;
                        $ride_waiting_rate = 0.000;

                        if ($arrDataService->pre_ride_driving_fees > 0) {
                            $pre_ride_driving_rate = (double) ($pre_ride_driving_distance) * $arrDataService->pre_ride_driving_fees;
                            $fare_amount = $fare_amount + $pre_ride_driving_rate;
                        }
                        if ($arrDataService->pre_ride_waiting_fees > 0) {
                            $pre_ride_waiting_fee = (double) ($pick_up_waiting_time) * $arrDataService->pre_ride_waiting_fees;
                            $fare_amount = $fare_amount + $pre_ride_waiting_fee;
                        }
                        if ($arrDataService->ride_driving_rate > 0) {
                            $ride_driver_rate = (double) ($distance) * $arrDataService->ride_driving_rate;
                            $fare_amount = $fare_amount + $ride_driver_rate;
                        }
                        if ($arrDataService->ride_waiting_rate > 0) {
                            $ride_waiting_rate = (double) ($middel_location_wating_time) * $arrDataService->ride_waiting_rate;
                            $fare_amount = $fare_amount + $ride_waiting_rate;
                        }
                    }
                    $arrWalletAmt = array();
                    $coupon_detail = Coupon::where(['id' => $order_details->getOrderTransInformation->coupon_id, 'status' => 1])->first();
                    if (isset($coupon_detail) && count($coupon_detail) > 0) {
                        $type = $coupon_detail->type;
                        $discount_amount = $fare_amount * ($coupon_detail->discount / 100);
                        if ($discount_amount > $coupon_detail->max_discount) {
                            $discount_amount = $fare_amount * ($coupon_detail->max_discount / 100);
                        }
                        $coupon_type = "";
                        if ($coupon_detail->coupon_type == '1') {
                            $coupon_type = "Seasonal";
                        } else {
                            $coupon_type = "Promotion";
                        }
                        if ($coupon_detail->type == 1) {
                            $fare_amount = $fare_amount - $discount_amount;
                            $arrWalletAmt['text'] = "Coupon Name - " . $coupon_type;
                            $arrWalletAmt['discount_type'] = '1';
                            $arrWalletAmt['discount_amount'] = $coupon_detail->discount;
                        } else {
                            $flag = 1;
                            $arrWalletAmt['text'] = "Coupon Name - " . $coupon_type;
                            $arrWalletAmt['discount_type'] = '2';
                            $arrWalletAmt['discount_amount'] = $coupon_detail->discount;
                        }
                    }
                    $final_net_amount = $fare_amount;
                    $order_details->status = 2;
                    $order_details->total_amount = $final_net_amount;
                    $order_details->order_complete_date_time = $date2;
                    $order_details->save();
                    //get trip duration
                    if (!empty($order_details->on_my_way_date_time)) {
                        $trip_duration = $this->diffInMinutes($order_details->on_my_way_date_time, $date2_val);
                        $order_details->trip_duration = $trip_duration . ' ' . 'Min';
                        $order_details->save();
                    }
                    $order_details->getOrderTransInformation->discount = $discount_amount;
                    $order_details->getOrderTransInformation->type = $type;
                    $order_details->getOrderTransInformation->ride_final_distance = $distance;
                    $order_details->getOrderTransInformation->required_time_for_pickup = $required_time_for_pickup;
                    $order_details->getOrderTransInformation->save();
                    //insert data in order fare calculation                                                                            
                    $order_fare_calculation = new OrderFareCalculation();
                    $order_fare_calculation->order_id = $order_details->id;
                    $order_fare_calculation->driver_id = $order_details->driver_id;
                    $order_fare_calculation->fixed_fees = isset($arrDataService) ? $arrDataService->fixed_fees : 0.000;
                    $order_fare_calculation->ride_starting_fees = isset($arrDataService) ? $arrDataService->ride_starting_fees : 0.000;
                    $order_fare_calculation->pre_ride_driving_fees = $pre_ride_driving_rate;
                    $order_fare_calculation->pre_ride_waiting_fees = $pre_ride_waiting_fee;
                    $order_fare_calculation->ride_driving_rate = $ride_driver_rate;
                    $order_fare_calculation->ride_waiting_rate = $ride_waiting_rate;
                    $order_fare_calculation->ride_waiting_time = $middel_location_wating_time;
                    $order_fare_calculation->pre_ride_waiting_time = $pick_up_waiting_time;
                    $order_fare_calculation->total_fare_estimation = $final_net_amount;
                    $order_fare_calculation->status = 1;
                    $order_fare_calculation->save();
                    $fixed_fees = $order_fare_calculation->fixed_fees;
                    //driver get credit when user complete first ride
                    $this->getCreditOnReferralCodeToCustomer($order_details->customer_id, $locale);
                    $this->getCreditOnReferralCodeToDriver($order_details->driver_id, $locale);
                    //update customer wallet details     
                    if ($order_details->payment_type == '2') {
                        //update cashbag status
                        $cash_bag_for_last_ride_details = DB::table('orders')
                                ->join('orders_informations', function($join) {
                                    $join->on('orders.id', '=', 'orders_informations.order_id');
                                })
                                ->where('orders_informations.type', '=', 0)
                                ->where('orders.customer_id', '=', $order_details->customer_id)
                                ->where('orders.status', '=', 2)
                                ->where('orders.used_cashback', '=', 0)
                                ->orderBy('orders.id', 'DESC')
                                ->first();
                        if (isset($cash_bag_for_last_ride_details) && count($cash_bag_for_last_ride_details) > 0) {
                            DB::table('orders')
                                    ->where('orders.id', '=', $cash_bag_for_last_ride_details->id)
                                    ->update(array('used_cashback' => '1'));
                        }

                        $userBalance = GlobalValues::userBalance($order_details->customer_id);
                        $arrWalletAmt['user_id'] = $order_details->customer_id;
                        $arrWalletAmt['order_id'] = $order_details->id;
                        $arrWalletAmt['transaction_amount'] = $fare_amount;
                        $arrWalletAmt['final_amout'] = $final_net_amount;
                        $arrWalletAmt['avl_balance'] = $userBalance - $final_net_amount;
                        $arrWalletAmt['trans_desc'] = Lang::choice('messages.order_completed', "", [], $locale);
                        $arrWalletAmt['transaction_type'] = '1';
                        $arrWalletAmt['flag'] = '3';
                        $arrWalletAmt['payment_type'] = '2';
                        UserWalletDetail::create($arrWalletAmt);
                        //get cashbag on discount
                        if ($flag == 1) {
                            $arrWalletAmt['user_id'] = $order_details->customer_id;
                            $arrWalletAmt['order_id'] = $order_details->id;
                            $arrWalletAmt['transaction_amount'] = $discount_amount;
                            $arrWalletAmt['final_amout'] = $discount_amount;
                            $arrWalletAmt['avl_balance'] = $userBalance + $discount_amount;
                            $arrWalletAmt['trans_desc'] = Lang::choice('messages.passenger_cashbag_on_promotion', "", [], $locale);
                            $arrWalletAmt['transaction_type'] = '0';
                            $arrWalletAmt['flag'] = '7';
                            $arrWalletAmt['payment_type'] = '2';
                            UserWalletDetail::create($arrWalletAmt);
                        }

                        //update driver wallet details                 
                        $arrDriverWalletAmt = array();
                        $driverBalance = GlobalValues::userBalance($order_details->driver_id);
                        $arrDriverWalletAmt['user_id'] = $order_details->driver_id;
                        $arrDriverWalletAmt['order_id'] = $order_details->id;
                        $arrDriverWalletAmt['transaction_amount'] = $fare_amount;
                        $arrDriverWalletAmt['final_amout'] = $final_net_amount;
                        $arrDriverWalletAmt['avl_balance'] = $driverBalance + $final_net_amount;
                        $arrDriverWalletAmt['trans_desc'] = Lang::choice('messages.order_completed', "", [], $locale);
                        $arrDriverWalletAmt['transaction_type'] = '0';
                        $arrDriverWalletAmt['flag'] = '3';
                        $arrDriverWalletAmt['payment_type'] = '2';
                        UserWalletDetail::create($arrDriverWalletAmt);
                    } elseif ($order_details->payment_type == '3') {
                        $arrDriverWalletAmt = array();
                        $driverBalance = GlobalValues::userBalance($order_details->driver_id);
                        $arrDriverWalletAmt['user_id'] = $order_details->driver_id;
                        $arrDriverWalletAmt['order_id'] = $order_details->id;
                        $arrDriverWalletAmt['transaction_amount'] = $fixed_fees;
                        $arrDriverWalletAmt['final_amout'] = $fixed_fees;
                        $arrDriverWalletAmt['avl_balance'] = $driverBalance - $fixed_fees;
                        $arrDriverWalletAmt['trans_desc'] = Lang::choice('messages.order_completed', "", [], $locale);
                        $arrDriverWalletAmt['transaction_type'] = '1';
                        $arrDriverWalletAmt['flag'] = '3';
                        $arrDriverWalletAmt['payment_type'] = '2';
                        UserWalletDetail::create($arrDriverWalletAmt);
                    }


                    //Get todays's total fare amount for driver.
                    $today_total_fare_amount = OrderFareCalculation::where(['driver_id' => $order_details->driver_id, 'status' => 1])->where('created_at', '>=', Carbon::today())->where('created_at', '<=', Carbon::now()->format('Y-m-d') . " 23:59:00")->sum('total_fare_estimation');

                    $order_fare_details = [
                        'fixed_fees' => isset($arrDataService) ? $arrDataService->fixed_fees : 0.00,
                        'ride_starting_fees' => isset($arrDataService) ? $arrDataService->ride_starting_fees : 0.00,
                        'pre_ride_driving_fees' => isset($pre_ride_driving_rate) ? $pre_ride_driving_rate : 0.00,
                        'pre_ride_waiting_fees' => isset($pre_ride_waiting_fee) ? $pre_ride_waiting_fee : 0.00,
                        'ride_driving_rate' => isset($ride_driver_rate) ? $ride_driver_rate : 0.00,
                        'ride_waiting_rate' => isset($ride_waiting_rate) ? $ride_waiting_rate : 0.00,
                        'discount_amount' => isset($discount_amount) ? $discount_amount : 0.00,
                        'total_fare_estimation' => $final_net_amount,
                        'no_show_fee_passenger' => 0.00,
                        'no_show_fee_driver' => 0.00,
                        'cancellation_fee' => 0.00,
                        'today_total_fare_estimation' => isset($today_total_fare_amount) ? $today_total_fare_amount : 0.00
                    ];

                    //create mail for customer
                    $customer_details = User::where('id', $order_details->customer_id)->first();

                    //create mail for driver
                    $driver_details = User::where('id', $order_details->driver_id)->first();
                    if (isset($customer_details) && isset($driver_details)) {
                        $site_email = GlobalValues::get('site-email');
                        $site_title = GlobalValues::get('site-title');
                        $arr_keyword_values = array();
                        //Assign values to all macros
                        $starting_fees = (isset($arrDataService->fixed_fees) ? $arrDataService->fixed_fees : 0.00) + (isset($arrDataService->ride_starting_fees) ? $arrDataService->ride_starting_fees : 0.00);
                        $moving_fees = (isset($pre_ride_driving_rate) ? $pre_ride_driving_rate : 0.00) + (isset($ride_driver_rate) ? $ride_driver_rate : 0.00);
                        $initial_waiting_fees = isset($pre_ride_waiting_fee) ? $pre_ride_waiting_fee : 0.00;
                        $journey_waiting_fees = isset($ride_waiting_rate) ? $ride_waiting_rate : 0.00;
                        $coupon_amt = isset($discount_amount) ? $discount_amount : 0.00;
                        $currency = $this->getUserCurrencyCode($customer_details->id);

                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        $arr_keyword_values['CUSTOMER_FIRST_NAME'] = isset($customer_details->userInformation->first_name) ? $customer_details->userInformation->first_name : '';
                        $arr_keyword_values['CUSTOMER_LAST_NAME'] = isset($customer_details->userInformation->last_name) ? $customer_details->userInformation->last_name : '';
                        $arr_keyword_values['DRIVER_FIRST_NAME'] = isset($driver_details->userInformation->first_name) ? $driver_details->userInformation->first_name : '';
                        $arr_keyword_values['DRIVER_LAST_NAME'] = isset($driver_details->userInformation->last_name) ? $driver_details->userInformation->last_name : '';
                        $arr_keyword_values['ORDER_ID'] = isset($order_details->id) ? $order_details->id : '';
                        $arr_keyword_values['PICKUP_AREA'] = isset($order_details->getOrderTransInformation->pickup_area) ? $order_details->getOrderTransInformation->pickup_area : '';
                        $arr_keyword_values['DROP_AREA'] = isset($order_details->getOrderTransInformation->drop_area) ? $order_details->getOrderTransInformation->drop_area : '';
                        $arr_keyword_values['ORDER_NUMBER'] = isset($order_details->order_unique_id) ? $order_details->order_unique_id : '';
                        $arr_keyword_values['FIXED_FEES'] = isset($arrDataService->fixed_fees) ? $currency . ' ' . $this->changeNumberFormat($arrDataService->fixed_fees) : '';
                        $arr_keyword_values['RIDE_STARTING_FEES'] = isset($arrDataService->ride_starting_fees) ? $currency . ' ' . $this->changeNumberFormat($arrDataService->ride_starting_fees) : '';
                        $arr_keyword_values['PRE_RIDE_DRIVING_FEES'] = isset($pre_ride_driving_rate) ? $currency . ' ' . $this->changeNumberFormat($pre_ride_driving_rate) : '';
                        $arr_keyword_values['PRE_RIDE_WAITING_FEES'] = isset($pre_ride_waiting_fee) ? $currency . ' ' . $this->changeNumberFormat($pre_ride_waiting_fee) : '';
                        $arr_keyword_values['RIDE_DRIVING_RATE'] = isset($ride_driver_rate) ? $currency . ' ' . $this->changeNumberFormat($ride_driver_rate) : '';
                        $arr_keyword_values['RIDE_WAITING_RATE'] = isset($ride_waiting_rate) ? $currency . ' ' . $this->changeNumberFormat($ride_waiting_rate) : '';
                        $arr_keyword_values['TOTAL_TRIP_AMOUNT'] = $currency . ' ' . $this->changeNumberFormat($final_net_amount);
                        $arr_keyword_values['middle_location_address'] = $middle_location_address;
                        $arr_keyword_values['middle_location_waiting_time'] = $middel_location_wating_time;
                        $arr_keyword_values['STARTING'] = $starting_fees;
                        $arr_keyword_values['MOVING'] = $moving_fees;
                        $arr_keyword_values['INITIAL_WAITING_PRICE'] = $currency . ' ' . $this->changeNumberFormat($initial_waiting_fees);
                        $arr_keyword_values['JOURNEY_WAITING_PRICE'] = $currency . ' ' . $this->changeNumberFormat($journey_waiting_fees);
                        $arr_keyword_values['COUPON_AMT'] = $currency . ' ' . $this->changeNumberFormat($coupon_amt);
                        $email_subject = Lang::choice('messages.trip_complete_fare_details_email', "", [], $locale);
                    }
                    // $status = "save";
                    //$res = \App::call('App\PiplModules\orderdetails\Controllers\OrderController@downloadOrderPdf', [$order_details->id, $status]);
                    //if ($res) {
                    if (isset($customer_details)) {
                        // $filename = "Order_" . $order_details->order_unique_id . "_invoice_1.pdf";
                        //$file_path = base_path() . '/' . 'storage/app/public/order-document' . '/' . $filename;
                        // if (file_exists($file_path)) {
                        $tempate_name = "emailtemplate::trip-fare-estimation-" . $locale;
                        $customer_email = $customer_details->email;
                        $status = @Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($customer_email, $email_subject, $site_email, $site_title) {
                                    $message->to($customer_email)->subject($email_subject)->from($site_email, $site_title);
                                });
                        //}
                    }
                    // }
                    if (isset($driver_details)) {
                        $temp_name = "emailtemplate::trip-fare-estimation-for-driver-" . $locale;
                        $driver_email = $driver_details->email;
                        @Mail::send($temp_name, $arr_keyword_values, function ($message) use ($driver_email, $email_subject, $site_email, $site_title) {
                                    $message->to($driver_email)->subject($email_subject);
                                });
                    }

                    //notification for customer
                    $notification_customer_time = $date2_val;
                    $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . GlobalValues::get('driver-reject-time') . ' min'));
                    $order_message = Lang::choice('messages.order_completed', "", [], $locale);
                    $data = ['notification_customer_time' => $notification_customer_time, 'notification_end_time' => $notification_end_time, 'order_message' => $order_message, 'order_details' => $order_details, 'date2' => $date2];
                    $this->sendNoificationForOrderStatusChange($data);

                    //notification for driver
                    $site_title = GlobalValues::get('site-title');
                    $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $order_message, "flag" => 'ride_status_updated', 'message' => $order_message, 'order_id' => $order_details->id);

                    $available_driver_details = UserInformation::where('user_id', $order_details->driver_id)->first();
                    if (isset($available_driver_details)) {
                        $notiMsg = $order_message;
                        $notiMsg = str_replace("%%CUSTOMER_NAME%%", $available_driver_details->first_name . " " . $available_driver_details->last_name, $notiMsg);
                        $notiMsg = str_replace("%%ORDER_NUMBER%%", $order_details->order_unique_id, $notiMsg);
                        $notiMsg = str_replace("%%DATE_TIME%%", $notification_customer_time, $notiMsg);
                        $saveNotification = new AppNotification();
                        $saveNotification->saveNotification($available_driver_details->user_id, $order_details->id, $order_message, $notiMsg, $notification_customer_time, 0, 'order');

                        if (isset($available_driver_details->device_id) && $available_driver_details->device_id != '') {
                            $obj_send_push_notification = new SendPushNotification();
                            if ($available_driver_details->device_type == '0') {
                                //sending push notification customer user.               
                                $arr_push_message_android = [];
                                $arr_push_message_android = ["data" => ['body' => $order_message, 'title' => $site_title, "flag" => 'ride_status_updated', 'order_id' => $order_details->id], 'notification' => ['body' => $order_message, 'title' => $site_title]];
                                $obj_send_push_notification->androidPushNotification($arr_push_message_android, $available_driver_details->device_id, $available_driver_details->user_type);
                            } else {
                                $arr_push_message_ios = array();
                                $arr_push_message_ios['to'] = $available_driver_details->device_id;
                                $arr_push_message_ios['priority'] = "high";
                                $arr_push_message_ios['sound'] = "iOSSound.wav";
                                $arr_push_message_ios['notification'] = $arr_push_message;
                                $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                            }
                        }
                    }

                    $arr_to_return = array("error_code" => 0, "order_fare_details" => $order_fare_details, "pick_up_waiting_time" => $pick_up_waiting_time, "middel_location_wating_time" => $middel_location_wating_time, "order_amount" => $fare_amount, "msg" => Lang::choice('messages.status_updated', "", [], $locale));
                }
            } else {

                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_invalid', "", [], $locale));
            }
        } else {

            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.order_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function changeDriverCivilId(Request $request) {
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $new_civil_id = isset($request['new_civil_id']) ? $request['new_civil_id'] : '';
        $old_civil_id = isset($request['old_civil_id']) ? $request['old_civil_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $arr_user_data = UserInformation::where('user_id', $driver_id)->where('civil_id', $old_civil_id)->first();
        \App::setLocale($locale);
        if (isset($arr_user_data) && count($arr_user_data) > 0) {
            $check_civil_user_data = UserInformation::where('civil_id', $new_civil_id)->first();
            if (isset($check_civil_user_data)) {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.old_civil_id_does_not_match', "", [], $locale));
                return response()->json($arr_to_return);
            }
            $arr_user_data->civil_id = $new_civil_id;
            $arr_user_data->save();
            $arr_to_return = array("error_code" => 0, "civil id" => $new_civil_id, "msg" => Lang::choice('messages.civil_id_has_been_changed', "", [], $locale));
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.old_civil_id_does_not_match', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function changeDriverDocumentStatus() {
        $today_date = date("Y-m-d", strtotime('-1 day'));
        $locale = config("app.locale");
        $site_title = GlobalValues::get('site-title');
        $DriverDocumentinfo = DriverDocumentInformation::get();
        foreach ($DriverDocumentinfo as $doc_info) {
            if ($doc_info->expiry_date == $today_date) {
                Notification::where('user_id', $doc_info->user_id)->where('redirect_flag', 'document_expiry')->delete();
                $arr_user_data = UserInformation::where('user_id', $doc_info->user_id)->first();
                if (isset($arr_user_data) && count($arr_user_data) > 0) {
                    $msg = $doc_info->document_name;
                    $msg .= ' ' . Lang::choice('messages.expired_document', "", [], $locale);
                    $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $msg, "flag" => 'expired_document', 'message' => $msg);
                    $arr_push_message_ios = array();
                    $obj_send_push_notification = new SendPushNotification();
                    if ($arr_user_data->device_type == '0') {
                        $user_type = $arr_user_data->user_type;
                        //sending push notification driver user.
                        $arr_push_message_android = ["data" => ['body' => $msg, 'title' => $site_title, "flag" => 'expired_document', 'order_id' => ''], 'notification' => ['body' => $msg, 'title' => $site_title]];
                        $obj_send_push_notification->androidPushNotification($arr_push_message_android, $arr_user_data->device_id, $arr_user_data->user_type);
                    } else {
                        $user_type = $arr_user_data->user_type;
                        $arr_push_message_ios['to'] = $arr_user_data->device_id;
                        $arr_push_message_ios['priority'] = "high";
                        $arr_push_message_ios['sound'] = "iOSSound.wav";
                        $arr_push_message_ios['notification'] = $arr_push_message;
                        $obj_send_push_notification->iosPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                    }
                    $doc_info->status = 3;
                    $doc_info->save();

                    $notiMsg = $msg;
                    $notiMsg = str_replace("%%DRIVER_NAME%%", $arr_user_data->first_name . " " . $arr_user_data->last_name, $notiMsg);
                    $notiMsg = str_replace("%%DATE_TIME%%", date("Y-m-d H:i:s"), $notiMsg);
                    $saveNotification = new AppNotification();
                    $saveNotification->saveNotification($doc_info->user_id, 0, $msg, $notiMsg, date("Y-m-d"), 0, 'document_expiry');
                    $arr_user_data->user_status = 3;
                    $arr_user_data->save();



                    $usr_suspended_reason = new UserSuspendedReason();
                    $usr_suspended_reason->user_id = $arr_user_data->user_id;
                    $usr_suspended_reason->reason = $msg;
                    $usr_suspended_reason->save();
                }
            }
        }
    }

    public function driverDocumentExpiryDaysNotification() {
        $site_title = GlobalValues::get('site-title');
        $today_date = date("Y-m-d");
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $driverDocumentinfo = DriverDocumentInformation::where('status', 1)->get();
        if (isset($driverDocumentinfo) && count($driverDocumentinfo)) {
            foreach ($driverDocumentinfo as $doc_info) {
                $dateDiff = (int) $this->dateDiff($today_date, $doc_info->expiry_date);
                if (($dateDiff == 30) || ($dateDiff == 15) || ($dateDiff == 7) || ($dateDiff == 5) || ($dateDiff == 2) || ($dateDiff == 1)) {

                    Notification::where('user_id', $doc_info->user_id)->where('redirect_flag', 'document_expiry')->delete();

                    $available_driver_details = UserInformation::where('user_id', $doc_info->user_id)->first();
                    $existDriverDoc = Document::where('id', $doc_info->document_id)->first();
                    if (isset($available_driver_details->device_id) && $available_driver_details->device_id != '') {
                        $msg = $existDriverDoc->document_name;
                        $msg .= ' ' . Lang::choice('messages.expired_document_within_days', "", [], $locale);
                        $msg .= ' ' . $dateDiff . ' days';
                        $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $msg, "flag" => 'expired_document', 'message' => $msg);
                        $arr_push_message_ios = array();
                        $obj_send_push_notification = new SendPushNotification();
                        if ($available_driver_details->device_type == '0') {
                            $user_type = $available_driver_details->user_type;
                            //sending push notification driver user.                           
                            $arr_push_message_android = ["data" => ['body' => $msg, 'title' => $site_title, "flag" => 'expired_document', 'order_id' => ''], 'notification' => ['body' => $msg, 'title' => $site_title]];
                            $obj_send_push_notification->androidPushNotification($arr_push_message_android, $available_driver_details->device_id, $available_driver_details->user_type);
                        } else {
                            $user_type = $available_driver_details->user_type;
                            $arr_push_message_ios['to'] = $available_driver_details->device_id;
                            $arr_push_message_ios['priority'] = "high";
                            $arr_push_message_ios['sound'] = "iOSSound.wav";
                            $arr_push_message_ios['notification'] = $arr_push_message;
                            $obj_send_push_notification->iosPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                        }
                        $doc_info->status = 3;
                        $doc_info->save();
                        $exist_driver = User::where('id', $doc_info->user_id)->first();
                        if (isset($exist_driver) && count($exist_driver) > 0) {
                            $notiMsg = $msg;
                            $notiMsg = str_replace("%%DRIVER_NAME%%", $exist_driver->userInformation->first_name . " " . $exist_driver->userInformation->last_name, $notiMsg);
                            $notiMsg = str_replace("%%DATE_TIME%%", date("Y-m-d H:i:s"), $notiMsg);
                            $saveNotification = new AppNotification();
                            $saveNotification->saveNotification($doc_info->user_id, 0, $msg, $notiMsg, date("Y-m-d"), 0, 'document_expiry');
                        }
                    }
                }
            }
        }
    }

    public function getSubscriptionPlanDetail(Request $request) {
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        dd($driver_id);
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $arr_to_return = array();
        $driver_sub_plan = array();
        \App::setLocale($locale);
        $driverSubscriptionPlan = SubscriptionPlanForDriverDetail::where('driver_id', $driver_id)->get();
        $driver_status = UserInformation::where('user_type', 2)->where('user_id', $driver_id)->first(['user_status']);

        if (isset($driverSubscriptionPlan) && count($driverSubscriptionPlan) > 0) {
            $i = 0;
            foreach ($driverSubscriptionPlan as $serviceplan) {
                $exist_subscription_plan = SubscriptionPlanDetail::WHERE('id', $serviceplan->subscription_plan_detail_id)->first();
                //get subscription plan name
                $plan_name = DB::table('subscription_plans')
                        ->join('subscription_plan_translations', function ($join) use ($locale) {
                            $join->on('subscription_plan_translations.subscription_plan_id', '=', 'subscription_plans.id');
                            $join->where('subscription_plan_translations.locale', '=', $locale);
                        })
                        ->where('subscription_plans.id', '=', $exist_subscription_plan->subscription_plan_id)
                        ->first();
                $driver_sub_plan[$i] = $serviceplan;
                $driver_sub_plan[$i]['plan'] = $plan_name->name;
                $driver_sub_plan[$i]['exist_subscription_plan_detail_id'] = $exist_subscription_plan->subscriptionPlan->id;
                $driver_sub_plan[$i]['user_status'] = $driver_status->user_status;
                $i++;
            }

            $arr_to_return = array("error_code" => 0, "driver_subscription_plan_detail" => $driver_sub_plan);
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.subscription_plan_not_available', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

//    public function expiredSubscriptionPlan(Request $request) {
//        $arr_to_return = array();
//        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
//        $locale = isset($request['locale']) ? $request['locale'] : 'en';
//
//        $today_date = date("Y-m-d");
//        $expiry_date = "";
//        $subscriptionPlan = SubscriptionPlanForDriverDetail::where('driver_id', $driver_id)->where('expiry_date', '>=', $today_date)->first();
//        if (isset($subscriptionPlan) && count($subscriptionPlan) > 0) {
//            $expiry_date = $subscriptionPlan->expiry_date;
//            $arr_to_return = array("error_code" => 0, "expiry date" => $expiry_date, "msg" => Lang::choice('messages.service_plan_expiry_Date',"",[],$locale));
//            return response()->json($arr_to_return);
//        } else {
//            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.subscription_plan_not_available',"",[],$locale));
//            return response()->json($arr_to_return);
//        }
//    }

    public function testSMS() {
        $locale = 'en';

        $mobile_number_to_send = "+" . 918668916247;
        $message = "";

        //sending sms to customer
        $trackURL = url('/') . '/order-track/recievered-order-track/329';
        $message = Lang::choice('messages.customer_sms_order_accepted', 'en') . " " . 'Arvind';
        $message .= "\n";
        $message .= Lang::choice('messages.driver_number_msg', 'en') . " +918668752430";
        $message .= "Track your order: " . $trackURL;

        $obj_sms = new SendSms();
        $obj_sms->sendMessage($mobile_number_to_send, $message);
    }

    public function getDriverUserCurrentOrder(Request $request) {
        $user_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        \App::setLocale($locale);
        $arrOrderDetails = array();
        $arrOrderDetailsAssigned = array();
        if ($user_id > 0 && $user_id != '') {
            $arr_orders = Order::where('driver_id', $user_id)->where('status', '1')->get();

            $arr_orders = $arr_orders->sortByDesc('created_at');
            if (count($arr_orders) > 0) {
                //storing all order details
                if (count($arr_orders)) {
                    foreach ($arr_orders as $order) {
                        $arrOrderDetails['order'] = $order;
                        // getting order details.

                        $region_info = CountryZoneService::where(['service_id' => $order->service_id, 'zone_id' => $order->zone_id])->first();
                        if (isset($region_info) && count($region_info) > 0) {
                            $arrOrderDetails['region_details'] = $region_info;
                        }
                        $arrOrderDetails['order_details'] = $order->getOrderTransInformation;
                        $arrOrderDetails['customer_details'] = $order->getUserCustomerInformation;
                        //get customer rating
                        $avg_passenger_rating = 0;
                        $userRating = UserRatingInformation::where('to_id', $order->customer_id)->where('status', '1')->avg('rating');
                        $avg_passenger_rating = isset($userRating) ? round($userRating, 2) : '0';
                        $arrOrderDetails['customer_details']['avg_passenger_rating'] = $avg_passenger_rating;

                        $arrOrderDetails['service'] = $order->getServicesDetails->name;
                        $arrOrderDetails['middle_location'] = $order->getMiddleLocation;
                        if (isset($order->getServicesDetails)) {
                            $service = $order->getServicesDetails;
                            $arrOrderDetails['category'] = $service->categoryInfo->name;
                        }
                    }
                }
                $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.order_listing_success', "", [], $locale), "order_details" => $arrOrderDetails);
            } else {
                $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.current_orders_not_exist', "", [], $locale));
            }
        } else {
            $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.driver_invalid', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function addSubscriptionPlanDaysForDriver(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $arrSubscriptionPlan = array();
        $subscriptiondriver = array();
        $arr_to_return = array();
        \App::setLocale($locale);
        $subscription_plan_id = isset($request['subscription_plan_id']) ? $request['subscription_plan_id'] : '';
        $service_id = isset($request['service_id']) ? $request['service_id'] : '';

        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $subscription_plan_id)->where('service_id', $service_id)->where('status', 1)->first();
        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
            $pendingDriverSubscriptionPlan = SubscriptionPlanForDriverDetail::where('driver_id', $driver_id)->where('status', 2)->first();
            if (isset($pendingDriverSubscriptionPlan) && count($pendingDriverSubscriptionPlan) > 0) {
                $arr_to_return = array("error_code" => 2, "msg" => Lang::choice('messages.already_exist_subscription_plan', "", [], $locale));
            } else {
                $activeDriverSubscriptionPlan = SubscriptionPlanForDriverDetail::where('driver_id', $driver_id)->where('status', 1)->first();

                if (isset($activeDriverSubscriptionPlan) && count($activeDriverSubscriptionPlan) > 0) {
                    //when driver user is suspended
                    $suspended_driver = UserInformation::where("user_mobile", $activeDriverSubscriptionPlan->driver_id)->where('user_status', 3)->first();
                    if (isset($suspended_driver) && count($suspended_driver) > 0) {
                        $DriverExpiredDocument = DriverDocumentInformation::where('user_id', $driver_id)->where('status', 3)->first();
                        $msg = Lang::choice('messages.add_plan_for_account_suspended', "", [], $locale);
                        $msg .= ' ' . $DriverExpiredDocument->document_name;
                        $msg .= ' ' . Lang::choice('messages.expired_document', "", [], $locale);
                        $arr_to_return = array("error_code" => 2, "msg" => $msg);
                    } else {
                        if ($subscription_plan_Detail->subscriptionPlan->slug == "Monthly") {
                            $expiry_date = $activeDriverSubscriptionPlan->expiry_date;
                            $start_date = new DateTime($expiry_date);
                            $end_date = new DateTime($expiry_date);
                            $start_date->modify('first day of next month');
                            $end_date->modify('last day of next month');
                            $arrSubscriptionPlan['expiry_date'] = $end_date->format('Y-m-d');
                            $arrSubscriptionPlan['start_date'] = $start_date->format('Y-m-d');
                            $arrSubscriptionPlan['driver_id'] = $driver_id;
                            $arrSubscriptionPlan['status'] = 2;
                            $arrSubscriptionPlan['subscription_plan_detail_id'] = $subscription_plan_Detail->id;
                            $subscriptiondriver = SubscriptionPlanForDriverDetail::create($arrSubscriptionPlan);
                        } elseif ($subscription_plan_Detail->subscriptionPlan->slug == "Quaterly") {
                            $prev_expiry_date = $activeDriverSubscriptionPlan->expiry_date;

                            $start_date = new DateTime($prev_expiry_date);
                            $start_date->modify('first day of next month');

                            $end_date = date('Y-m-d', strtotime("+3 months", strtotime($start_date->format('Y-m-d'))));
                            $expiry_date = date('Y-m-d', strtotime('-1 day', strtotime($end_date)));
                            $arrSubscriptionPlan['start_date'] = $start_date->format('Y-m-d');
                            $arrSubscriptionPlan['expiry_date'] = $expiry_date;
                            $arrSubscriptionPlan['driver_id'] = $driver_id;
                            $arrSubscriptionPlan['status'] = 2;
                            $arrSubscriptionPlan['subscription_plan_detail_id'] = $subscription_plan_Detail->id;
                            $subscriptiondriver = SubscriptionPlanForDriverDetail::create($arrSubscriptionPlan);
                        } elseif ($subscription_plan_Detail->subscriptionPlan->slug == "Half Yearly") {
                            $prev_expiry_date = $activeDriverSubscriptionPlan->expiry_date;

                            $start_date = new DateTime($prev_expiry_date);
                            $start_date->modify('first day of next month');

                            $end_date = date('Y-m-d', strtotime("+6 months", strtotime($start_date->format('Y-m-d'))));
                            $expiry_date = date('Y-m-d', strtotime('-1 day', strtotime($end_date)));

                            $arrSubscriptionPlan['start_date'] = $start_date->format('Y-m-d');
                            $arrSubscriptionPlan['expiry_date'] = $expiry_date;
                            $arrSubscriptionPlan['driver_id'] = $driver_id;
                            $arrSubscriptionPlan['status'] = 2;
                            $arrSubscriptionPlan['subscription_plan_detail_id'] = $subscription_plan_Detail->id;
                            $subscriptiondriver = SubscriptionPlanForDriverDetail::create($arrSubscriptionPlan);
                        } elseif ($subscription_plan_Detail->subscriptionPlan->slug == "Yearly") {
                            $prev_expiry_date = $activeDriverSubscriptionPlan->expiry_date;

                            $start_date = new DateTime($prev_expiry_date);
                            $start_date->modify('first day of next month');

                            $end_date = date('Y-m-d', strtotime("+12 months", strtotime($start_date->format('Y-m-d'))));
                            $expiry_date = date('Y-m-d', strtotime('-1 day', strtotime($end_date)));

                            $arrSubscriptionPlan['start_date'] = $start_date->format('Y-m-d');
                            $arrSubscriptionPlan['expiry_date'] = $expiry_date;
                            $arrSubscriptionPlan['driver_id'] = $driver_id;
                            $arrSubscriptionPlan['status'] = 2;
                            $arrSubscriptionPlan['subscription_plan_detail_id'] = $subscription_plan_Detail->id;
                            $subscriptiondriver = SubscriptionPlanForDriverDetail::create($arrSubscriptionPlan);
                        }
                        $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.add_subscription_plan_expiry_Date', "", [], $locale), "subscription_plan_detail" => $subscriptiondriver);
                    }
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.subscription_plan_not_available', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

    public function pauseSubscriptionPlanDays(Request $request) {
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $driver_id = isset($request['driver_id']) ? $request['driver_id'] : '';
        $to_date = isset($request['to_date']) ? $request['to_date'] : '';
        $from_date = isset($request['from_date']) ? $request['from_date'] : '';
        $arrSubscriptionPlan = array();
        \App::setLocale($locale);

        //dd($to_date);
        $to_date = str_replace("/", "-", $to_date);
        $from_date = str_replace("/", "-", $from_date);
        $dateDiff = (int) $this->dateDiff($from_date, $to_date);

        $userDetailsSubscriptionPlan = SubscriptionPlanForDriverDetail::where('driver_id', $driver_id)->where('status', 1)->first();

        if (isset($userDetailsSubscriptionPlan) && count($userDetailsSubscriptionPlan) > 0) {
            //when driver user is suspended
            $suspended_driver = UserInformation::where("user_mobile", $userDetailsSubscriptionPlan->driver_id)->where('user_status', 3)->first();
            if (isset($suspended_driver) && count($suspended_driver) > 0) {
                $DriverExpiredDocument = DriverDocumentInformation::where('user_id', $driver_id)->where('status', 3)->first();
                $msg = Lang::choice('messages.pause_plan_for_account_suspended', "", [], $locale);
                $msg .= ' ' . $DriverExpiredDocument->document_name;
                $msg .= ' ' . Lang::choice('messages.expired_document', "", [], $locale);
                $arr_to_return = array("error_code" => 2, "msg" => $msg);
            } else {
                $min_pause = $userDetailsSubscriptionPlan->subscriptionplandetail->min_pause_time_in_days;
                $max_pause = $userDetailsSubscriptionPlan->subscriptionplandetail->max_pause_time_in_days;
                if ($dateDiff < $min_pause) {
                    $msg = Lang::choice('messages.min_subscription_plan', "", [], $locale);
                    $msg .= ' ' . $min_pause . ' days';
                    $arr_to_return = array("error_code" => 2, "msg" => $msg);
                } elseif ($dateDiff > $max_pause) {
                    $msg = Lang::choice('messages.max_subscription_plan', "", [], $locale);
                    $msg .= ' ' . $max_pause . ' days';
                    $arr_to_return = array("error_code" => 2, "msg" => $msg);
                } else {
                    $expiry_date = date('Y-m-d', strtotime($userDetailsSubscriptionPlan->expiry_date . ' + ' . $dateDiff . ' days'));
                    $userDetailsSubscriptionPlan->expiry_date = $expiry_date;
                    $userDetailsSubscriptionPlan->save();
                    $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('messages.update_subscription_plan_expiry_Date', "", [], $locale));
                }
            }
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.subscription_plan_not_available', "", [], $locale));
        }
        return response()->json($arr_to_return);
    }

//cron for expiry date basis
    public function remainingSubscriptionPlanDays(Request $request) {
        $site_title = GlobalValues::get('site-title');
        $locale = config('app.locale');
        $arr_to_return = array();
        $today_date = date("Y-m-d");
        $expiry_date = "";
        $all_plan = SubscriptionPlan::all();
        $prev_date = date('Y-m-d', strtotime('-1 day', strtotime($today_date)));
        $allSubscriptionPlan = SubscriptionPlanForDriverDetail::where('expiry_date', $prev_date)->where('status', 1)->get();
        if (isset($allSubscriptionPlan) && count($allSubscriptionPlan)) {
            foreach ($allSubscriptionPlan as $subscriptionPlan) {
                //when driver user is suspended
                // $suspended_driver = UserInformation::where("user_mobile", $subscriptionPlan->driver_id)->where('user_status', 3)->first();
                //if (count($suspended_driver <= 0)) {
                $expiry_date = $subscriptionPlan->expiry_date;
                //end date of expiry date
                $end_date = new DateTime($expiry_date);
                $end_date->modify('last day of this month');

                $month = date("m", strtotime($expiry_date));
                $year = date("y", strtotime($expiry_date));
                $day = date("d", strtotime($expiry_date));
                $no_of_days = $this->noOfDaysInMonth($month, $year);
                $remaining_days = $no_of_days - $day;

                if ($remaining_days > 0) {
                    if ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Free plan") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Monthly";
                        });
                        $exist_subscription_plan = SubscriptionPlanDetail::WHERE('id', $subscriptionPlan->subscription_plan_detail_id)->first();
                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $price = $this->remainingDaysPrice($no_of_days, $remaining_days, $subscription_plan_Detail->price);

                            $price = round($price[0], 3);

                            $subscriptionPlan->subscription_plan_detail_id = $all_plan[1]->id;
                            $subscriptionPlan->expiry_date = $end_date->format('Y-m-d');
                            $subscriptionPlan->save();

                            $driverBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);
                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($driverBalance - $price);
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = '1';
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $subscriptionPlan->id;
                            $walletAmountCustomer['payment_type'] = '2';
                            UserWalletDetail::create($walletAmountCustomer);

                            //send email to driver
                            // $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $end_date->format('Y-m-d'), $locale);
                            //send push notification for deduct amount from wallet
                            $driver_details = User::where('id', $subscriptionPlan->driver_id)->first();
                            $deduct_amount_from_wallet = Lang::choice('messages.expired_plan', "", [], $locale);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . (double) ($price);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . " KD " . Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);

                            $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $deduct_amount_from_wallet, "flag" => 'deduct_amount_from_wallet', 'message' => $deduct_amount_from_wallet);
                            $arr_push_message_ios = array();
                            if (isset($driver_details->userInformation->device_id) && $driver_details->userInformation->device_id != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($driver_details->userInformation->device_type == '0') {
                                    $user_type = $driver_details->userInformation->user_type;
                                    //sending push notification driver user.
                                    $arr_push_message_android = ["data" => ['body' => $deduct_amount_from_wallet, 'title' => $site_title, "flag" => 'deduct_amount_from_wallet', 'order_id' => ''], 'notification' => ['body' => $deduct_amount_from_wallet, 'title' => $site_title]];
                                    $obj_send_push_notification->androidPushNotification($arr_push_message_android, $driver_details->userInformation->device_id, $driver_details->userInformation->user_type);
                                } else {
                                    $user_type = $driver_details->userInformation->user_type;
                                    $arr_push_message_ios = array();
                                    $arr_push_message_ios['to'] = $driver_details->userInformation->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                        }
                    } elseif ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Monthly") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Monthly";
                        });
                        $exist_subscription_plan = SubscriptionPlanDetail::WHERE('id', $subscriptionPlan->subscription_plan_detail_id)->first();
                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $price = $this->remainingDaysPrice($no_of_days, $remaining_days, $subscription_plan_Detail->price);
                            $price = round($price[0], 3);

                            $userBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);

                            $subscriptionPlan->subscription_plan_detail_id = $all_plan[1]->id;
                            $subscriptionPlan->expiry_date = $end_date->format('Y-m-d');
                            $subscriptionPlan->save();

                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($userBalance - $price);
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = 1;
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $subscriptionPlan->id;
                            $walletAmountCustomer['payment_type'] = 2;
                            UserWalletDetail::create($walletAmountCustomer);


                            //send email to driver
                            //$this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $end_date->format('Y-m-d'), $locale);
                            //send push notification for deduct amount from wallet
                            $driver_details = User::where('id', $subscriptionPlan->driver_id)->first();
                            $deduct_amount_from_wallet = Lang::choice('messages.expired_plan', "", [], $locale);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . (double) ($price);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . " KD " . Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);

                            $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $deduct_amount_from_wallet, "flag" => 'deduct_amount_from_wallet', 'message' => $deduct_amount_from_wallet);
                            $arr_push_message_ios = array();
                            if (isset($driver_details->userInformation->device_id) && $driver_details->userInformation->device_id != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($driver_details->userInformation->device_type == '0') {
                                    $user_type = $driver_details->userInformation->user_type;
                                    //sending push notification driver user.
                                    $arr_push_message_android = ["data" => ['body' => $deduct_amount_from_wallet, 'title' => $site_title, "flag" => 'deduct_amount_from_wallet', 'order_id' => ''], 'notification' => ['body' => $deduct_amount_from_wallet, 'title' => $site_title]];
                                    $obj_send_push_notification->androidPushNotification($arr_push_message_android, $driver_details->userInformation->device_id, $driver_details->userInformation->user_type);
                                } else {
                                    $user_type = $driver_details->userInformation->user_type;
                                    $arr_push_message_ios = array();
                                    $arr_push_message_ios['to'] = $driver_details->userInformation->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                        }
                    } elseif ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Quaterly") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Quaterly";
                        });
                        $exist_subscription_plan = SubscriptionPlanDetail::WHERE('id', $subscriptionPlan->subscription_plan_detail_id)->first();
                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $price = $this->remainingDaysPrice($no_of_days, $remaining_days, $subscription_plan_Detail->price);
                            $price = round($price[0], 3);

                            $userBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);

                            $subscriptionPlan->subscription_plan_detail_id = $all_plan[1]->id;
                            $subscriptionPlan->expiry_date = $end_date->format('Y-m-d');
                            $subscriptionPlan->save();

                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($userBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = '1';
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $subscriptionPlan->id;
                            $walletAmountCustomer['payment_type'] = '2';
                            UserWalletDetail::create($walletAmountCustomer);

                            //send email to driver
                            // $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $end_date->format('Y-m-d'), $locale);
                            //send push notification for deduct amount from wallet
                            $driver_details = User::where('id', $subscriptionPlan->driver_id)->first();
                            $deduct_amount_from_wallet = Lang::choice('messages.expired_plan', "", [], $locale);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . (double) ($price);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . " KD " . Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);

                            $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $deduct_amount_from_wallet, "flag" => 'deduct_amount_from_wallet', 'message' => $deduct_amount_from_wallet);
                            $arr_push_message_ios = array();
                            if (isset($driver_details->userInformation->device_id) && $driver_details->userInformation->device_id != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($driver_details->userInformation->device_type == '0') {
                                    $user_type = $driver_details->userInformation->user_type;
                                    //sending push notification driver user.

                                    $arr_push_message_android = ["data" => ['body' => $deduct_amount_from_wallet, 'title' => $site_title, "flag" => 'deduct_amount_from_wallet', 'order_id' => ''], 'notification' => ['body' => $deduct_amount_from_wallet, 'title' => $site_title]];
                                    $obj_send_push_notification->androidPushNotification($arr_push_message_android, $driver_details->userInformation->device_id, $driver_details->userInformation->user_type);
//                                    $objDeliveryUser = new OrderController();
//                                    $arr_push_message_android = array();
//                                    $arr_push_message_android['to'] = $driver_details->userInformation->device_id;
//                                    $arr_push_message_android['priority'] = "high";
//                                    $arr_push_message_android['sound'] = "default";
//                                    $arr_push_message_android['notification'] = $arr_push_message;
//                                    $objDeliveryUser->androidPushNotification(json_encode($arr_push_message_android), $user_type);
                                } else {
                                    $user_type = $driver_details->userInformation->user_type;
                                    $arr_push_message_ios = array();
                                    $arr_push_message_ios['to'] = $driver_details->userInformation->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                        }
                    } elseif ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Half Yearly") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Half Yearly";
                        });
                        $exist_subscription_plan = SubscriptionPlanDetail::WHERE('id', $subscriptionPlan->subscription_plan_detail_id)->first();
                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $price = $this->remainingDaysPrice($no_of_days, $remaining_days, $subscription_plan_Detail->price);
                            $price = round($price[0], 3);

                            $userBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);

                            $subscriptionPlan->subscription_plan_detail_id = $all_plan[1]->id;
                            $subscriptionPlan->expiry_date = $end_date->format('Y-m-d');
                            $subscriptionPlan->save();

                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($userBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = '1';
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $subscriptionPlan->id;
                            $walletAmountCustomer['payment_type'] = '2';
                            UserWalletDetail::create($walletAmountCustomer);

//send email to driver
                            // $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $end_date->format('Y-m-d'), $locale);
//send push notification for deduct amount from wallet
                            $driver_details = User::where('id', $subscriptionPlan->driver_id)->first();
                            $deduct_amount_from_wallet = Lang::choice('messages.expired_plan', "", [], $locale);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . (double) ($price);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . " KD " . Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);

                            $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $deduct_amount_from_wallet, "flag" => 'deduct_amount_from_wallet', 'message' => $deduct_amount_from_wallet);
                            $arr_push_message_ios = array();
                            if (isset($driver_details->userInformation->device_id) && $driver_details->userInformation->device_id != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($driver_details->userInformation->device_type == '0') {
                                    $user_type = $driver_details->userInformation->user_type;
//sending push notification driver user.

                                    $arr_push_message_android = ["data" => ['body' => $deduct_amount_from_wallet, 'title' => $site_title, "flag" => 'deduct_amount_from_wallet', 'order_id' => ''], 'notification' => ['body' => $deduct_amount_from_wallet, 'title' => $site_title]];
                                    $obj_send_push_notification->androidPushNotification($arr_push_message_android, $driver_details->userInformation->device_id, $driver_details->userInformation->user_type);
                                } else {
                                    $user_type = $driver_details->userInformation->user_type;
                                    $arr_push_message_ios = array();
                                    $arr_push_message_ios['to'] = $driver_details->userInformation->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                        }
                    } else {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->id == "Yearly";
                        });
                        $exist_subscription_plan = SubscriptionPlanDetail::WHERE('id', $subscriptionPlan->subscription_plan_detail_id)->first();
                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $price = $this->remainingDaysPrice($no_of_days, $remaining_days, $subscription_plan_Detail->price);
                            $price = round($price[0], 3);

                            $userBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);

                            $subscriptionPlan->subscription_plan_detail_id = $all_plan[1]->id;
                            $subscriptionPlan->expiry_date = $end_date->format('Y-m-d');
                            $subscriptionPlan->save();

                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($userBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = '1';
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $subscriptionPlan->id;
                            $walletAmountCustomer['payment_type'] = '2';
                            UserWalletDetail::create($walletAmountCustomer);


//send email to driver
                            // $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $end_date->format('Y-m-d'), $locale);
//send push notification for deduct amount from wallet
                            $driver_details = User::where('id', $subscriptionPlan->driver_id)->first();
                            $deduct_amount_from_wallet = Lang::choice('messages.expired_plan', "", [], $locale);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . (double) ($price);
                            $deduct_amount_from_wallet = $deduct_amount_from_wallet . " KD " . Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);

                            $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $deduct_amount_from_wallet, "flag" => 'deduct_amount_from_wallet', 'message' => $deduct_amount_from_wallet);
                            $arr_push_message_ios = array();
                            if (isset($driver_details->userInformation->device_id) && $driver_details->userInformation->device_id != '') {
                                $obj_send_push_notification = new SendPushNotification();
                                if ($driver_details->userInformation->device_type == '0') {
                                    $user_type = $driver_details->userInformation->user_type;
//sending push notification driver user.

                                    $arr_push_message_android = ["data" => ['body' => $deduct_amount_from_wallet, 'title' => $site_title, "flag" => 'deduct_amount_from_wallet', 'order_id' => ''], 'notification' => ['body' => $deduct_amount_from_wallet, 'title' => $site_title]];
                                    $obj_send_push_notification->androidPushNotification($arr_push_message_android, $driver_details->userInformation->device_id, $user_type);
                                } else {
                                    $user_type = $driver_details->userInformation->user_type;
                                    $arr_push_message_ios = array();
                                    $arr_push_message_ios['to'] = $driver_details->userInformation->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                                }
                            }
                        }
                    }
                }
//}
            }
        }

        $this->driverDocumentExpiryDaysNotification();
        $this->changeDriverDocumentStatus();
        $this->expiredCoupon();
        $this->croneForcheckValidityOfCashBack();
        $this->noOfRejectionForDriver();
    }

    function dateDiff($date1, $date2) {
        $date1_ts = strtotime($date1);
        $date2_ts = strtotime($date2);
        $diff = $date2_ts - $date1_ts;
        return round($diff / 86400);
    }

    function noOfDaysInMonth($month, $year) {
        $number = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        return $number;
    }

    function remainingDaysPrice($no_of_days, $remaining_days, $price) {
        $price = [($remaining_days * $price) / $no_of_days];
        return $price;
    }

//cron for every month on 12 am
    public function expiredDriverRejectionLimit() {
        $locale = config('app.locale');
        $today_date = date("Y-m-d");
        $site_title = GlobalValues::get('site-title');
        $all_plan = SubscriptionPlan::all();
        $prev_date = date('Y-m-d', strtotime('-1 day', strtotime($today_date)));
        $allSubscriptionPlan = SubscriptionPlanForDriverDetail::where('expiry_date', $prev_date)->where('status', 1)->get();

        if (isset($allSubscriptionPlan) && count($allSubscriptionPlan) > 0) {
            foreach ($allSubscriptionPlan as $subscriptionPlan) {
                $subscriptionPlan->status = 0;
                $subscriptionPlan->save();
//when driver user is suspended
// $suspended_driver = UserInformation::where("user_mobile", $subscriptionPlan->driver_id)->where('user_status', 3)->first();
//if (count($suspended_driver <= 0)) {
                $schedule_subscription_plan = SubscriptionPlanForDriverDetail::where('driver_id', $subscriptionPlan->driver_id)->where('status', 2)->first();
                if (isset($schedule_subscription_plan) && count($schedule_subscription_plan) > 0) {
                    $schedule_subscription_plan->status = 1;
                    $schedule_subscription_plan->save();

                    $price = ($schedule_subscription_plan->subscriptionplandetail->price);

//deduct subscription plan price from account
                    $objDeliveryUser = new DeliveryController();
                    $discount = $objDeliveryUser->getDiscountOnSubscriptionPlan($subscriptionPlan->driver_id);
                    if (!empty($discount)) {
                        if ($discount [
                                'service_discount_type'] == 0) {
                            $price -= $price * (($discount['service_discount_val']) / 100);
                            $type = 1;
                        } else {
                            $price -= ($discount['service_discount_val']);
                            $type = 2;
                        }
                        $walletAmountCustomer['text'] = "Subscription Plan - " . $subscriptionPlan->subscriptionplandetail->subscriptionPlan->name;
                        $walletAmountCustomer['discount_type'] = $type;
                        $walletAmountCustomer ['discount_amount'] = $discount['service_discount_val'];
                    }
                    $driverBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);
                    $walletAmountCustomer['user_id'] = $subscriptiosPlan->driver_id;
                    $walletAmountCustomer['transaction_amount'] = (double) ($price);
                    $walletAmountCustomer['final_amout'] = (double) ($price);
                    $walletAmountCustomer['avl_balance'] = (double) ($driverBalance - $price );
                    $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                    $walletAmountCustomer['transaction_type'] = 1;
                    $walletAmountCustomer['flag'] = '2';
                    $walletAmountCustomer['subscription_plan_driver_detail_id'] = $schedule_subscription_plan->id;
                    $walletAmountCustomer['payment_type'] = 2;
                    UserWalletDetail::create($walletAmountCustomer);

//send email to driver
                    //  $this->sendMailForSubscriptionPlanBill($schedule_subscription_plan->subscriptionplandetail, $price, $subscriptionPlan->driver_id, $subscriptionPlan->start_date, $subscriptionPlan->expiry_date, $locale);
                } else {
                    $exist_subscription_plan = SubscriptionPlanDetail::WHERE('id', $subscriptionPlan->subscription_plan_detail_id)->first();

                    if ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Free plan") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Monthly";
                        });
                        $end_date = new DateTime($today_date);
                        $end_date->modify('last day of this month');

                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $default_subscription_plan = new SubscriptionPlanForDriverDetail();
                            $default_subscription_plan->driver_id = $subscriptionPlan->driver_id;
                            $default_subscription_plan->start_date = $today_date;
                            $default_subscription_plan->status = 1;
                            $default_subscription_plan->expiry_date = $end_date->format('Y-m-d');
                            $default_subscription_plan->subscription_plan_detail_id = $subscription_plan_Detail->id;
                            $default_subscription_plan->save();


                            $price = ($subscription_plan_Detail->price);


//deduct subscription plan price from account
                            $objDeliveryUser = new DeliveryController();
                            $discount = $objDeliveryUser->getDiscountOnSubscriptionPlan($subscriptionPlan->driver_id);
                            if (!empty($discount)) {
                                if ($discount['service_discount_type'] == 0) {
                                    $price -= $price * ( ($discount['service_discount_val']) / 100 );
                                    $type = 1;
                                } else {
                                    $price -= ($discount['service_discount_val']);
                                    $type = 2;
                                }
                                $walletAmountCustomer['text'] = "Subscription Plan - " . $subscriptionPlan->subscriptionplandetail->subscriptionPlan->name;
                                $walletAmountCustomer['discount_type'] = $type;
                                $walletAmountCustomer['discount_amount'] = $discount['service_discount_val'];
                            }
                            $driverBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);
                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($driverBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = 1;
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $default_subscription_plan->id;
                            $walletAmountCustomer['payment_type'] = 2;
                            UserWalletDetail::create($walletAmountCustomer);


//send email to driver
                            // $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $end_date->format('Y-m-d'), $locale);
                        }
                    } elseif ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Monthly") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Monthly";
                        });
                        $end_date = new DateTime($today_date);
                        $end_date->modify('last day of this month');
                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $default_subscription_plan = new SubscriptionPlanForDriverDetail();
                            $default_subscription_plan->driver_id = $subscriptionPlan->driver_id;
                            $default_subscription_plan->start_date = $today_date;
                            $default_subscription_plan->status = 1;
                            $default_subscription_plan->expiry_date = $end_date->format('Y-m-d');
                            $default_subscription_plan->subscription_plan_detail_id = $subscription_plan_Detail->id;
                            $default_subscription_plan->save();

                            $price = ($subscription_plan_Detail->price);

//deduct subscription plan price from account
                            $objDeliveryUser = new DeliveryController();
                            $discount = $objDeliveryUser->getDiscountOnSubscriptionPlan($subscriptionPlan->driver_id);
                            if (!empty($discount)) {
                                if ($discount['service_discount_type'] == 0) {
                                    $price -= $price * ( ($discount['service_discount_val']) / 100 );
                                    $type = 1;
                                } else {
                                    $price -= ($discount['service_discount_val']);
                                    $type = 2;
                                }
                                $walletAmountCustomer['text'] = "Subscription Plan - " . $subscriptionPlan->subscriptionplandetail->subscriptionPlan->name;
                                $walletAmountCustomer['discount_type'] = $type;
                                $walletAmountCustomer['discount_amount'] = $discount['service_discount_val'];
                            }
                            $driverBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);
                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($driverBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = 1;
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $default_subscription_plan->id;
                            $walletAmountCustomer['payment_type'] = 2;
                            UserWalletDetail::create($walletAmountCustomer);


//send email to driver
                            //  $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $end_date->format('Y-m-d'), $locale);
                        }
                    } elseif ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Quaterly") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Quaterly";
                        });
                        $start_date = new DateTime($today_date);
                        $start_date->modify('first day of this month');

                        $end_date = date('Y-m-d', strtotime("+3 months", strtotime($start_date->format('Y-m-d'))));
                        $expiry_date = date('Y-m-d', strtotime('-1 day', strtotime($end_date)));

                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $default_subscription_plan = new SubscriptionPlanForDriverDetail();
                            $default_subscription_plan->driver_id = $subscriptionPlan->driver_id;
                            $default_subscription_plan->start_date = $today_date;
                            $default_subscription_plan->status = 1;
                            $default_subscription_plan->expiry_date = $expiry_date;
                            $default_subscription_plan->subscription_plan_detail_id = $subscription_plan_Detail->id;
                            $default_subscription_plan->save();

                            $price = ($subscription_plan_Detail->price);
//deduct subscription plan price from account
                            $objDeliveryUser = new DeliveryController();
                            $discount = $objDeliveryUser->getDiscountOnSubscriptionPlan($subscriptionPlan->driver_id);
                            if (!empty($discount)) {
                                if ($discount['service_discount_type'] == 0) {
                                    $price -= $price * ( ($discount['service_discount_val']) / 100 );
                                    $type = 1;
                                } else {
                                    $price -= ($discount['service_discount_val']);
                                    $type = 2;
                                }
                                $walletAmountCustomer['text'] = "Subscription Plan - " . $subscriptionPlan->subscriptionplandetail->subscriptionPlan->name;
                                $walletAmountCustomer['discount_type'] = $type;
                                $walletAmountCustomer['discount_amount'] = $discount['service_discount_val'];
                            }
                            $driverBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);
                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($driverBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = 1;
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $default_subscription_plan->id;
                            $walletAmountCustomer['payment_type'] = 2;
                            UserWalletDetail::create($walletAmountCustomer);

//send email to driver
                            //   $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $expiry_date, $locale);
                        }
                    } elseif ($subscriptionPlan->subscriptionplandetail->subscriptionPlan->slug == "Half Yearly") {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->slug == "Half Yearly";
                        });

                        $start_date = new DateTime($today_date);
                        $start_date->modify('first day of this month');

                        $end_date = date('Y-m-d', strtotime("+6 months", strtotime($start_date->format('Y-m-d'))));
                        $expiry_date = date('Y-m-d', strtotime('-1 day', strtotime($end_date)));

                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $default_subscription_plan = new SubscriptionPlanForDriverDetail();
                            $default_subscription_plan->driver_id = $subscriptionPlan->driver_id;
                            $default_subscription_plan->start_date = $today_date;
                            $default_subscription_plan->status = 1;
                            $default_subscription_plan->expiry_date = $expiry_date;
                            $default_subscription_plan->subscription_plan_detail_id = $subscription_plan_Detail->id;
                            $default_subscription_plan->save();

                            $price = ($subscription_plan_Detail->price);
//deduct subscription plan price from account
                            $objDeliveryUser = new DeliveryController();
                            $discount = $objDeliveryUser->getDiscountOnSubscriptionPlan($subscriptionPlan->driver_id);
                            if (!empty($discount)) {
                                if ($discount['service_discount_type'] == 0) {
                                    $price -= $price * ( ($discount['service_discount_val']) / 100 );
                                    $type = 1;
                                } else {
                                    $price -= ($discount['service_discount_val']);
                                    $type = 2;
                                }
                                $walletAmountCustomer['text'] = "Subscription Plan - " . $subscriptionPlan->subscriptionplandetail->subscriptionPlan->name;
                                $walletAmountCustomer['discount_type'] = $type;
                                $walletAmountCustomer['discount_amount'] = $discount['service_discount_val'];
                            }
                            $driverBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);
                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($driverBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = 1;
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $default_subscription_plan->id;
                            $walletAmountCustomer['payment_type'] = 2;
                            UserWalletDetail::create($walletAmountCustomer);

//send email to driver
                            // $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $expiry_date, $locale);
                        }
                    } else {
                        $all_plan = $all_plan->filter(function($plan) {
                            return $plan->id == "Yearly";
                        });

                        $start_date = new DateTime($today_date);
                        $start_date->modify('first day of this month');

                        $end_date = date('Y-m-d', strtotime("+12 months", strtotime($start_date->format('Y-m-d'))));
                        $expiry_date = date('Y-m-d', strtotime('-1 day', strtotime($end_date)));

                        $subscription_plan_Detail = SubscriptionPlanDetail::where('subscription_plan_id', $all_plan[1]->id)->WHERE('service_id', $exist_subscription_plan->service_id)->first();
                        if (isset($subscription_plan_Detail) && count($subscription_plan_Detail) > 0) {
                            $default_subscription_plan = new SubscriptionPlanForDriverDetail();
                            $default_subscription_plan->driver_id = $subscriptionPlan->driver_id;
                            $default_subscription_plan->start_date = $today_date;
                            $default_subscription_plan->status = 1;
                            $default_subscription_plan->expiry_date = $expiry_date;
                            $default_subscription_plan->subscription_plan_detail_id = $subscription_plan_Detail->id;
                            $default_subscription_plan->save();


                            $price = ($subscription_plan_Detail->price);

//deduct subscription plan price from account
                            $objDeliveryUser = new DeliveryController();
                            $discount = $objDeliveryUser->getDiscountOnSubscriptionPlan($subscriptionPlan->driver_id);
                            if (!empty($discount)) {
                                if ($discount['service_discount_type'] == 0) {
                                    $price -= $price * ( ($discount['service_discount_val']) / 100 );
                                    $type = 1;
                                } else {
                                    $price -= ($discount['service_discount_val']);
                                    $type = 2;
                                }
                                $walletAmountCustomer['text'] = "Subscription Plan - " . $subscriptionPlan->subscriptionplandetail->subscriptionPlan->name;
                                $walletAmountCustomer['discount_type'] = $type;
                                $walletAmountCustomer['discount_amount'] = $discount['service_discount_val'];
                            }
                            $driverBalance = GlobalValues::userBalance($subscriptionPlan->driver_id);
                            $walletAmountCustomer['user_id'] = $subscriptionPlan->driver_id;
                            $walletAmountCustomer['transaction_amount'] = (double) ($price);
                            $walletAmountCustomer['final_amout'] = (double) ($price);
                            $walletAmountCustomer['avl_balance'] = (double) ($driverBalance - $price );
                            $walletAmountCustomer['trans_desc'] = Lang::choice('messages.deduct_money_from_wallet', "", [], $locale);
                            $walletAmountCustomer['transaction_type'] = 1;
                            $walletAmountCustomer['flag'] = '2';
                            $walletAmountCustomer['subscription_plan_driver_detail_id'] = $default_subscription_plan->id;
                            $walletAmountCustomer['payment_type'] = 2;
                            UserWalletDetail::create($walletAmountCustomer);

//send email to driver
                            // $this->sendMailForSubscriptionPlanBill($subscription_plan_Detail, $price, $subscriptionPlan->driver_id, $today_date, $expiry_date, $locale);
                        }
                    }
                }
//send push notification for deduct amount from wallet
                $driver_details = User::where('id', $subscriptionPlan->driver_id)->first();
                $schedule_plan_activate = Lang::choice('messages.expired_plan', "", [], $locale);
                $schedule_plan_activate = $schedule_plan_activate . "" . Lang::choice('messages.schedule_plan_activate', "", [], $locale);

                $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $schedule_plan_activate, "flag" => 'schedule_plan_activate', 'message' => $schedule_plan_activate);
                $arr_push_message_ios = array();
                if (isset($driver_details->userInformation->device_id) && $driver_details->userInformation->device_id != '') {
                    $obj_send_push_notification = new SendPushNotification();
                    if ($driver_details->userInformation->device_type == '0') {
                        $user_type = $driver_details->userInformation->user_type;
//sending push notification driver user.

                        $arr_push_message_android = ["data" => ['body' => $schedule_plan_activate, 'title' => $site_title, "flag" => 'schedule_plan_activate', 'order_id' => ''], 'notification' => ['body' => $schedule_plan_activate, 'title' => $site_title]];
                        $obj_send_push_notification->androidPushNotification($arr_push_message_android, $driver_details->userInformation->device_id, $user_type);
                    } else {
                        $user_type = $driver_details->userInformation->user_type;
                        $arr_push_message_ios = array();
                        $arr_push_message_ios['to'] = $driver_details->userInformation->device_id;
                        $arr_push_message_ios['priority'] = "high";
                        $arr_push_message_ios['sound'] = "iOSSound.wav";
                        $arr_push_message_ios['notification'] = $arr_push_message;
                        $obj_send_push_notification->iOSPushNotificatonDriver(json_encode($arr_push_message_ios), $user_type);
                    }
                }
//}
            }
        }
        $settelement = new CronController();
        $settelement->cronForDriverEvaluation();
// $arr_orders = Order::where('status', '2')->get();
//        if (isset($arr_orders) && count($arr_orders)) {
//            foreach ($arr_orders as $order) {
//                $this->removeInvoiceFromStorage($order->order_unique_id);
//            }
//        }
    }

    public function sendNoificationForOrderStatusChange($data) {
        $site_title = GlobalValues::get('site-title');
        $arr_push_message = array("sound" => "iOSSound.wav", "title" => $site_title, "text" => $data['order_message'], "flag" => 'ride_status_updated', 'message' => $data['order_message'], 'order_id' => $data['order_details']->id);


//        $arrOrderNotificationDetails['order_id'] = $data['order_details']->id;
//        $arrOrderNotificationDetails['user_id'] = $data['order_details']->customer_id;
//        $arrOrderNotificationDetails['created_at'] = $data['date2'];
//        $arrOrderNotificationDetails['updated_at'] = $data['date2'];
//        $arrOrderNotificationDetails['message'] = $data['order_message'];
//        OrderNotification::create($arrOrderNotificationDetails);

        $available_driver_details = UserInformation::where('user_id', $data['order_details']->customer_id)->first();
        if (isset($available_driver_details)) {
            $notiMsg = $data['order_message'];
            $notiMsg = str_replace("%%CUSTOMER_NAME%%", $available_driver_details->first_name . " " . $available_driver_details->last_name, $notiMsg);
            $notiMsg = str_replace("%%ORDER_NUMBER%%", $data['order_details']->order_unique_id, $notiMsg);
            $notiMsg = str_replace("%%DATE_TIME%%", $data['notification_customer_time'], $notiMsg);
            $saveNotification = new AppNotification();
            $saveNotification->saveNotification($available_driver_details->user_id, $data['order_details']->id, $data['order_message'], $notiMsg, $data['notification_customer_time'], 0, 'order');


            if (isset($available_driver_details->device_id) && $available_driver_details->device_id != '') {
                $obj_send_push_notification = new SendPushNotification();
                if ($available_driver_details->device_type == '0') {
//sending push notification customer user.               
                    $arr_push_message_android = [];
                    $arr_push_message_android = ["data" => ['body' => $data['order_message'], 'title' => $site_title, "flag" => 'ride_status_updated', 'order_id' => $data['order_details']->id], 'notification' => ['body' => $data['order_message'], 'title' => $site_title]];
                    $obj_send_push_notification->androidPushNotification($arr_push_message_android, $available_driver_details->device_id, $available_driver_details->user_type);
                } else {
                    $arr_push_message_ios = array();
                    $arr_push_message_ios['to'] = $available_driver_details->device_id;
                    $arr_push_message_ios['priority'] = "high";
                    $arr_push_message_ios['sound'] = "iOSSound.wav";
                    $arr_push_message_ios['notification'] = $arr_push_message;
                    $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                }
            }
        }
    }

    private function removeProfilePictureFromStrorage($file_name) {
        if (Storage::exists('public/user-images/' . $file_name)) {
            Storage::delete('public/user-images/' . $file_name);
            return true;
        }
        return false;
    }

    public function removeInvoiceFromStorage($order_id) {
        $filename = "Order_" . $order_id . "_invoice_1.pdf";
        if (Storage::exists('public/order-document/' . $filename)) {
            Storage::delete('public/order-document/' . $filename);
            return true;
        }
        return false;
    }

    public function sendMailForSubscriptionPlanBill($subscription_plan_detail = '', $price = '', $driver_id = '', $from_Date = '', $to_date = '', $locale) {
        $arr_keyword_values = array();
        $site_email = GlobalValues::get('site-email');
        $site_title = GlobalValues::get('site-title');

        $arrDriverDetails = User::find($driver_id);
        if (isset($arrDriverDetails) && count($arrDriverDetails)) {
            $user_code = "+" . str_replace("+", "", $arrDriverDetails->userInformation->mobile_code);
            $countryDetails = Country::where('country_code', $user_code)->first();
            $currencyCode = '';
            if (count($countryDetails) > 0) {
                $currencyCode = $countryDetails->currency_code;
            } else {
                $currencyCode = 'KD';
            }
            $arr_keyword_values['DRIVER_FIRST_NAME'] = isset($arrDriverDetails->userInformation->first_name) ? $arrDriverDetails->userInformation->first_name : '';
            $arr_keyword_values['DRIVER_LAST_NAME'] = isset($arrDriverDetails->userInformation->last_name) ? $arrDriverDetails->userInformation->last_name : '';
            $arr_keyword_values['SUBSCRIPTION_PLAN_NAME'] = isset($subscription_plan_detail->subscriptionPlan->name) ? $subscription_plan_detail->subscriptionPlan->name : '';
            $arr_keyword_values['AMOUNT'] = isset($price) ? $currencyCode . ' ' . $price : '';
            $arr_keyword_values['FROM_DATE'] = isset($from_Date) ? $from_Date : '';
            $arr_keyword_values['TO_DATE'] = isset($to_date) ? $to_date : '';
            $arr_keyword_values['SITE_TITLE'] = $site_title;
            $email_template_title = "emailtemplate::driver-subscription-plan-bill-" . $locale;
            $email_template_subject = Lang::choice('messages.subscription_invoice', "", [], $locale);

            $email = @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($email_template_subject, $arrDriverDetails, $site_email, $site_title) {
                        if (isset($arrDriverDetails->user->email)) {
                            $message->to($arrDriverDetails->user->email)->subject($email_template_subject)->from($site_email, $site_title);
                        }
                    });
        }
    }

    public function sendOrderRelatedInvoice(Request $request) {
        $arr_keyword_values = array();
        $site_email = App\PiplModules\admin\Helpers\GlobalValues::get('site-email');
        $site_title = GlobalValues::get('site-title');
        $order_id = isset($request['order_id']) ? $request['order_id'] : '';
        $locale = isset($request['locale']) ? $request['locale'] : 'en';
        $status = "save";
        $arr_to_return = array();
        \App::setLocale($locale);
        $order = Order::find($order_id);
        if (isset($order) && count($order) > 0) {
            $customer_details = User::where('id', $order->customer_id)->first();
            if (isset($customer_details) && count($customer_details) > 0) {

                $sql = "SELECT ofc.ride_waiting_time, ofc.total_fare_estimation,ofc.ride_waiting_rate,ofc.ride_driving_rate,ofc.pre_ride_waiting_fees,ofc.pre_ride_driving_fees,czs.ride_starting_fees,czs.fixed_fees,con.currency_code,oi.discount,
                    oi.pickup_area,oi.drop_area,gao.id,gao.order_unique_id,gao.fare_amount,gao.total_amount,gao.driver_id as driver_id
                ,gao.customer_id as passenger_id,gadui.first_name as driver_first_name,gadui.last_name as driver_last_name,
                gadu.email as driver_email,gadui.mobile_code as driver_mobile_code,gadui.user_mobile as driver_user_mobile,
                gadui.civil_id as driver_civil_id,gapui.first_name as passenger_first_name,gapui.last_name as passenger_last_name,
                gapu.email as passenger_email,gapui.mobile_code as passenger_mobile_code,gapui.user_mobile as passenger_user_mobile,
                gaz.zone_name as ride_zone_name FROM " . DB::getTablePrefix() . "orders as gao 
                LEFT JOIN " . DB::getTablePrefix() . "users as gadu ON gao.driver_id = gadu.id 
                LEFT JOIN " . DB::getTablePrefix() . "user_informations as gadui ON gadu.id = gadui.user_id
                LEFT JOIN " . DB::getTablePrefix() . "users as gapu ON gao.customer_id = gapu.id
                LEFT JOIN " . DB::getTablePrefix() . "user_informations as gapui ON gapu.id = gapui.user_id
                LEFT JOIN " . DB::getTablePrefix() . "zones as gaz ON gao.zone_id = gaz.id 
                LEFT JOIN " . DB::getTablePrefix() . "orders_informations as oi ON gao.id = oi.order_id 
                LEFT JOIN " . DB::getTablePrefix() . "countries as con ON gao.country_id = con.id 
                LEFT JOIN " . DB::getTablePrefix() . "country_zone_services as czs ON gao.zone_id = czs.zone_id 
                LEFT JOIN " . DB::getTablePrefix() . "order_fare_calculations as ofc ON gao.id = ofc.order_id 
                WHERE gao.id = $order_id AND gao.status = '2' AND ofc.status = '1'";


                $sql1 = "SELECT ml.address FROM " . DB::getTablePrefix() . "orders as gao 
                  LEFT JOIN " . DB::getTablePrefix() . "middle_locations as ml ON gao.id = ml.order_id 
                  WHERE gao.id = $order_id AND gao.status = '2'";

                $middle_details = DB::select($sql1);
                $order_details = DB::select($sql);
                if (isset($order_details) && count($order_details) > 0) {
                    $order_details = $order_details[0];
                }

                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $arr_keyword_values = array();
//Assign values to all macros
                if ($order_details != "") {
                    $currency = $order_details->currency_code;
                } else {
                    $currency = "KD";
                }
//  $starting_fees = (isset($order_details->fixed_fees) ? $order_details->fixed_fees : 0.000) + (isset($order_details->ride_starting_fees) ? $order_details->ride_starting_fees : 0.000);
// $starting_fees = isset($starting_fees) ? number_format(round($starting_fees, 2), 3, '.', '') : 0.000;
                $fixed_fees = isset($order_details->fixed_fees) ? number_format(round($order_details->fixed_fees, 2), 3, '.', '') : 0.000;
                $ride_starting_fees = isset($order_details->ride_starting_fees) ? number_format(round($order_details->ride_starting_fees, 2), 3, '.', '') : 0.000;
                $pre_ride_driving_fees = isset($order_details->pre_ride_driving_fees) ? number_format(round($order_details->pre_ride_driving_fees, 2), 3, '.', '') : 0.000;
                $pre_ride_waiting_fees = isset($order_details->pre_ride_waiting_fees) ? number_format(round($order_details->pre_ride_waiting_fees, 2), 3, '.', '') : 0.000;
                $ride_driving_rate = isset($order_details->ride_driving_rate) ? number_format(round($order_details->ride_driving_rate, 2), 3, '.', '') : 0.000;
                $ride_waiting_rate = isset($order_details->ride_waiting_rate) ? number_format(round($order_details->ride_waiting_rate, 2), 3, '.', '') : 0.000;
                $total_fare_estimation = isset($order_details->total_fare_estimation) ? number_format(round($order_details->total_fare_estimation, 2), 3, '.', '') : 0.000;


                $arr_keyword_values['SITE_TITLE'] = $site_title;
                $arr_keyword_values ['CUSTOMER_FIRST_NAME'] = isset($customer_details->userInformation->first_name) ? $customer_details->userInformation->first_name : '';
                $arr_keyword_values ['CUSTOMER_LAST_NAME'] = isset($customer_details->userInformation->last_name) ? $customer_details->userInformation->last_name : '';
                $arr_keyword_values['ORDER_ID'] = isset($order_details->id) ? $order_details->id : '';
                $arr_keyword_values['PICKUP_AREA'] = isset($order_details->pickup_area) ? $order_details->pickup_area : '';
                $arr_keyword_values['DROP_AREA'] = isset($order_details->drop_area) ? $order_details->drop_area : '';
                $arr_keyword_values['ORDER_NUMBER'] = isset($order_details->order_unique_id) ? $order_details->order_unique_id : '';
                $arr_keyword_values['FIXED_FEES'] = $currency . ' ' . $fixed_fees;
                $arr_keyword_values['RIDE_STARTING_FEES'] = $currency . ' ' . $ride_starting_fees;
                $arr_keyword_values['PRE_RIDE_DRIVING_FEES'] = $currency . ' ' . $pre_ride_driving_fees;
                $arr_keyword_values['PRE_RIDE_WAITING_FEES'] = $currency . ' ' . $pre_ride_waiting_fees;
                $arr_keyword_values['RIDE_DRIVING_RATE'] = $currency . ' ' . $ride_driving_rate;
                $arr_keyword_values['RIDE_WAITING_RATE'] = $currency . ' ' . $ride_waiting_rate;
                $arr_keyword_values['TOTAL_TRIP_AMOUNT'] = $currency . ' ' . $total_fare_estimation;
                $arr_keyword_values['middle_location_address'] = $middle_details;
                $arr_keyword_values['middle_location_waiting_time'] = '-';
//  $email_subject = Lang::choice('messages.trip_complete_fare_details_email',"",[],$locale);
                //  $email_template_title = "emailtemplate::order-related-invoice-" . $locale;
                $notiMsg = Lang::choice('messages.order_invoice', "", [], $locale);
                $notiMsg = str_replace("%%ORDER_NUMBER%%", $order->order_unique_id, $notiMsg);
                $email_subject = $notiMsg;
                $customer_email = $customer_details->email;
//                @Mail::send($email_template_title, $arr_keyword_values, function ($message) use ($customer_email, $email_subject, $site_email, $site_title, $email_template_subject) {
//                            $message->to($customer_email)->subject($email_template_subject)->from($site_email, $site_title);
//                        });


                $tempate_name = "emailtemplate::trip-fare-estimation-" . $locale;
                $customer_email = $customer_details->email;
                @Mail::send($tempate_name, $arr_keyword_values, function ($message) use ($customer_email, $email_subject, $site_email, $site_title) {
                            $message->to($customer_email)->subject($email_subject)->from($site_email, $site_title);
                        });
                $arr_to_return = array("error_code" => 0);
                return response()->json($arr_to_return);
            }
        }
    }

    public function changeNumberFormat($value) {
        return number_format(round($value, 2), 3, '.', '');
    }

    private function getUserCurrencyCode($user_id) {
        $currencyCode = '';
        $userDetails = UserInformation::where('user_id', $user_id)->first();
        if (isset($userDetails) && count($userDetails) > 0) {
            $user_code = "+" . str_replace("+", "", $userDetails->mobile_code);
            $countryDetails = Country::where('country_code', $user_code)->first();
            if (count($countryDetails) > 0) {
                $currencyCode = $countryDetails->currency_code;
            }
        }
        return $currencyCode;
    }

    public function expiredCoupon() {
        $today_date = date("Y-m-d 00:00:00");
        $timestamp = date("h:i a");
        $coupon_detail = DB::table('coupons')
                ->where('coupons.end_date', '<=', $today_date)
                ->where('coupons.to_time', '<=', strtotime($timestamp))
                ->update(array('status' => 2));
    }

    public function croneForcheckValidityOfCashBack() {
        $locale = config('app.locale');
        $today_date = date("Y-m-d");
        $site_title = GlobalValues::get('site-title');
        $cash_bag_for_ride_details = DB::table('orders')
                ->join('orders_informations', function($join) {
                    $join->on('orders.id', '=', 'orders_informations.order_id');
                })
                ->where('orders_informations.type', '=', 0)
                ->where('orders.status', '=', 2)
                ->where('orders.used_cashback', '=', 0)
                ->get();
        if (isset($cash_bag_for_ride_details) && count($cash_bag_for_ride_details)) {
            foreach ($cash_bag_for_ride_details as $cash_back_detail) {
                $start_date = $cash_back_detail->order_complete_date_time;
//end date of expiry date
                $datetime1 = new DateTime($start_date);
                $datetime2 = new DateTime($today_date);
                $interval = $datetime1->diff($datetime2);
                $coupon_detail = DB::table('coupons')
                        ->where('coupons.id', $cash_back_detail->coupon_id)
                        ->where('coupons.user_type', 1)
                        ->where('coupons.type', 0)
                        ->where('coupons.validity', '<=', $interval->format('%R%a'))
                        ->get();
                if (isset($coupon_detail) && count($coupon_detail) > 0) {

//update cashbag status
                    DB::table('orders')
                            ->where('orders.id', '=', $cash_back_detail->id)
                            ->update(array('used_cashback' => '1'));



                    $notiMsg2 = Lang::choice('messages.expired_cash_back', "", [], $locale);
                    $notiMsg2 = str_replace("%%ORDER_NUMBER%%", $cash_back_detail->order_unique_id, $notiMsg2);

//debit cashback from wallet
                    $arrWalletAmt = array();
                    $arrWalletAmt['user_id'] = $cash_back_detail->customer_id;
                    $arrWalletAmt['order_id'] = $cash_back_detail->id;
                    $arrWalletAmt['transaction_amount'] = $cash_back_detail->discount;
                    $arrWalletAmt['final_amout'] = $cash_back_detail->discount;
                    $arrWalletAmt['trans_desc'] = $notiMsg2;
                    $arrWalletAmt['transaction_type'] = '1';
                    $arrWalletAmt['flag'] = '3';
                    $arrWalletAmt['payment_type'] = '2';
                    $wallet_detail = UserWalletDetail::create($arrWalletAmt);

                    if (isset($wallet_detail) && count($wallet_detail) > 0) {

//sending push notification to driver
                        $customer_details = User::where('id', $cash_back_detail->customer_id)->first();
                        $arr_push_message_ios = array();
                        $arr_push_message_ios = array("sound" => "iOSSound.wav", 'title' => $site_title, "text" => $notiMsg2, "flag" => 'expired_cashback', 'message' => $notiMsg2, 'order_id' => $cash_back_detail->id);
                        $obj_send_push_notification = new SendPushNotification();
                        if (isset($customer_details->userInformation->device_id) && $customer_details->userInformation->device_id != '') {
                            if ($customer_details->userInformation->device_type == '0') {
//sending push notification customer user.                                
                                $arr_push_message_android = [];
                                $arr_push_message_android = ["data" => ['body' => $notiMsg2, 'title' => $site_title, "flag" => 'expired_cashback', 'order_id' => $cash_back_detail->id], 'notification' => ['body' => $notiMsg2, 'title' => $site_title]];
                                $obj_send_push_notification->androidPushNotification($arr_push_message_android, $customer_details->userInformation->device_id, $customer_details->userInformation->user_type);
                            } else {
                                $arr_push_message_ios['to'] = $customer_details->userInformation->device_id;
                                $arr_push_message_ios['priority'] = "high";
                                $arr_push_message_ios['sound'] = "iOSSound.wav";
                                $arr_push_message_ios['notification'] = $arr_push_message_ios;
                                $obj_send_push_notification->iOSPushNotificaton(json_encode($arr_push_message_ios));
                            }
                        }
                    }
                }
            }
        }
    }

    public function noShowSuspensionTimer($user_id = '') {
        $canelled_order = Order::where(['status' => 6, 'driver_id' => $user_id])->orderBy('id', 'desc')->first();
        if (isset($canelled_order) && count($canelled_order) > 0) {
            $arrUser = UserInformation::where('user_id', $canelled_order->driver_id)->first();
            if ($arrUser->no_show_date_time != "") {
                $region_info = CountryZoneService::where(['service_id' => $canelled_order->service_id, 'zone_id' => $canelled_order->zone_id])->first();
                if (isset($region_info) && count($region_info) > 0) {
//cancellation date time
                    $cancellation_date = new DateTime(date($arrUser->no_show_date_time));
                    $cancel_date = $cancellation_date->format('Y-m-d H:i:s');
//no show suspension time

                    $no_show_date = $cancellation_date->modify($region_info->driver_no_show_suspension_time . " minutes");
                    $no_show_date = $no_show_date->format('Y-m-d H:i:s');

//current time
                    $current_time = date('Y-m-d H:i:s');

                    $can_time = strtotime($cancel_date);
                    $to_time = strtotime($no_show_date);
                    $from_time = strtotime($current_time);
                    if (($from_time >= $can_time) && ($from_time <= $to_time)) {
                        $diff_min = round(abs($to_time - $from_time) / 60, 2) . " minute";
                        return $diff_min;
                    }
                }
            }
        }
    }

    public function logoutFromOtherDevices(Request $request) {
        $locale = $request['locale'];
        \App::setLocale($locale);
        $user_id = $request['user_id'];
        $device_id = $request['device_id'];
        $userData = DB::table('user_informations')
                ->select('user_id', 'device_id')
                ->where(['user_id' => $user_id, 'device_id' => $device_id])
                ->first();
        if ($userData) {
            $arr_to_return = array("error_code" => 0, "msg" => Lang::choice('', "", [], $locale));
            return response()->json($arr_to_return);
        } else {
            $arr_to_return = array("error_code" => 1, "msg" => Lang::choice('messages.you_have_logout_from_other_devices', "", [], $locale));
            return response()->json($arr_to_return);
        }
    }

    public function noOfRejectionForDriver() {
        
        $today_date = date("Y-m-d");
        $locale = config('app.locale');
        $order_cancellation_details = OrderCancelationDetail::where('canceled_by', '2')->get();
        $no_of_rejection = GlobalValues::get('no_of_rejection');
//get current month order details
        $order_cancellation_details = $order_cancellation_details->filter(function ($order) use ($today_date) {
            return date("Y-m", strtotime($order->created_at)) == date("Y-m", strtotime($today_date));
        });
        foreach ($order_cancellation_details as $order) {
//get driver rejection count
            $driver_details = $order_cancellation_details->filter(function ($order_data) use ($order) {
                return ($order_data->user_id) == ($order->user_id);
            });
            if (count($driver_details) >= $no_of_rejection) {
                $user_info = userInformation::where('user_id', $order->user_id)->where('user_type', 2)->first();
                if (isset($user_info) && count($user_info) > 0) {
                    $user_info->user_status = 3;
                    $user_info->save();

                    $user_suspended_reason = new UserSuspendedReason();
                    $user_suspended_reason->user_id = $order->user_id;
                    $user_suspended_reason->reason = Lang::choice('messages.order_rejection_limit', "", [], $locale);
                    $user_suspended_reason->save();
                }
            }
        }
    }

    public function getCurrentMonthRides($driver_id = '') {
        $order_fare_details = OrderFareCalculation::where('driver_id', $driver_id)->where('status', '1')->get();
        if (isset($order_fare_details) && count($order_fare_details)) {
            $order_fare_details = $order_fare_details->filter(function ($order) {
                $today_date = date("Y-m-d");
                return date("Y-m", strtotime($order->created_at)) == date("Y-m", strtotime($today_date));
            });
        }
        $no_of_ride = empty($order_fare_details) ? 0 : count($order_fare_details);
        return $no_of_ride;
    }

    public function getPreviousMonthTargetAchieve($driver_id = '') {
        $target = 0.00;
        $taget_details = MonthlyDriverAmountEvaluation::where('driver_id', $driver_id)->get();
        if (isset($taget_details) && count($taget_details)) {
            $taget_details = $taget_details->filter(function ($target) {
                return date("m-d", strtotime($target->to) >= strtotime("first day of previous month")) && date("m-d", strtotime($target->from) <= strtotime("last day of previous month"));
            });
            $target_details = end($taget_details);
            if ($target_details[0]->percent_target_achievement != "") {
                $target = $target_details[0]->percent_target_achievement;
            }
        }
        return $target;
    }

    public function getCreditOnReferralCodeToCustomer($customer_id = '', $locale = '') {
        $order_details = Order::where('customer_id', $customer_id)->where('status', 2)->first();
        if (count($order_details) <= 0) {
            $check_existing_reward = UserReferencingReward::where('user_id', $customer_id)->first();
            //add reward in wallet

            if (isset($check_existing_reward) && count($check_existing_reward) > 0) {
                $userReferral_info = UserInformation::where("user_id", $check_existing_reward->referred_by)->first();
                if (isset($userReferral_info) && count($userReferral_info) > 0) {
                    if ($userReferral_info->referral_code != "") {
                        $amount = 0.00;
                        if ($userReferral_info->user_type == 3) {
                            $amount = GlobalValues::get('passenger_reward_amount');
                        }
                        if ($userReferral_info->user_type == 2) {
                            $amount = GlobalValues::get('driver_reward_amount');
                        }
                        // add amount in wallet
                        $userBalance = GlobalValues::userBalance($userReferral_info->user_id);
                        $arrWalletAmt = new UserWalletDetail();
                        $arrWalletAmt->user_id = $userReferral_info->user_id;
                        $arrWalletAmt->transaction_amount = $amount;
                        $arrWalletAmt->final_amout = $amount;
                        $arrWalletAmt->avl_balance = $userBalance + $amount;
                        $arrWalletAmt->flag = '3';
                        $arrWalletAmt->trans_desc = Lang::choice('messages.reward_amount', "", [], $locale);
                        $arrWalletAmt->transaction_type = '0';
                        $arrWalletAmt->payment_type = '2';
                        $arrWalletAmt->save();
                    }
                }
            }
        }
    }

    public function getCreditOnReferralCodeToDriver($driver_id = '', $locale = '') {
        $order_details = Order::where('driver_id', $driver_id)->where('status', 2)->first();
        if (count($order_details) <= 0) {
            $check_existing_reward = UserReferencingReward::where('user_id', $driver_id)->first();
            //add reward in wallet

            if (isset($check_existing_reward) && count($check_existing_reward) > 0) {
                $userReferral_info = UserInformation::where("user_id", $check_existing_reward->referred_by)->first();
                if (isset($userReferral_info) && count($userReferral_info) > 0) {
                    if ($userReferral_info->referral_code != "") {
                        $amount = 0.00;
                        if ($userReferral_info->user_type == 3) {
                            $amount = GlobalValues::get('passenger_reward_amount');
                        }
                        if ($userReferral_info->user_type == 2) {
                            $amount = GlobalValues::get('driver_reward_amount');
                        }
                        // add amount in wallet
                        $userBalance = GlobalValues::userBalance($userReferral_info->user_id);
                        $arrWalletAmt = new UserWalletDetail();
                        $arrWalletAmt->user_id = $userReferral_info->user_id;
                        $arrWalletAmt->transaction_amount = $amount;
                        $arrWalletAmt->final_amout = $amount;
                        $arrWalletAmt->avl_balance = $userBalance + $amount;
                        $arrWalletAmt->flag = '3';
                        $arrWalletAmt->trans_desc = Lang::choice('messages.reward_amount', "", [], $locale);
                        $arrWalletAmt->transaction_type = '0';
                        $arrWalletAmt->payment_type = '2';
                        $arrWalletAmt->save();
                    }
                }
            }
        }
    }

    public function diffInMinutes($on_my_way = '', $completed = '') {
        $difference = 0;
        // $to_time = strtotime($completed);
        //   $from_time = strtotime($on_my_way);      
        $date2 = new DateTime($completed);
        $date1 = new DateTime($on_my_way);
        $diffdate = date_diff($date1, $date2);
        $difference = ((($diffdate->h) * 60) + ($diffdate->i) + (($diffdate->s) / 60));
        return round($difference, 2);
        //return round(abs($to_time - $from_time) / 60, 2);
    }

}
