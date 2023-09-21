<?php

namespace App\PiplModules\orderdetails\Controllers;

use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PiplModules\admin\Models\Country;
use Validator;
use Storage;
use App\UserPaymentMethod;
use App\PiplModules\orderdetails\Models\Order;
use Mail;
use Datatables;
use App\PiplModules\service\Models\Service;
use App\UserServiceInformation;
use DB;
use GlobalValues;
use App\UserInformation;
use DateTime;
use DateTimeZone;
use App\PiplModules\orderdetails\Models\OrderNotification;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use App\PiplModules\orderdetails\Models\UserServiceQuotation;
use App\PanaceaClasses\SendPushNotification;

class OrderController extends Controller {

    public function __construct() {
        $this->middleware('auth');
        \App::setLocale('en');
    }

    public function index(Request $request, $status = '') {

        $all_countries = Country::translatedIn(\App::getLocale())->get();
        $all_services = Service::translatedIn(\App::getLocale())->get();
        return view("orderdetails::list", array("status" => $status, "all_services" => $all_services, "all_countries" => $all_countries));
    }

    public function orderViewQuotes(Request $request, $order_id) {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        return view("orderdetails::quote-list", array("order_id" => $order_id));
    }

    public function getOrderData(Request $request, $status = '') {
        $country_name = $request->country_name;
        $filter_by_week = $request->week_filter;
        $order_filter_by = $request->order_filter_by;
        $order_filter_by_service = $request->order_filter_by_service;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;
        if (Auth::user()) {

            $all_data = Order::all()->sortByDesc("id");
            $all_data = $all_data->reject(function($order_data) use ($status) {

                if ($status != '') {
                    if ($status == 'pending') {
                        return $order_data->status != '0';
                    }
                    if ($status == 'active') {
                        return $order_data->status != '1';
                    }
                    if ($status == 'completed') {
                        return $order_data->status != '2';
                    }
                    if ($status == 'expired') {
                        return $order_data->status != '4';
                    }
                    if ($status == 'cancelled') {
                        return $order_data->status != '3';
                    }
                }
            });

            //filter by country
            if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                if ($country != 17) {
                    $all_data = $all_data->reject(function ($order) use ($country) {

                        return ($order->country_id != $country);
                    });
                }
            }

            if (Auth::user()->userInformation->user_type == '4' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                $all_data = $all_data->reject(function ($order) use ($country) {

                    if ($country != '17') {
                        return ($order->country_id != $country);
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '5' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                $all_data = $all_data->reject(function ($order) use ($country) {

                    if ($country != '17') {
                        return ($order->country_id != $country);
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '6' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                $all_data = $all_data->reject(function ($order) use ($country) {

                    if ($country != '17') {
                        return ($order->country_id != $country);
                    }
                });
            }
            if ($country_name != "") {
                $all_data = $all_data->filter(function($order)use($country_name) {
                    $country_id = \App\PiplModules\admin\Models\CountryTranslation::where('name', $country_name)->first();
                    if (count($country_id) > 0) {
                        return $order->country_id == $country_id->country_id;
                    } else {
                        return $order;
                    }
                });
            }

            //filter by week

            if ($filter_by_week != "") {
                $all_data = $all_data->filter(function($order)use($filter_by_week) {
                    $today_date = date("Y-m-d");
                    $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                    return date("Y-m-d", strtotime($order->created_at)) <= $today_date && date("Y-m-d", strtotime($order->created_at)) >= $seven_days_back;
                });
            }

            if ($order_country_id != "") {
                if ($order_country_id != "17") {
                    $all_data = $all_data->filter(function($order)use($order_country_id) {
                        return $order->country_id == $order_country_id;
                    });
                }
            }


            //filter order by date

            if ($order_start_date != "" && $order_end_date != "") {
                $all_data = $all_data->filter(function($order)use($order_start_date, $order_end_date) {
                    return date("Y-m-d", strtotime($order->created_at)) >= $order_start_date && date("Y-m-d", strtotime($order->created_at)) <= $order_end_date;
                });
            }

            //filter order by days
            if ($order_filter_by != "") {

                $all_data = $all_data->filter(function($all_data)use($order_filter_by) {
                    return ($all_data->status == $order_filter_by);
                });
            }
            if ($order_filter_by_service != "") {

                $all_data = $all_data->filter(function($all_data)use($order_filter_by_service) {
                    return ($all_data->service_id == $order_filter_by_service);
                });
            }

            return Datatables::of($all_data)
                            ->addcolumn('star_user', function($all_data) {
                                if (isset($all_data->getUserStarInformation)) {
                                    return $all_data->getUserStarInformation->first_name . ' ' . $all_data->getUserStarInformation->last_name;
                                } else {
                                    return "-";
                                }
                            })
                            ->addcolumn('mate_user', function($all_data) {
                                if (isset($all_data->getUserMateInformation)) {
                                    return $all_data->getUserMateInformation->first_name . ' ' . $all_data->getUserMateInformation->last_name;
                                } else {
                                    return "-";
                                }
                            })
                            ->addcolumn('service_name', function($all_data) {
                                return $all_data->getServicesDetails->getServiceTransDetails->name;
                            })
                            ->addcolumn('order_type', function($all_data) {
                                $order_type = "Instant";
                                if ($all_data->order_type == '2') {
                                    $order_type = "Scheduled";
                                } else if ($all_data->order_type == '3') {
                                    $order_type = "Pick Now Deliver Later";
                                }
                                return ($order_type);
                            })
                            ->addcolumn('cancellation_charge', function($all_data) {
                                if ($all_data->cancellation_charge > 0) {
                                    return $all_data->cancellation_charge;
                                } else {
                                    return '-';
                                }
                            })
                            ->addcolumn('cancelled_date', function($all_data) {
                                if ($all_data->cancelled_date != '0000-00-00 00:00:00') {
                                    return $all_data->cancelled_date;
                                } else {
                                    return '-';
                                }
                            })
                            ->addcolumn('status', function($all_data) {

                                $order_status = "Pending";
                                if ($all_data->status == 1) {
                                    $order_status = "Active";
                                } else if ($all_data->status == 2) {
                                    $order_status = "Completed";
                                } else if ($all_data->status == 3) {
                                    $order_status = "Cancelled";
                                } else if ($all_data->status == 4) {
                                    $order_status = "Expired";
                                }
                                return $order_status;
                            })
                            ->addcolumn('star_status', function($all_data) {

                                $order_star_status = "Pending";
                                if ($all_data->status_by_star == 1) {
                                    $order_star_status = "Request Accepted";
                                } else if ($all_data->status_by_star == 2) {
                                    $order_star_status = "Picked Up";
                                } else if ($all_data->status_by_star == 3) {
                                    $order_star_status = "Travelling";
                                } else if ($all_data->status_by_star == 4) {
                                    $order_star_status = "Reached Destination";
                                } else if ($all_data->status_by_star == 5) {
                                    $order_star_status = "Delivered";
                                } else if ($all_data->status_by_star == 6) {
                                    $order_star_status = "Waiting";
                                } else if ($all_data->status_by_star == 7) {
                                    $order_star_status = "Stared From Driver user Location";
                                } else if ($all_data->status_by_star == 8) {
                                    $order_star_status = "Delivered To PIckup Location";
                                } else if ($all_data->status_by_star == 9) {
                                    $order_star_status = "Cancel by customer";
                                }
                                return $order_star_status;
                            })
                            ->addcolumn('Quote', function($all_data) {
                                if ($all_data->service_id == '20' || $all_data->service_id == '28') {
                                    $url = url("/admin/order-view-quotes/" . $all_data->id);
                                    return '<a href="' . $url . '" class="btn btn-sm btn-primary">View Quotes</a>';
                                } else {
                                    return "-";
                                }
                            })
                            ->make(true);
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function orderViewQuotesData(Request $request, $order_id) {

        if (Auth::user()) {
            $all_data = UserServiceQuotation::where('order_id', $order_id)->orderBy("id", 'desc')->get();

            return Datatables::of($all_data)
                            ->addcolumn('order_unique_id', function($all_data) {
                                if (isset($all_data->getOrderInformations)) {
                                    return $all_data->getOrderInformations->order_unique_id;
                                } else {
                                    return "-";
                                }
                            })
                            ->addcolumn('star_user', function($all_data) {
                                if (isset($all_data->getUserStarInformation)) {
                                    return $all_data->getUserStarInformation->first_name . ' ' . $all_data->getUserStarInformation->last_name;
                                } else {
                                    return "-";
                                }
                            })
                            ->addcolumn('status', function($all_data) {

                                $status = "Pending";
                                if ($all_data->status == 0) {
                                    $status = "Pending";
                                } else if ($all_data->status == 1) {
                                    $status = "Accepted";
                                } else if ($all_data->status == 2) {
                                    $status = "Rejected";
                                }
                                return $status;
                            })
                            ->make(true);
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function orderDetails($order_id) {
        if (Auth::user()) {
            $order_details = Order::find($order_id);
            return view("orderdetails::view", array('oder_details' => $order_details));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function ListRejection($order_id) {
        return view("orderdetails::rejectionList", array("order_id" => $order_id));
    }

    public function rejectionDetails($order_id) {

        if (Auth::user()) {
            $order_details = Order::find($order_id);
            $list_data = $order_details->getOrderCancellations;
            /* create datatable */
            return Datatables::of($list_data)
                            ->addcolumn('message', function($list_data) {
                                return $list_data->reason_text;
                            })
                            ->addcolumn('reject_by', function($list_data) {
                                $reject_by = UserInformation::where('user_id', $list_data->user_id)->first();
                                return $reject_by->first_name . " " . $reject_by->last_name;
                            })
                            ->addcolumn('created_at', function($list_data) {
                                return $list_data->created_at;
//                                return date('Y-m-d',  strtotime($notification_details->created_at));
                            })
                            ->make(true);
            /* create datatable */
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function ListNotification($order_id) {
        return view("orderdetails::notificationDetails", array("order_id" => $order_id));
    }

    public function notificationDetails($order_id) {

        if (Auth::user()) {
            $notification_details = OrderNotification::where('order_id', $order_id);


            /* create datatable */
            return Datatables::of($notification_details)
                            ->addcolumn('message', function($notification_details) {

                                return $notification_details->message;
                            })
                            ->addcolumn('sent_to', function($notification_details) {
                                $sent_to = UserInformation::where('user_id', $notification_details->user_id)->first();
                                return $sent_to->first_name . " " . $sent_to->last_name;
                            })
                            ->addcolumn('created_at', function($notification_details) {

                                return $notification_details->created_at;
//                                return date('Y-m-d',  strtotime($notification_details->created_at));
                            })
                            ->make(true);
            /* create datatable */


//            return view("orderdetails::notificationdetails", array('notification_details' => $notification_details));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function deleteSelectedOrder($order_id) {
        $order_id = Order::find($order_id);
        if ($order_id) {
            $order_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function deleteSelectedNotification($notification_id) {
        $notification_id = OrderNotification::find($notification_id);
        if ($notification_id) {
            $notification_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function deleteSelectedOrderQuotes($quote_id) {
        $order_quote_id = UserServiceQuotation::find($quote_id);
        if ($order_quote_id) {
            $order_quote_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function assignStarForOrder(Request $request, $order_id_in = 0, $driver_id = 0) {
        
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $assign_to = isset($request['assign_to']) ? $request['assign_to'] : '0';
        if ($order_id == '0') {
            $order_id = $order_id_in;
        }
        if ($assign_to == '0') {
            $assign_to = $driver_id;
        }
        $data_values = $request->all();
        if ($order_id_in == 0) {

            $validate_response = Validator::make($data_values, array(
                        'assign_to' => 'required'
            ));
        } else {
            $validate_response = Validator::make($data_values, array(
            ));
        }
        if ($validate_response->fails()) {
            return redirect('admin/assign-star/' . $order_id)
                            ->withErrors($validate_response)
                            ->withInput();
        } else {
            if ($order_id > 0 && $assign_to > 0) {
                $order_details = Order::where('id', $order_id)->first();
                $check_any_order_already_assigned = OrderNotification::where('user_id', $assign_to)->first();
                if (count($check_any_order_already_assigned) <= 0) {
                    if ($order_details->status == 0) {

                        if ((!$order_details->driver_id > 0)) {

                            //storing that user in notification table.
                            
                            
                            $dt = new DateTime(date('Y-m-d H:i:s'));
                            $countryInfo = Country::where('id', $order_details->country_id)->first();
                            if (isset($countryInfo) && count($countryInfo) > 0) {
                                $tz = new DateTimeZone($countryInfo->time_zone); // or whatever zone you're after
                                $dt->setTimezone($tz);
                            }

                            $date2_val = $dt->format('Y-m-d H:i:s');
                            
                            $date2 = new DateTime($date2_val);                           
                            $notification_start_time = $date2_val;
                            $notification_end_time = date("Y-m-d H:i:s", strtotime($date2_val . ' + ' . GlobalValues::get('star-reject-time') . ' min'));
                            $arrOrderNotificationDetails['order_id'] = $order_id;
                            $arrOrderNotificationDetails['user_id'] = $assign_to;
                            $arrOrderNotificationDetails['created_at'] = $date2;
                            $arrOrderNotificationDetails['updated_at'] = $date2;
                            $arrOrderNotificationDetails['message'] = $order_details->order_unique_id . " has been assigned to you.";
                            OrderNotification::create($arrOrderNotificationDetails);

                            //$order_details->driver_id = $assign_to;
                            //  $order_details->status = 1;
                            //  $order_details->status_by_star = 1;
                            // $order_details->save();
                            //sending push noification to star
                            $available_star_details = UserInformation::where('user_id', $assign_to)->first();
                            $arr_push_message_ios = array();
                            if (isset($available_star_details->user_id)) {
                                if ($order_details->service_id != '20' && $order_details->service_id != '28') {
                                    $arr_push_message = array("title" => "BAGGI", "text" => 'A new order has been assigned to you by admin, Please check the order details.', "flag" => 'order_post', 'message' => 'A new order has been assigned to you by admin, Please check the order details.', 'order_id' => $order_details->id, 'notification_start_time' => $notification_start_time, 'notification_end_time' => $notification_end_time);
                                } else {
                                    $arr_push_message = array("title" => "BAGGI", "text" => 'A new order has been assigned to you, Please check the order details.', "flag" => 'order_quotation_request', 'message' => 'A new order has been assigned to you to admin, Please check the order details.', 'order_id' => $order_details->id, 'notification_start_time' => $notification_start_time, 'notification_end_time' => $notification_end_time);
                                }
                                if (isset($available_star_details->device_id) && $available_star_details->device_id != '') {
                                    $obj_send_push_notification = new SendPushNotification();
                                    if ($available_star_details->device_type == '0') {
                                        //sending push notification star user.
                                        $arr_push_message_android = array();
                                        $arr_push_message_android['to'] = $available_star_details->device_id;
                                        $arr_push_message_android['priority'] = "high";
                                        $arr_push_message_android['sound'] = "default";
                                        $arr_push_message_android['notification'] = $arr_push_message;
                                        $obj_send_push_notification->androidPushNotificatonStar(json_encode($arr_push_message_android));
                                    } else {
                                        $arr_push_message_ios['to'] = $available_star_details->device_id;
                                        $arr_push_message_ios['priority'] = "high";
                                        $arr_push_message_ios['sound'] = "default";
                                        $arr_push_message_ios['notification'] = $arr_push_message;
                                        $obj_send_push_notification->iOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                    }
                                }
                            }
                        }
                        return redirect("admin/order-list/pending")->with("status", "Driver user has been successfully assigned to selected order.");
                    } else {
                        return redirect("admin/order-list/pending")->with("status_error", "Sorry this order is already assigned to some star.");
                    }
                } else {
                    return redirect("admin/order-list/pending")->with("status_error", "Sorry this star is already have some order request.");
                }
            }
        }
        return redirect("admin/order-list/pending");
        
    }

    public function getStarForOrder(Request $request) {
        $order_id = isset($request['order_id']) ? $request['order_id'] : '0';
        $order_details = Order::where('id', $order_id)->first();
        $current_lat = '';
        $current_long = '';
        $radious = GlobalValues::get('star-range-radious-admin');
        if (isset($order_details->service_id)) {
            $current_lat = $order_details->getOrderTransInformation->selected_pickup_lat;
            $current_long = $order_details->getOrderTransInformation->selected_pickup_long;
            $arrserviceAvailableUsers = array();
            $arrServiceUsers = UserServiceInformation::where('service_id', $order_details->service_id)->get();

            $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                if (isset($userInfo->user->starUserInformation->availability))
                    return ($userInfo->user->starUserInformation->availability == 0);
            });

            //get all user who has only 50 km range
            if (count($arrServiceUsers) > 0) {
                $user_ids = "0";
                $arrayUserIds = array();
                foreach ($arrServiceUsers as $users_ids) {
                    if (isset($users_ids->user_id) && $users_ids->user_id != 0) {
                        $user_ids .= ",$users_ids->user_id";
                        $arrayUserIds[] = $users_ids->user_id;
                    }
                }
                $users = array();
                if ($current_lat != '' && $current_long != '') {
                    //
                    if ($current_lat == 'null') {
                        $current_lat = "0";
                    }
                    if ($current_long == 'null') {
                        $current_long = "0";
                    }
                    $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");
                }


                //check if a user is having any active orders
                if (count($users) > 0) {
                    $j = 0;
                    foreach ($users as $user) {
                        if (in_array($user->user_id, $arrayUserIds)) {
                            //checkUser Status
                            $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();
                            if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                $statusArr = array("1", "2", "3", "4", "6", "7", "8", "9");
                                $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->whereIn('status_by_star', $statusArr)->first();
                                $order_notification_count = OrderNotification::where('user_id', $user->user_id)->first();
                                $payment_method_id = isset($order_details->payment_type) ? $order_details->payment_type : '0';
                                $paymentMethods = UserPaymentMethod::where('user_id', $user->user_id)->where('payment_method_id', $payment_method_id)->first();

                                if (count($userData) <= 0 && count($order_notification_count) <= 0 && count($paymentMethods) > 0) {

                                    $arrserviceAvailableUsers[$j]['user_id'] = $user->user_id;
                                    $arrserviceAvailableUsers[$j]['distance'] = round($user->distance, 2);
                                    $arrserviceAvailableUsers[$j]['first_name'] = $userDetailsStatus->first_name;
                                    $arrserviceAvailableUsers[$j]['last_name'] = $userDetailsStatus->last_name;
                                }
                            }
                            $j++;
                        }
                    }
                }
            }

//             //getting all order data 
//            $availablestarData="";
//            foreach ($allOrderMap as $order) {
//                $availablestarData.="['";
//                $availablestarData.="order Number:-" . $order->order_unique_id . "<br>";
//                $availablestarData.="order Posted Date:-" . $order->created_at . "<br>";
//                $availablestarData.="order Date:-" . $order->order_place_date_time . "<br>";
//                $availablestarData.='<a targe="_blank" href="order-view/' . $order->id . '">View More</a><br>';
//                $availablestarData.="'";
//                $availablestarData.=",";
//                $availablestarData.=$order->getOrderTransInformation->selected_pickup_lat;
//                $availablestarData.=",";
//                $availablestarData.=$order->getOrderTransInformation->selected_pickup_long;
//                $availablestarData.="],";
//            }
        } else {
            $arr_to_return = array("error_code" => 1);
        }

        $arr_to_return = array("error_code" => 0, "data" => $arrserviceAvailableUsers);
        echo json_encode($arr_to_return);
    }

    public function getStarForOrderPage(Request $request, $order_id) {
        $order_id = isset($order_id) ? $order_id : '0';
        $order_details = Order::where('id', $order_id)->first();
        if (isset($order_details->id) && $order_details->id != '') {
            $current_lat = '0';
            $current_long = '0';
            $arrserviceAvailableUsers = array();
            $availablestarData = "";
            $radious = GlobalValues::get('star-range-radious-admin');
            if (isset($order_details->service_id)) {
                $current_lat = $order_details->getOrderTransInformation->selected_pickup_lat;
                $current_long = $order_details->getOrderTransInformation->selected_pickup_long;

                $arrServiceUsers = UserServiceInformation::where('service_id', $order_details->service_id)->get();

                $arrServiceUsers = $arrServiceUsers->reject(function ($userInfo) {
                            if (isset($userInfo->user->starUserInformation->availability))
                                return ($userInfo->user->starUserInformation->availability == 0);
                        })->values();

                //get all user who has only 50 km range
                       
                if (count($arrServiceUsers) > 0) {
                    $user_ids = "0";
                    $arrayUserIds = array();
                    foreach ($arrServiceUsers as $users_id) {
                        if (isset($users_id->user_id) && $users_id->user_id != 0) {
                            $user_ids .= ",$users_id->user_id";
                            $arrayUserIds[] = $users_id->user_id;
                        }
                    }

                    $users = array();
                    if ($current_lat != '' && $current_long != '') {
                        //
                        if ($current_lat == 'null') {
                            $current_lat = "0";
                        }
                        if ($current_long == 'null') {
                            $current_long = "0";
                        }

                        $users = DB::select("call getUserByDistance(" . $current_lat . "," . $current_long . ",'" . $user_ids . "'," . $radious . ")");
                    }
                    
                    //check if a user is having any active orders
                    $availablestarData = "";
                    if (count($users) > 0) {
                        $j = 0;
                        foreach ($users as $user) {
                            if (in_array($user->user_id, $arrayUserIds)) {
                                //checkUser Status
                                $userDetailsStatus = UserInformation::where('user_id', $user->user_id)->first();

                                if ($userDetailsStatus->user_status == '1' && $userDetailsStatus->user_type == '2') {
                                    $statusArr = array("1", "2", "3", "4", "6", "7", "8", "9");
                                    $userData = Order::where('driver_id', $user->user_id)->where('status', '1')->whereIn('status_by_star', $statusArr)->first();

                                    $order_notification_count = OrderNotification::where('user_id', $user->user_id)->first();
                                    
                                    if (count($userData) <= 0 && count($order_notification_count) <= 0) {

                                        $arrserviceAvailableUsers[$j]['user_id'] = $user->user_id;
                                        $arrserviceAvailableUsers[$j]['distance'] = round($user->distance, 2);
                                        $arrserviceAvailableUsers[$j]['first_name'] = $userDetailsStatus->first_name;
                                        $arrserviceAvailableUsers[$j]['last_name'] = $userDetailsStatus->last_name;

                                        $availablestarData .= "['";
                                        $availablestarData .= "Driver user Name:-" . $userDetailsStatus->first_name . " " . $userDetailsStatus->last_name . "<br>";
                                        $availablestarData .= "Driver user Mobile:-" . "+" . str_replace("+", "", $userDetailsStatus->mobile_code) . " " . $userDetailsStatus->user_mobile . "<br>";
                                        $url_assign = "assign-star-to-order/" . $order_details->id . "/" . $userDetailsStatus->user_id;
                                        $order_confirm = "return confirm(\'Do you really want to assign this order to selected driver?\')";
                                        $availablestarData .= '<a  href="' . $url_assign . '" onclick="' . $order_confirm . '">Assign Trip</a><br>';
                                        $availablestarData .= "'";
                                        $availablestarData .= ",";
                                        $availablestarData .= $user->latitude;
                                        $availablestarData .= ",";
                                        $availablestarData .= $user->longitude;
                                        $availablestarData .= ",";
                                        $availablestarData .= "' " . $userDetailsStatus->first_name . " " . $userDetailsStatus->last_name . " (" . (round($user->distance, 2) . "Km)") . "'";
                                        $availablestarData .= "],";
                                    }
                                }
                                $j++;
                            }
                        }
                    }
                }
            } else {
                $arr_to_return = array("error_code" => 1);
            }
            
            $availablestarData = substr($availablestarData, 0, -1);

            return view("orderdetails::assign-star", array('availablestarData' => $availablestarData, 'order_details' => $order_details, 'available_stars' => $arrserviceAvailableUsers));
        } else {
            return redirect("admin/order-list");
            exit;
        }
    }

    public function IOSPushNotificatonStar($arrayToSend) {
        $fcmApiKey = 'AIzaSyChETgkXysMMEje6m6ei6-OqlvUEkA95Uk'; //App API Key(This is google cloud messaging api key not web api key)
        $url = 'https://fcm.googleapis.com/fcm/send'; //Google URL
        //Fcm Device ids array

        $headers = array(
            'Authorization: key=' . $fcmApiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayToSend);
        // Execute post
        $result = curl_exec($ch);

        // if ($result === FALSE) {
        //     die('Curl failed: ' . curl_error($ch));
        // }
        // Close connection
        curl_close($ch);
        return "1";
    }

}
