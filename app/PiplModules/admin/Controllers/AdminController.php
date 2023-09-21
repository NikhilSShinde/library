<?php

namespace App\PiplModules\admin\Controllers;

use Session;
use App\User;
use App\GeoLimit;
use App\PaymentMethod;
use App\UserPaymentMethod;
use App\UserInformation;
use App\DriverUserInformation;
use App\UserAddress;
use App\DriverDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Auth;
use Mail;
use Hash;
use App\Nationality;
use Datatables;
use App\PiplModules\roles\Models\Role;
use App\PiplModules\ratingreview\Models\UserRatingInformation;
use App\PiplModules\roles\Models\Permission;
use App\PiplModules\admin\Models\GlobalSetting;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\admin\Models\State;
use App\PiplModules\admin\Models\City;
use App\PiplModules\admin\Models\UserCustomNotification;
use App\PiplModules\contactrequest\Models\ContactRequest;
use GlobalValues;
use Storage;
use App\PiplModules\category\Models\Category;
use App\PiplModules\service\Models\Service;
use App\PiplModules\supporttickets\Models\SupportTicket;
use App\UserServiceInformation;
use Twilio;
use PDF;
use TwilioException;
use App\PiplModules\admin\Models\CountryServices;
use App\PiplModules\admin\Models\SpokenLanguage;
use App\PiplModules\admin\Models\SpokenLanguageTranslation;
use App\UserSpokenLanguageInformation;
use App\DriverPendingAmount;
use App\CompanyInformation;
use App\PiplModules\orderdetails\Models\Order;
use Lang;
use Cache;
use App\PiplModules\vehicle\Models\UserVehicleInformation;
use App\PiplModules\vehicle\Models\DriverAssignedDetail;
use DateTime;
use App\PiplModules\admin\Models\UserPaymentReceivedDetail;
use App\PiplModules\wallethistory\Models\UserWalletDetail;
use App\DeliveryuserBalanceDetail;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use App\PanaceaClasses\AppNotification;
use App\Notification;

class AdminController extends Controller {

    /**
     * Show the login window for admin.
     *
     * @return Response
     */
    public function __construct() {

        \App::setLocale('en');
    }

    protected function validator(Request $request) {
        //only common files if we have multiple registration
        $this->middleware('auth', ['except' => array('showLogin')]);
        return Validator::make($request, [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'gender' => 'required',
        ]);
    }

    public function logout() {
        $successMsg = "You have logged out successfully!";
        Auth::logout();
        return redirect("admin/login")->with("register-success", $successMsg);
    }

    public function showLogin() {
        Session::put('support_chat_access', '0');
        return view('admin::login');
    }

    public function showLoginChat(Request $request) {
        if ($request->method() == "GET") {
            return view('admin::login_support_chat');
        }
    }

    public function showSupportChat() {
        if (Auth::user()) {
            if (Session::get('support_chat_access') != '1') {
                return redirect("admin/login-chat");
            } else {
                return view('admin::support-chat');
            }
        } else {
            return redirect("admin/login-chat");
        }
    }

    public function showDashboard() {
        \App::setLocale('en');

        if (Auth::user()) {
            if (Auth::user()->userInformation->user_type == "4") {
                return redirect("agent/dashboard");
                exit;
            } else if (Auth::user()->userInformation->user_type == "2") {
                return redirect("home");
                exit;
            } else if (Auth::user()->userInformation->user_type == "3") {
                return redirect("home");
                exit;
            } else if (Auth::user()->userInformation->user_type == "5") {
                return redirect("company/dashboard");
                exit;
            } else if (Auth::user()->userInformation->user_type == "6") {
                return redirect("agent-manager/dashboard");
                exit;
            } else if (Auth::user()->userInformation->user_type == "7") {
                return redirect("free-toner/dashboard");
                exit;
            }
            $all_users = UserInformation::all();
            $company_user = $all_users->reject(function ($user) {
                return ($user->user_type != '5');
            });
            $all_orders = Order::all();

            $pending_orders = $all_orders->reject(function($order) {
                return $order->status != "0";
            });
            $active_orders = $all_orders->reject(function($order) {
                return $order->status != "1";
            });
            $completed_orders = $all_orders->reject(function($order) {
                return $order->status != "2";
            });

            $support_data_count = SupportTicket::where('status', '0')->get();
            $allCountries = Country::all();
            $allOrderMap = Order::where('status', '0')->get();
            $allOrderHeatMap = Order::all();
            $country = '';
            if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                if ($country != 17) {
                    $company_user = $all_users->reject(function ($user) use ($country) {
                        $company_country = 0;
                        if ($user->user->userAddress) {

                            foreach ($user->user->userAddress as $address) {
                                $company_country = $address->user_country;
                            }
                        }
                        return ($user->user_type != '5' || $company_country != $country);
                    });
                }

                if ($country != 17 && $country != '') {
                    $pending_orders = $all_orders->reject(function($order) use ($country) {
                        return ($order->country_id != $country || $order->status != 0);
                    });
                    $active_orders = $all_orders->reject(function($order) use ($country) {
                        return $order->country_id != $country || $order->status != "1";
                    });
                    $completed_orders = $all_orders->reject(function($order) use ($country) {
                        return $order->country_id != $country || $order->status != "2";
                    });
                    $support_data_count = $support_data_count->reject(function($support) use ($country) {
                        return $support->orderInformation->country_id != $country;
                    });
                    $allCountries = $allCountries->reject(function($countryDataList) use ($country) {
                        return $countryDataList->id != $country;
                    });
                    $allOrderMap = $allOrderMap->reject(function($ordermap) use ($country) {
                        return $ordermap->country_id != $country;
                    });
                }
            }
            if (Auth::user()->userInformation->user_type == '1' && ((Auth::user()->hasRole('superadmin'))) && (Auth::user()->hasRole('superadmin'))) {


                if (Auth::user()->userAddress) {
                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                if ($country != 17 && $country != '') {
                    $company_user = $all_users->reject(function ($user) use ($country) {
                        $company_country = 0;
                        if ($user->user->userAddress) {

                            foreach ($user->user->userAddress as $address) {
                                $company_country = $address->user_country;
                            }
                        }
                        return ($user->user_type != '5' || $company_country != $country);
                    });
                }

                if ($country != 17 && $country != '') {
                    $pending_orders = $all_orders->reject(function($order) use ($country) {
                        return ($order->country_id != $country || $order->status != 0);
                    });
                    $active_orders = $all_orders->reject(function($order) use ($country) {
                        return $order->country_id != $country || $order->status != "1";
                    });
                    $completed_orders = $all_orders->reject(function($order) use ($country) {
                        return $order->country_id != $country || $order->status != "2";
                    });
                    $support_data_count = $support_data_count->reject(function($support) use ($country) {
                        return $support->orderInformation->country_id != $country;
                    });
                    $allCountries = $allCountries->reject(function($countryDataList) use ($country) {
                        return $countryDataList->id != $country;
                    });
                    $allOrderMap = $allOrderMap->reject(function($ordermap) use ($country) {
                        return $ordermap->country_id != $country;
                    });
                }
            }

            $count_pending_orders = count($pending_orders);
            $count_active_orders = count($active_orders);
            $count_support_count = count($support_data_count);
            $count_completed_count = count($completed_orders);


            //get support ticket count
            $order_count = array(
                "active_count" => $count_active_orders,
                "pending_count" => $count_pending_orders,
                "completed_count" => $count_completed_count,
                "support_count" => $count_support_count
            );

            $admin_users = $all_users->reject(function ($user) {
                if (isset($user->user)) {
                    return (($user->user->hasRole('registered.user') === true) || $user->user->hasRole('superadmin') || (($user->user_type != 1)));
                }
            });
            if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {
                $userAddress = UserAddress::where('user_id', Auth::user()->id)->first();
                $country = 0;
                if (isset($userAddress->user_country) && $userAddress->user_country != 17) {
                    $country = $userAddress->user_country;
                    $admin_users = $all_users->reject(function ($user) use($country) {

                        $user_country = 0;
                        if ($user->user->userAddress) {

                            foreach ($user->user->userAddress as $address) {
                                $user_country = $address->user_country;
                            }
                        }
                        return $user->user->hasRole('superadmin') || ($user->user_type > 1) || ($user_country != $country) || ($user->id == Auth::user()->id) || ($user->user->supervisor_id != Auth::user()->id);
                    });
                }
            }

            $agent_users = $all_users->reject(function ($user) {

                return ($user->user_type != '4');
            });

            if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                if ($country != 17) {
                    $agent_users = $all_users->reject(function ($user) use ($country) {
                        $agent_country = 0;
                        if ($user->user->userAddress) {

                            foreach ($user->user->userAddress as $address) {
                                $agent_country = $address->user_country;
                            }
                        }
                        return ($user->user_type != '4' || $agent_country != $country);
                    });
                }
            }
            $star_users = $all_users->reject(function ($user) {
                return ($user->user_type != '2');
            });
            //countries Data


            if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
                if ($country != 17) {
                    $star_users = $all_users->reject(function ($user) use ($country) {
                        $star_country = 0;
                        if ($user->user->userAddress) {

                            foreach ($user->user->userAddress as $address) {
                                $star_country = $address->user_country;
                            }
                        }
                        return ($user->user_type != '2' || $star_country != $country);
                    });
                }
            }

            $mate_users = $all_users->reject(function ($user) {
                if (isset($user->user)) {
                    return ($user->user->hasRole('registered.user') == false || $user->user_type != '3');
                }
            });


            if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {
                $country_code = 0;
                $country = 0;
                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                        $countryData = Country::where('id', $country)->first();
                        $country_code = str_replace("+", "", $countryData->country_code);
                    }
                }
                $mate_users = UserInformation::where("user_type", 3)->get();
                if ($country != 17 && $country != 0) {

                    $mate_users = $mate_users->reject(function ($user) use ($country_code) {

                        return (($user->mobile_code != $country_code));
                    });
                }
            }

            $company_user_count = count($company_user);
            $agent_user_count = count($agent_users);
            $star_user_count = count($star_users);
            $mate_user_count = count($mate_users);
            $admin_user_count = count($admin_users);


            //Get All orders
            $countryOrderData = array();
            if (count($allCountries) > 0) {

                $i = 0;
                foreach ($allCountries as $country) {
                    $all_orders = Order::where('country_id', $country->id)->get();

                    $countryOrderData[$i]["title"] = $country->name;
                    $countryOrderData[$i]["value"] = count($all_orders);
                    $i++;
                }
            }
            $countryOrderRevenueData = array();

            //getting order coun as per month
            $current_month = date('m');
            $star_month = 1;
            if ($current_month > 6) {
                $star_month = ($current_month - 6);
            }
            $counter = 0;

            while (1) {
                $month_name = date('F', mktime(0, 0, 0, $star_month, 10));
                $countryOrderRevenueData[$counter]['month'] = $month_name;
                if (count($allCountries) > 0) {
                    foreach ($allCountries as $country) {


                        if ($country->id != 17) {


                            //get all completed date for this year and month
                            $orderDateWiseAmountSum = Order::whereRaw('MONTH(order_complete_date_time)=' . $star_month)->whereRaw('YEAR(order_complete_date_time)=YEAR(NOW())')->where('country_id', $country->id)->sum('total_amount');
                            $countryOrderRevenueData[$counter][$country->name] = isset($orderDateWiseAmountSum) ? $orderDateWiseAmountSum : '0';
                        }
                    }
                }


                //$countryOrderRevenueData[$j]['month']=$orderDateWiseAmountSum;
                $counter++;
                $star_month++;
                if ($counter == 7) {
                    break;
                }
            }


            //getting all order data 
            $orderLocationData = "";
            foreach ($allOrderMap as $order) {
                $orderLocationData .= "['";
                $orderLocationData .= "Trip Number:-" . $order->order_unique_id . "<br>";
                $orderLocationData .= "Trip Posted Date:-" . $order->created_at . "<br>";
                $orderLocationData .= "Trip Date:-" . $order->order_place_date_time . "<br>";
                $orderLocationData .= '<a targe="_blank" href="order-view/' . $order->id . '">View More</a><br>';
                $orderLocationData .= "'";
                $orderLocationData .= ",";
                if (isset($order->getOrderTransInformation->selected_pickup_lat)) {
                    $orderLocationData .= $order->getOrderTransInformation->selected_pickup_lat;
                }
                $orderLocationData .= ",";
                if (isset($order->getOrderTransInformation->selected_pickup_long)) {
                    $orderLocationData .= $order->getOrderTransInformation->selected_pickup_long;
                }
                $orderLocationData .= ",";
                $orderLocationData .= $order->order_unique_id;
                $orderLocationData .= "],";
            }
            $orderLocationData = substr($orderLocationData, 0, -1);

            //getting all order data  heat map

            $orderLocationDataHeatMap = "";
            if (count($allOrderHeatMap) > 0) {
                foreach ($allOrderHeatMap as $order1) {

                    $orderLocationDataHeatMap .= "new google.maps.LatLng(" . $order1->getOrderTransInformation->selected_pickup_lat . "," . $order1->getOrderTransInformation->selected_pickup_long . "),";


//            
//     
//            
//            $orderLocationData.=",";
//            if(isset($order->getOrderTransInformation->selected_pickup_long))
//           {
//            $orderLocationData.=$order->getOrderTransInformation->selected_pickup_long;
//           }
                }
                $orderLocationDataHeatMap = substr($orderLocationDataHeatMap, 0, -1);
            }

            return view("admin::dashboard", array('orderLocationDataHeatMap' => $orderLocationDataHeatMap, 'countryRevinueData' => json_encode($countryOrderRevenueData), 'orderLocationData' => $orderLocationData, 'countryData' => json_encode($countryOrderData), 'star_users' => $star_user_count, 'mate_users' => $mate_user_count, 'agent_users' => $agent_user_count, 'company_user' => $company_user_count, 'admin_user_count' => $admin_user_count, 'order_counts' => $order_count));
        } else {
            return redirect("admin/login");
        }
    }

    public function showCompanyDashboard() {
        \App::setLocale('en');
        if (Auth::user()) {
            $all_data = Order::all()->sortByDesc("id");
            $all_users = UserInformation::all();
            $country = 0;
            $state = 0;
            $city = 0;

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }

            $star_users = $all_users->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '0') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    if ($city != '22') {
                        $city_passed = ($star_city != $city);
                    }
                    return ($condition || ($contry_passed || $state_passed || $city_passed));
                } else {
                    return $user;
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
            $agent_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 4) || ($user->user->supervisor_id != Auth::user()->id)));
            });

            $company_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 5) || ($user->user->supervisor_id != Auth::user()->id)));
            });
            $mate_users = $all_users->reject(function($user) {
                        $mobile_code = str_replace("+", "", Auth::user()->userInformation->mobile_code);
                        $mobile_code_mate = str_replace("+", "", $user->mobile_code);

                        return ($user->user_type != 3 || ($mobile_code_mate != $mobile_code));
                    })->values();
            $all_data = $all_data->reject(function ($order) use ($country) {

                return ($order->country_id != $country);
            });
            $pending_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '0');
            });
            $active_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '1');
            });
            $completed_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '2');
            });

            $all_SupportTicket = SupportTicket::where('status', '0')->get();
            $all_SupportTicket = $all_SupportTicket->sortByDesc("id");
            if (Auth::user()->userInformation->user_type == '4') {

                $all_SupportTicket = $all_SupportTicket->filter(function ($obj) use($country, $state, $city) {
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($state == '32') {
                        $flag = 1;
                    } else {
                        if ($user_state == $state) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($city == '22') {
                        $flag = 1;
                    } else {
                        if ($user_city == $city) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return (($obj->assignTicketInformation->assign_to == Auth::user()->id) || ($flag == 1));
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '6') {
                $agentuser = User::where('supervisor_id', Auth::user()->id)->get();
                $arrAgentUsers = array();
                if (count($agentuser) > 0) {
                    foreach ($agentuser as $agent) {
                        $arrAgentUsers[] = $agent->id;
                    }
                }

                $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($arrAgentUsers, $country, $state, $city) {
                    //get all user agents
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($state == '32') {
                        $flag = 1;
                    } else {
                        if ($user_state == $state) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($city == '22') {
                        $flag = 1;
                    } else {
                        if ($user_city == $city) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }

                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return ((in_array($obj->assignTicketInformation->assign_to, $arrAgentUsers)) || ($flag == 1));
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '5') {
                $agentuser = User::where('supervisor_id', Auth::user()->id)->get();
                $arrAgentUsers = array();
                if (count($agentuser) > 0) {
                    foreach ($agentuser as $agent) {
                        $arrAgentUsers[] = $agent->id;
                    }
                }
                $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($arrAgentUsers, $country, $state, $city) {
                    //get all user agents
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($state == '32') {
                        $flag = 1;
                    } else {
                        if ($user_state == $state) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($city == '22') {
                        $flag = 1;
                    } else {
                        if ($user_city == $city) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }

                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return ((in_array($obj->assignTicketInformation->assign_to, $arrAgentUsers)) || ($flag == 1));
                    }
                });
            }

            $star_users_count = count($star_users);
            $agent_users_count = count($agent_users);
            $mate_users_count = count($mate_users);
            $company_users_count = count($company_users);
            $pending_order_count = count($pending_order);
            $active_order_count = count($active_order);
            $completed_order_count = count($completed_order);
            $all_supportTicket_count = count($all_SupportTicket);
            return view("admin::company-dashboard", array('all_supportTicket_count' => $all_supportTicket_count, 'active_order_count' => $active_order_count, 'pending_order_count' => $pending_order_count, 'completed_order_count' => $completed_order_count, 'star_users_count' => $star_users_count, "agent_users_count" => $agent_users_count, "mate_users_count" => $mate_users_count, "company_users_count" => $company_users_count));
        } else {
            return redirect("admin/login");
        }
    }

    public function showAgentDashboard() {
        \App::setLocale('en');
        if (Auth::user()) {
            $all_data = Order::all()->sortByDesc("id");
            $all_users = UserInformation::all();
            $country = 0;
            $state = 0;
            $city = 0;

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }

            $star_users = $all_users->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '0') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    if ($city != '22') {
                        $city_passed = ($star_city != $city);
                    }
                    return ($condition || ($contry_passed || $state_passed || $city_passed));
                } else {
                    return $user;
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
            $agent_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 4) || ($user->user->supervisor_id != Auth::user()->id)));
            });

            $company_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 5) || ($user->user->supervisor_id != Auth::user()->id)));
            });
            $mate_users = $all_users->reject(function($user) {
                        $mobile_code = str_replace("+", "", Auth::user()->userInformation->mobile_code);
                        $mobile_code_mate = str_replace("+", "", $user->mobile_code);

                        return ($user->user_type != 3 || ($mobile_code_mate != $mobile_code));
                    })->values();
            $all_data = $all_data->reject(function ($order) use ($country) {

                if ($country != '17') {
                    return ($order->country_id != $country);
                }
            });
            $pending_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '0');
            });
            $active_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '1');
            });
            $completed_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '2');
            });

            $all_SupportTicket = SupportTicket::where('status', '0')->get();
            $all_SupportTicket = $all_SupportTicket->sortByDesc("id");
            if (Auth::user()->userInformation->user_type == '4') {

                $all_SupportTicket = $all_SupportTicket->filter(function ($obj) use($country, $state, $city) {
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }


                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($obj->UserInformation->user_type != '3') {
                        if ($state == '32') {
                            $flag = 1;
                        } else {
                            if ($user_state == $state) {
                                $flag = 1;
                            } else {
                                $flag = 0;
                            }
                        }
                        if ($city == '22') {
                            $flag = 1;
                        } else {
                            if ($user_city == $city) {
                                $flag = 1;
                            } else {
                                $flag = 0;
                            }
                        }
                    }
                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return (($obj->assignTicketInformation->assign_to == Auth::user()->id) || ($flag == 1));
                    } else {
                        return ($flag == 1);
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '6') {
                $agentuser = User::where('supervisor_id', Auth::user()->id)->get();
                $arrAgentUsers = array();
                if (count($agentuser) > 0) {
                    foreach ($agentuser as $agent) {
                        $arrAgentUsers[] = $agent->id;
                    }
                }

                $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($arrAgentUsers, $country, $state, $city) {
                    //get all user agents
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($state == '32') {
                        $flag = 1;
                    } else {
                        if ($user_state == $state) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($city == '22') {
                        $flag = 1;
                    } else {
                        if ($user_city == $city) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }

                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return ((in_array($obj->assignTicketInformation->assign_to, $arrAgentUsers)) || ($flag == 1));
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '5') {
                $agentuser = User::where('supervisor_id', Auth::user()->id)->get();
                $arrAgentUsers = array();
                if (count($agentuser) > 0) {
                    foreach ($agentuser as $agent) {
                        $arrAgentUsers[] = $agent->id;
                    }
                }
                $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($arrAgentUsers, $country, $state, $city) {
                    //get all user agents
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($state == '32') {
                        $flag = 1;
                    } else {
                        if ($user_state == $state) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($city == '22') {
                        $flag = 1;
                    } else {
                        if ($user_city == $city) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }

                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return ((in_array($obj->assignTicketInformation->assign_to, $arrAgentUsers)) || ($flag == 1));
                    }
                });
            }

            $star_users_count = count($star_users);
            $agent_users_count = count($agent_users);
            $mate_users_count = count($mate_users);
            $company_users_count = count($company_users);
            $pending_order_count = count($pending_order);
            $active_order_count = count($active_order);
            $completed_order_count = count($completed_order);
            $all_supportTicket_count = count($all_SupportTicket);
            //getting all order data 
            $allOrderMap = Order::where('status', '0')->get();
            if ($country != 17 && $country != '') {
                $allOrderMap = $allOrderMap->reject(function($ordermap) use ($country) {
                    return $ordermap->country_id != $country;
                });
            }
            $orderLocationData = "";
            foreach ($allOrderMap as $order) {
                $orderLocationData .= "['";
                $orderLocationData .= "Trip Number:-" . $order->order_unique_id . "<br>";
                $orderLocationData .= "Trip Posted Date:-" . $order->created_at . "<br>";
                $orderLocationData .= "Trip Date:-" . $order->order_place_date_time . "<br>";
                $orderLocationData .= '<a targe="_blank" href="order-view/' . $order->id . '">View More</a><br>';
                $orderLocationData .= "'";
                $orderLocationData .= ",";
                if (isset($order->getOrderTransInformation->selected_pickup_lat)) {
                    $orderLocationData .= $order->getOrderTransInformation->selected_pickup_lat;
                }
                $orderLocationData .= ",";
                if (isset($order->getOrderTransInformation->selected_pickup_long)) {
                    $orderLocationData .= $order->getOrderTransInformation->selected_pickup_long;
                }
                $orderLocationData .= ",";
                $orderLocationData .= $order->order_unique_id;
                $orderLocationData .= "],";
            }
            $orderLocationData = substr($orderLocationData, 0, -1);

            return view("admin::agent-dashboard", array('orderLocationData' => $orderLocationData, 'all_supportTicket_count' => $all_supportTicket_count, 'active_order_count' => $active_order_count, 'pending_order_count' => $pending_order_count, 'completed_order_count' => $completed_order_count, 'star_users_count' => $star_users_count, "agent_users_count" => $agent_users_count, "mate_users_count" => $mate_users_count, "company_users_count" => $company_users_count));
        } else {
            return redirect("admin/login");
        }
    }

    public function showAgentManagerDashboard() {
        \App::setLocale('en');
        if (Auth::user()) {
            $all_data = Order::all()->sortByDesc("id");
            $all_users = UserInformation::all();
            $country = 0;
            $state = 0;
            $city = 0;

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }

            $star_users = $all_users->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '0') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    if ($city != '22') {
                        $city_passed = ($star_city != $city);
                    }
                    return ($condition || ($contry_passed || $state_passed || $city_passed));
                } else {
                    return $user;
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
            $agent_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 4) || ($user->user->supervisor_id != Auth::user()->id)));
            });

            $company_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 5) || ($user->user->supervisor_id != Auth::user()->id)));
            });
            $mate_users = $all_users->reject(function($user) {
                        $mobile_code = str_replace("+", "", Auth::user()->userInformation->mobile_code);
                        $mobile_code_mate = str_replace("+", "", $user->mobile_code);

                        return ($user->user_type != 3 || ($mobile_code_mate != $mobile_code));
                    })->values();
            $all_data = $all_data->reject(function ($order) use ($country) {

                if ($country != '17') {
                    return ($order->country_id != $country);
                }
            });
            $pending_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '0');
            });
            $active_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '1');
            });
            $completed_order = $all_data->reject(function ($order) use ($country) {

                return ($order->status != '2');
            });

            $all_SupportTicket = SupportTicket::where('status', '0')->get();
            $all_SupportTicket = $all_SupportTicket->sortByDesc("id");
            if (Auth::user()->userInformation->user_type == '4') {

                $all_SupportTicket = $all_SupportTicket->filter(function ($obj) use($country, $state, $city) {
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($obj->UserInformation->user_type != '3') {
                        if ($state == '32') {
                            $flag = 1;
                        } else {
                            if ($user_state == $state) {
                                $flag = 1;
                            } else {
                                $flag = 0;
                            }
                        }
                        if ($city == '22') {
                            $flag = 1;
                        } else {
                            if ($user_city == $city) {
                                $flag = 1;
                            } else {
                                $flag = 0;
                            }
                        }
                    }

                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return (($obj->assignTicketInformation->assign_to == Auth::user()->id) || ($flag == 1));
                    } else {
                        return ($flag == 1);
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '6') {
                $agentuser = User::where('supervisor_id', Auth::user()->id)->get();
                $arrAgentUsers = array();
                if (count($agentuser) > 0) {
                    foreach ($agentuser as $agent) {
                        $arrAgentUsers[] = $agent->id;
                    }
                }

                $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($arrAgentUsers, $country, $state, $city) {
                    //get all user agents
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($obj->UserInformation->user_type != '3') {
                        if ($state == '32') {
                            $flag = 1;
                        } else {
                            if ($user_state == $state) {
                                $flag = 1;
                            } else {
                                $flag = 0;
                            }
                        }
                        if ($city == '22') {
                            $flag = 1;
                        } else {
                            if ($user_city == $city) {
                                $flag = 1;
                            } else {
                                $flag = 0;
                            }
                        }
                    }


                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return ((in_array($obj->assignTicketInformation->assign_to, $arrAgentUsers)) || ($flag == 1));
                    } else {
                        return (($flag == 1));
                    }
                });
            }
            if (Auth::user()->userInformation->user_type == '5') {
                $agentuser = User::where('supervisor_id', Auth::user()->id)->get();
                $arrAgentUsers = array();
                if (count($agentuser) > 0) {
                    foreach ($agentuser as $agent) {
                        $arrAgentUsers[] = $agent->id;
                    }
                }
                $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($arrAgentUsers, $country, $state, $city) {
                    //get all user agents
                    $user_country = 0;
                    $user_state = 0;
                    $user_city = 0;
                    if ($obj->UserInformation->user->userAddress) {
                        foreach ($obj->UserInformation->user->userAddress as $address) {
                            $user_country = $address->user_country;
                            $user_state = $address->user_state;
                            $user_city = $address->user_city;
                        }
                    }

                    $flag = 0;
                    if ($country == '17') {
                        $flag = 1;
                    } else {
                        if ($user_country == $country) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($state == '32') {
                        $flag = 1;
                    } else {
                        if ($user_state == $state) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }
                    if ($city == '22') {
                        $flag = 1;
                    } else {
                        if ($user_city == $city) {
                            $flag = 1;
                        } else {
                            $flag = 0;
                        }
                    }

                    if (isset($obj->assignTicketInformation->assign_to)) {
                        return ((in_array($obj->assignTicketInformation->assign_to, $arrAgentUsers)) || ($flag == 1));
                    }
                });
            }

            $star_users_count = count($star_users);
            $agent_users_count = count($agent_users);
            $mate_users_count = count($mate_users);
            $company_users_count = count($company_users);
            $pending_order_count = count($pending_order);
            $active_order_count = count($active_order);
            $completed_order_count = count($completed_order);
            $all_supportTicket_count = count($all_SupportTicket);
            //getting all order data 
            $allOrderMap = Order::where('status', '0')->get();
            if ($country != 17 && $country != '') {
                $allOrderMap = $allOrderMap->reject(function($ordermap) use ($country) {
                    return $ordermap->country_id != $country;
                });
            }
            $orderLocationData = "";
            foreach ($allOrderMap as $order) {
                $orderLocationData .= "['";
                $orderLocationData .= "Trip Number:-" . $order->order_unique_id . "<br>";
                $orderLocationData .= "Trip Posted Date:-" . $order->created_at . "<br>";
                $orderLocationData .= "Trip Date:-" . $order->order_place_date_time . "<br>";
                $orderLocationData .= '<a targe="_blank" href="order-view/' . $order->id . '">View More</a><br>';
                $orderLocationData .= "'";
                $orderLocationData .= ",";
                if (isset($order->getOrderTransInformation->selected_pickup_lat)) {
                    $orderLocationData .= $order->getOrderTransInformation->selected_pickup_lat;
                }
                $orderLocationData .= ",";
                if (isset($order->getOrderTransInformation->selected_pickup_long)) {
                    $orderLocationData .= $order->getOrderTransInformation->selected_pickup_long;
                }
                $orderLocationData .= ",";
                $orderLocationData .= $order->order_unique_id;
                $orderLocationData .= "],";
            }
            $orderLocationData = substr($orderLocationData, 0, -1);

            return view("admin::agent-manager-dashboard", array('orderLocationData' => $orderLocationData, 'all_supportTicket_count' => $all_supportTicket_count, 'active_order_count' => $active_order_count, 'pending_order_count' => $pending_order_count, 'completed_order_count' => $completed_order_count, 'star_users_count' => $star_users_count, "agent_users_count" => $agent_users_count, "mate_users_count" => $mate_users_count, "company_users_count" => $company_users_count));
        } else {
            return redirect("admin/login");
        }
        //return view("admin::agent-manager-dashboard", array('registered_users_count' => $resistered_user_count, 'admin_user_count' => $admin_user_count));
    }

    public function showFreeTonerDashboard() {
        \App::setLocale('en');
        $all_users = UserInformation::all();
        $all_vehicles = UserVehicleInformation::where('user_id', Auth::user()->id)->get();

        $star_by_free_toner = $all_users->reject(function ($user) {
            return $user->user_type != 2 || $user->user->supervisor_id != Auth::user()->id;
        });

        $star_by_free_toner_count = count($star_by_free_toner);
        $vehicle_count = count($all_vehicles);

        return view("admin::free-toner-manager-dashboard", array('free_toner_star' => $star_by_free_toner_count, 'vehicle_count' => $vehicle_count));
    }

    public function adminProfile() {
        if (Auth::user()) {
            $arr_user_data = Auth::user();
            return view('admin::profile', array("user_info" => $arr_user_data));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function agentProfile() {
        if (Auth::user()) {
            $arr_user_data = Auth::user();
            return view('admin::agent-profile', array("user_info" => $arr_user_data));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function updateProfile(Request $data) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = Auth::user();
            $validate_response = Validator::make($data_values, array(
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'gender' => 'required'
            ));

            if ($validate_response->fails()) {
                return redirect('admin/profile')
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                // update User Information
                /*
                 * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                 */


                /** user information goes here *** */
                if (isset($data["profile_picture"])) {
                    $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                }
                if (isset($data["gender"])) {
                    $arr_user_data->userInformation->gender = $data["gender"];
                }
                if (isset($data["user_status"])) {
                    $arr_user_data->userInformation->user_status = $data["user_status"];
                }

                if (isset($data["first_name"])) {
                    $arr_user_data->userInformation->first_name = $data["first_name"];
                }
                if (isset($data["last_name"])) {
                    $arr_user_data->userInformation->last_name = $data["last_name"];
                }
                if (isset($data["about_me"])) {
                    $arr_user_data->userInformation->about_me = $data["about_me"];
                }

                if (isset($data["user_mobile"])) {
                    $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                }

                $arr_user_data->userInformation->save();

                $succes_msg = "Your profile has been updated successfully!";
                return redirect("admin/profile")->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function updateAgentProfile(Request $data) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = Auth::user();
            $validate_response = Validator::make($data_values, array(
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'limit_to_pay_to_star' => 'required',
//                        'gender' => 'required'
                            ), array("limit_to_pay_to_star.required" => 'Please enter the star amount limit, on which you will pay to star.'));

            if ($validate_response->fails()) {

                return redirect('agent/profile')
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                // update User Information
                /*
                 * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                 */


                /** user information goes here *** */
                if (isset($data["profile_picture"])) {
                    $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                }
                if (isset($data["limit_to_pay_to_star"])) {
                    $arr_user_data->userInformation->limit_to_pay_to_star = $data["limit_to_pay_to_star"];
                }
                if (isset($data["gender"])) {
                    $arr_user_data->userInformation->gender = $data["gender"];
                }
                if (isset($data["user_status"])) {
                    $arr_user_data->userInformation->user_status = $data["user_status"];
                }

                if (isset($data["first_name"])) {
                    $arr_user_data->userInformation->first_name = $data["first_name"];
                }
                if (isset($data["last_name"])) {
                    $arr_user_data->userInformation->last_name = $data["last_name"];
                }
                if (isset($data["about_me"])) {
                    $arr_user_data->userInformation->about_me = $data["about_me"];
                }

                if (isset($data["user_mobile"])) {
                    $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                }

                $arr_user_data->userInformation->save();

                $succes_msg = "Your profile has been updated successfully!";
                return redirect("agent/profile")->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateEmailInfo(Request $data) {

        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = Auth::user();
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/profile')
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('admin/verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();

                Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                    $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                });

                $successMsg = "Congratulations! your email has been updated successfully. We have sent email verification email to your email address. Please verify";
                Auth::logout();
                return redirect("admin/login")->with("register-success", $successMsg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateAdminUserEmailInfo(Request $data, $user_id) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/update-admin-user/' . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('admin/verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();

                Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                    $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                });

                $succes_msg = "Admin user email has been updated successfully!";
                return redirect("admin/update-admin-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateCompanyUserEmailInfo(Request $data, $user_id) {

        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/update-company-user/' . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('admin/verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();

                Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                    $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                });

                $succes_msg = "Company user email has been updated successfully!";
                return redirect("admin/update-company-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateAgentUserEmailInfo(Request $data, $user_id) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/update-agent-user/' . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('admin/verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();

                Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                    $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                });

                $succes_msg = "Agent user email has been updated successfully!";
                return redirect("admin/update-agent-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateFreeTonerUserEmailInfo(Request $data, $user_id) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/update-free-toner-user/' . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('admin/verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();

                Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                    $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                });

                $succes_msg = "Free Toner user email has been updated successfully!";
                return redirect("admin/update-free-toner-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateAgentManagerUserEmail(Request $data, $user_id) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/update-agent-manager-user/' . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('admin/verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();
                if (isset($arr_user_data->email) && $arr_user_data->email != '') {
                    Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                        $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                    });
                }
                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $succes_msg = "Agent Manager user email has been updated successfully!";
                return redirect("admin/update-agent-manager-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updatePasswordInfo(Request $data) {
        $current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = Auth::user();
            // $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'new_password' => 'required',
                        'confirm_password' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/profile')
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $succes_msg = "Congratulations! your password has been updated successfully!";
                return redirect("admin/profile")->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateAgentManagerUserPassword(Request $data, $user_id) {
        //$current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            //  $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'new_password' => 'required',
                        'confirm_password' => 'required',
            ));
            if ($validate_response->fails()) {
                return redirect("admin/update-agent-manager-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $succes_msg = "Agent Manager user password has been updated successfully!";
                return redirect("admin/update-agent-manager-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateAdminUserPasswordInfo(Request $data, $user_id) {
        $current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            // $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'new_password' => 'required',
                        'confirm_password' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect("admin/update-admin-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $succes_msg = "Admin user password has been updated successfully!";
                return redirect("admin/update-admin-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateCompanyUserPasswordInfo(Request $data, $user_id) {
        $current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            // $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'new_password' => 'required',
                        'confirm_password' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect("admin/update-company-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $succes_msg = "Company user password has been updated successfully!";
                return redirect("admin/update-company-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateAgentUserPasswordInfo(Request $data, $user_id) {
        $current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            //   $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'new_password' => 'required',
                        'confirm_password' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect("admin/update-agent-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $succes_msg = "Agent user password has been updated successfully!";
                return redirect("admin/update-agent-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateFreeTonerUserPasswordInfo(Request $data, $user_id) {
        $current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            //   $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'new_password' => 'required',
                        'confirm_password' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect("admin/update-free-toner-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $succes_msg = "Free Tonee user password has been updated successfully!";
                return redirect("admin/update-free-toner-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    protected function verifyUserEmail($activation_code) {
        $user_informations = UserInformation::where('activation_code', $activation_code)->get()->first();
        if ($user_informations) {

            if ($user_informations->user_status === '0') {

                //updating the user status to active
                $user_informations->user_status = '1';
                $user_informations->activation_code = '';
                $user_informations->save();
                $successMsg = "Congratulations! your account has been successfully verified. Please login to continue";
                Auth::logout();
                return redirect("admin/login")->with("register-success", $successMsg);
            } else {
                $user_informations->activation_code = '';
                $user_informations->save();
                $errorMsg = "Error! this link has been expired";
                Auth::logout();
                return redirect("admin/login")->with("login-error", $errorMsg);
            }
        } else {
            $errorMsg = "Error! this link has been expired";
            Auth::logout();
            return redirect("admin/login")->with("login-error", $errorMsg);
        }
    }

    public function listRegisteredUsers() {
        \App::setLocale('en');
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        return view("admin::list-users", array("all_countries" => $all_countries));
    }

    public function userPaymentRecived() {
        \App::setLocale('en');
        $userAllPayments = UserPaymentReceivedDetail::all();
        return view("admin::user-payments", array("all_payments" => $userAllPayments));
    }

    public function userPaymentRecivedData(Request $request) {
        \App::setLocale('en');
        $order_filter_by = $request->order_filter_by;
        $userAllPayments = UserPaymentReceivedDetail::all();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $search_value = $request->search_value;
        $filter_type = $request->filter_type;
        $filter_type_reply = $request->filter_type_reply;
        if (Auth::user()->userInformation->user_type == '4' || Auth::user()->userInformation->user_type == '6') {
            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {
                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $userAllPayments = $userAllPayments->reject(function($wallet_data) use($country, $state, $city) {
                $user = UserInformation::where('user_id', $wallet_data->user_id)->first();
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user_type == '2') {

                    $userAddress = \App\UserAddress::where('user_id', $wallet_data->user_id)->first();
                    if (count($userAddress) > 0) {
                        $star_country = $userAddress->user_country;
                        $star_state = $userAddress->user_state;
                        $star_city = $userAddress->user_city;
                    }
                } else if ($user->user_type == '3') {
                    $userAddress = \App\UserAddress::where('user_id', $wallet_data->user_id)->where('address_type', '1')->first();
                    if (count($userAddress) > 0) {
                        $star_country = $userAddress->user_country;
                        $star_state = $userAddress->user_state;
                        $star_city = $userAddress->user_city;
                    }
                }
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '3') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    return (($contry_passed || $state_passed));
                } else {
                    $contry_passed = ($star_country != $country);
                    if ($state != '5') {
                        return (($contry_passed));
                    } else {
                        return (($contry_passed || $state_passed));
                    }
                }
            });
        }

        if ($start_date != "" && $end_date != "") {
            $userAllPayments = $userAllPayments->filter(function($payment)use($start_date, $end_date) {
                return date("Y-m-d", strtotime($payment->created_at)) >= $start_date && date("Y-m-d", strtotime($payment->created_at)) <= $end_date;
            });
        }
        if ($filter_type != "") {

            $userAllPayments = $userAllPayments->filter(function($all_data)use($filter_type) {

                return ($all_data->payment_mode == $filter_type);
            });
        }
        if ($filter_type_reply != "" && $search_value != '') {
            if ($filter_type_reply == 0) {
                $userAllPayments = $userAllPayments->filter(function($all_data)use($search_value) {
                    $mobile = '0';
                    if (isset($all_data->paidUserInfo->userInformation->user_mobile)) {
                        $mobile = $all_data->paidUserInfo->userInformation->user_mobile;
                    }
                    return ($mobile == $search_value);
                });
            } else if ($filter_type_reply == 1) {
                $userAllPayments = $userAllPayments->filter(function($all_data)use($search_value) {
                    $name = '0';
                    if (isset($all_data->paidUserInfo->userInformation->first_name)) {
                        $name = $all_data->paidUserInfo->userInformation->first_name . "" . $all_data->paidUserInfo->userInformation->last_name;
                    }
                    $search_value = str_replace(" ", "", $search_value);
                    $search_text = strstr(strtoupper($name), strtoupper($search_value));
                    return ($search_text != '' && $search_text != '0');
                });
            } else if ($filter_type_reply == 2) {
                $userAllPayments = $userAllPayments->filter(function($all_data)use($search_value) {
                    $name = '0';
                    if (isset($all_data->bank_name)) {
                        $name = $all_data->bank_name;
                    }
                    $search_text = strstr(strtoupper($name), strtoupper($search_value));
                    return ($search_text != '' && $search_text != '0');
                });
            } else if ($filter_type_reply == 3) {
                $userAllPayments = $userAllPayments->filter(function($all_data)use($search_value) {
                    $name = '0';
                    if (isset($all_data->cheque_number)) {
                        $name = $all_data->cheque_number;
                    }
                    $search_value = str_replace(" ", "", $search_value);
                    $search_text = strstr(strtoupper($name), strtoupper($search_value));
                    return ($search_text != '' && $search_text != '0');
                });
            } else if ($filter_type_reply == 4) {
                $userAllPayments = $userAllPayments->filter(function($all_data)use($search_value) {
                    $name = '0';
                    if (isset($all_data->transaction_number)) {
                        $name = $all_data->transaction_number;
                    }
                    $search_text = strstr(strtoupper($name), strtoupper($search_value));
                    return ($search_text != '' && $search_text != '0');
                });
            }
        }
        $userAllPayments = $userAllPayments->sortByDesc('id');
        return Datatables::of($userAllPayments)
                        ->addColumn('user_name', function($payment_details) {

                            return $payment_details->paidUserInfo->userInformation->first_name . " " . $payment_details->paidUserInfo->userInformation->last_name;
                        })
                        ->addColumn('payment_mode', function($payment_details) {
                            return $payment_details->payment_mode;
                        })
                        ->addColumn('payment_on', function($payment_details) {
                            return $payment_details->created_at;
                        })
                        ->addColumn('bank_name', function($payment_details) {
                            return ((isset($payment_details->bank_name) && ($payment_details->bank_name != '')) ? $payment_details->bank_name : 'NA');
                        })
                        ->addColumn('cheque_number', function($payment_details) {
                            return ((isset($payment_details->cheque_number) && ($payment_details->cheque_number != '')) ? $payment_details->cheque_number : 'NA');
                        })
                        ->addColumn('transaction_number', function($payment_details) {
                            return ((isset($payment_details->transaction_number) && ($payment_details->transaction_number != '')) ? $payment_details->transaction_number : 'NA');
                        })
                        ->addColumn('amount', function($payment_details) {
                            return (Double) $payment_details->amount;
                        })
                        ->make(true);
    }

    public function getAllStarAgents() {
        \App::setLocale('en');
        $terms = $_REQUEST['term'];
        $userAllList = UserInformation::where('user_mobile', 'like', '%' . $terms . '%')->orWhere('first_name', 'like', '%' . $terms . '%')->get();
        $userAllList = $userAllList->reject(function($user) {
            return ($user->user_type != '2' && $user->user_type != '4');
        });
        if (Auth::user()->userInformation->user_type == '4') {
            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $userAllList = $userAllList->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '3') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    return ($condition || ($contry_passed || $state_passed));
                } else {
                    $contry_passed = ($star_country != $country);
                    if ($state != '5') {
                        return ($condition || ($contry_passed));
                    } else {
                        return ($condition || ($contry_passed || $state_passed));
                    }
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
        }
        if (Auth::user()->userInformation->user_type == '6') {

            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $userAllList = $userAllList->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '3') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    return ($condition || ($contry_passed || $state_passed));
                } else {
                    $contry_passed = ($star_country != $country);
                    if ($state != '5') {
                        return ($condition || ($contry_passed));
                    } else {
                        return ($condition || ($contry_passed || $state_passed));
                    }
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
        }
        $arrUsers = array();
        $i = 0;
        if (count($userAllList) > 0) {

            foreach ($userAllList as $user_list) {
                $user_wallet_data = UserWalletDetail::where('user_id', $user_list->user_id)->orderBy('id', 'desc')->first(['final_amout']);
                $star_balance_data = DeliveryuserBalanceDetail::where('user_id', $user_list->user_id)->where('is_paid', '0')->get();
                $star_balance_cash = $star_balance_data->reject(function($star_balance) {
                    return $star_balance->pay_type != '0';
                });
                $star_balance_online = $star_balance_data->reject(function($star_balance) {
                    return $star_balance->pay_type != '1';
                });

                $arrUsers[$i]['id'] = $user_list->user_id;
                $arrUsers[$i]['amount'] = (isset($user_wallet_data->final_amout) ? ($user_wallet_data->final_amout) : '0');
                $arrUsers[$i]['value'] = $user_list->first_name . " " . $user_list->last_name . " +" . str_replace("+", "", $user_list->mobile_code) . "" . $user_list->user_mobile;
                $arrUsers[$i]['label'] = $user_list->first_name . " " . $user_list->last_name . " +" . str_replace("+", "", $user_list->mobile_code) . "" . $user_list->user_mobile;

                $arrUsers[$i]['total_order_amount'] = (double) $star_balance_data->sum('total_amount');
                $arrUsers[$i]['total_order_cash_amount'] = (double) $star_balance_cash->sum('total_amount');
                $arrUsers[$i]['total_order_online_amount'] = (double) $star_balance_online->sum('total_amount');
                $total_online_order = (double) $star_balance_online->sum('total_amount');
                $star_online_order = (double) $star_balance_online->sum('star_amount');
                $agent_online_order = ($total_online_order - $star_online_order);
                $agent_online_order_addmin_comission = ($agent_online_order * 10) / 100;
                $agent_online_order = ($agent_online_order - $agent_online_order_addmin_comission);
                $arrUsers[$i]['total_star_amount'] = (double) $star_balance_data->sum('star_amount');
                $arrUsers[$i]['total_star_cash_amount'] = (double) $star_balance_cash->sum('star_amount');
                $arrUsers[$i]['total_star_online_amount'] = (double) $star_balance_online->sum('star_amount');
                $arrUsers[$i]['total_star_payable_amount'] = (double) $star_balance_data->sum('star_payable_amt');
                $star_to_pay = (double) $star_balance_data->sum('star_payable_amt');

                $arrUsers[$i]['total_payable_amount_to_admin'] = ($star_to_pay * 10) / 100;
                $arrUsers[$i]['total_payable_amount_to_admin_online'] = ($agent_online_order);
                $i++;
            }
        }
        echo json_encode($arrUsers);
        exit;
    }

    public function getAllStarMateUsers() {
        \App::setLocale('en');
        $terms = $_REQUEST['term'];
        $userAllList = UserInformation::where('user_mobile', 'like', '%' . $terms . '%')->orWhere('first_name', 'like', '%' . $terms . '%')->get();
        $userAllList = $userAllList->reject(function($user) {

            return (($user->user_status != '1') || ($user->user_type != '2' && $user->user_type != '3'));
        });
        if (Auth::user()->userInformation->user_type == '4') {

            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $country_info = Country::where('id', $country)->first();
            $mobile_code = isset($country_info->country_code) ? (str_replace("+", "", $country_info->country_code)) : '0';

            $userAllList = $userAllList->reject(function ($user) use($country, $state, $city, $mobile_code) {
                if ($user->user_type == '2') {
                    $star_country = 0;
                    $star_state = 0;
                    $star_city = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $star_country = $address->user_country;
                            $star_state = $address->user_state;
                            $star_city = $address->user_city;
                        }
                    }

                    $contry_passed = false;
                    $state_passed = false;
                    $city_passed = false;
                    if ($country != '3') {
                        if ($country != '17') {
                            $contry_passed = ($star_country != $country);
                        }
                        if ($state != '32') {
                            $state_passed = ($star_state != $state);
                        }
                        return (($contry_passed || $state_passed));
                    } else {
                        $contry_passed = ($star_country != $country);
                        if ($state != '5') {
                            return (($contry_passed));
                        } else {
                            return (($contry_passed || $state_passed));
                        }
                    }
                } else if ($user->user_type == 3) {
                    $user_mobile_code = str_replace("+", "", $user->mobile_code);
                    return $user_mobile_code != $mobile_code;
                }
            });
        }
        if (Auth::user()->userInformation->user_type == '6') {

            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $country_info = Country::where('id', $country)->first();
            $mobile_code = isset($country_info->country_code) ? (str_replace("+", "", $country_info->country_code)) : '0';

            $userAllList = $userAllList->reject(function ($user) use($country, $state, $city, $mobile_code) {
                if ($user->user_type == '2') {
                    $star_country = 0;
                    $star_state = 0;
                    $star_city = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $star_country = $address->user_country;
                            $star_state = $address->user_state;
                            $star_city = $address->user_city;
                        }
                    }

                    $contry_passed = false;
                    $state_passed = false;
                    $city_passed = false;
                    if ($country != '3') {
                        if ($country != '17') {
                            $contry_passed = ($star_country != $country);
                        }
                        if ($state != '32') {
                            $state_passed = ($star_state != $state);
                        }
                        return (($contry_passed || $state_passed));
                    } else {
                        $contry_passed = ($star_country != $country);
                        if ($state != '5') {
                            return (($contry_passed));
                        } else {
                            return (($contry_passed || $state_passed));
                        }
                    }
                } else if ($user->user_type == 3) {
                    $user_mobile_code = str_replace("+", "", $user->mobile_code);
                    return $user_mobile_code != $mobile_code;
                }
            });
        }
        $arrUsers = array();
        $i = 0;
        if (count($userAllList) > 0) {

            foreach ($userAllList as $user_list) {

                $arrUsers[$i]['id'] = $user_list->user_id;
                $arrUsers[$i]['value'] = $user_list->first_name . " " . $user_list->last_name . " " . str_replace("+", "", $user_list->mobile_code) . " " . $user_list->user_mobile;
                $arrUsers[$i]['label'] = $user_list->first_name . " " . $user_list->last_name . " " . str_replace("+", "", $user_list->mobile_code) . " " . $user_list->user_mobile;
                $i++;
            }
        }
        echo json_encode($arrUsers);
        exit;
    }

    public function listRegisteredUsersData(Request $request) {
        \App::setLocale('en');
        $country_name = $request->country_name;
        $filter_by_week = $request->week_filter;
        $order_filter_by = $request->order_filter_by;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {
            $country_code = 0;
            $country = 0;
            if (Auth::user()->userAddress) {
                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $countryData = Country::where('id', $country)->first();
                    $country_code = str_replace("+", "", $countryData->country_code);
                }
            }
            $registered_users = UserInformation::where("user_type", 3)->get();
            if ($country != 17 && $country != 0) {

                $registered_users = $registered_users->reject(function ($user) use ($country_code) {

                    return (($user->mobile_code != $country_code));
                });
            }
        } else if (Auth::user()->userInformation->user_type == '4' || Auth::user()->userInformation->user_type == '5' || Auth::user()->userInformation->user_type == '6') {
            $mobile_code = str_replace("+", "", Auth::user()->userInformation->mobile_code);

            $all_users = UserInformation::where("user_type", 3)->get();
            $registered_users = $all_users->reject(function($user) use($mobile_code) {

                        $mobile_code_mate = str_replace("+", "", $user->mobile_code);
                        return ($user->user_type != 3 || ($mobile_code_mate != $mobile_code));
                    })->values();
        } else {
            $registered_users = UserInformation::where("user_type", 3)->get();
        }


        if ($order_country_id != "") {
            if ($order_country_id != "17") {
                $registered_users = $registered_users->filter(function($user)use($order_country_id) {
                    // return $user->country_id == $order_country_id;

                    $mobile_code = 0;
                    $mobile_code_with_plus = 0;
                    if (isset($user->mobile_code)) {
                        $mobile_code = $user->mobile_code;
                        $mobile_code = str_replace("+", "", $mobile_code);
                        $mobile_code_with_plus = "+" . $mobile_code;
                    }
                    return ($mobile_code == $order_country_id);
                });
            }
        }
        if ($order_start_date != "" && $order_end_date != "") {
            $registered_users = $registered_users->filter(function($user)use($order_start_date, $order_end_date) {
                return date("Y-m-d", strtotime($user->created_at)) >= $order_start_date && date("Y-m-d", strtotime($user->created_at)) <= $order_end_date;
            });
        }
        if ($order_filter_by != "") {

            $registered_users = $registered_users->filter(function($all_data)use($order_filter_by, $order_country_id) {
                return ($all_data->user_status == $order_filter_by);
            });
        }

        $registered_users = $registered_users->sortByDesc('id');

        return Datatables::of($registered_users)
                        ->addColumn('first_name', function($regsiter_user) {
                            return $regsiter_user->first_name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($regsiter_user) {
                            return $regsiter_user->user->email;
                        })
                        ->addColumn('username', function($regsiter_user) {
                            return $regsiter_user->user->username;
                        })
                        ->addColumn('location', function($regsiter_user) {
                            $location = '';
                            $user_address = UserAddress::where('user_id', $regsiter_user->user_id)->where('address_type', '1')->get();
                            if (count($user_address) > 0) {
                                foreach ($user_address as $address) {
                                    if (isset($address->countryinfo)) {
                                        if (isset($address->countryinfo->translate()->name)) {
                                            $location .= $address->countryinfo->translate()->name;
                                        }

                                        if (isset($address->stateInfo)) {
                                            $location .= " /" . $address->stateInfo->translate()->name;
                                        }
                                        if (isset($address->cityInfo)) {
                                            $location .= " /" . $address->cityInfo->translate()->name;
                                        }
                                    }
                                }
                            }

                            return $location;
                        })
                        ->addColumn('status', function($regsiter_user) {

                            $html = '';
                            if ($regsiter_user->user_status == 0) {
                                $html = '<div  id="active_div' . $regsiter_user->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 2);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="inactive_div' . $regsiter_user->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 1);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Inactive </a> </div>';
                                $html = $html . '<div id="blocked_div' . $regsiter_user->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 1);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Blocked </a> </div>';
                            } else if ($regsiter_user->user_status == 2) {
                                $html = '<div  id="active_div' . $regsiter_user->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 2);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $regsiter_user->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 1);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Blocked</a> </div>';
                            } else {//                              
                                $html = '<div  id="active_div' . $regsiter_user->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 2);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $regsiter_user->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 1);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Blocked</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('blocked', function($regsiter_user) {

                            $html = '';
                            if ($regsiter_user->user_status == 2) {
                                $html = '<div  id="active_div_block' . $regsiter_user->user->id . '"  style="display:none;" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 2);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $regsiter_user->user->id . '"    style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 1);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Click to Activate</a> </div>';
                            }if ($regsiter_user->user_status == 1) {//                              
                                $html = '<div  id="active_div_block' . $regsiter_user->user->id . '"   style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 2);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $regsiter_user->user->id . '"  style="display:none;"  >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $regsiter_user->user->id . ', 1);" href="javascript:void(0);" id="status_' . $regsiter_user->user->id . '">Click to Activate</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;

//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('created_at', function($regsiter_user) {
                            return $regsiter_user->user->created_at;
                        })
                        ->addColumn('rating', function($regsiter_user) {
                            //finding avg rating
                            $userRatingInfo = UserRatingInformation::where('to_id', $regsiter_user->user_id)->where('status', '1')->get();
                            $avg_rating = ($userRatingInfo->avg('rating')) ? $userRatingInfo->avg('rating') : '0';
                            return round($avg_rating);
                        })
                        ->make(true);
    }

    public function deleteRegisteredUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();

            return redirect('admin/manage-users')->with('delete-user-status', 'User has been deleted successfully!');
        } else {
            return redirect("admin/manage-users");
        }
    }

    public function deletGeoCitySetting($id) {
        $geoData = GeoLimit::find($id);

        if ($geoData) {
            $geoData->delete();

            return redirect('admin/city-geo-settings/list')->with('country-status', 'Record has been deleted successfully!');
        } else {
            return redirect("admin/city-geo-settings/list");
        }
    }

    public function deleteUserPayment($payment_id) {
        $payment = UserPaymentReceivedDetail::find($payment_id);

        if ($payment) {
            $payment->delete();

            return redirect('admin/users-payments/list')->with('delete-user-payment', 'Payment record has been deleted successfully!');
        } else {
            return redirect("admin/users-payments/list");
        }
    }

    public function deleteSelectedRegisteredUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function updateRegisteredUser(Request $request, $user_id) {
        $arr_user_data = User::find($user_id);

        if ($arr_user_data) {
            if ($request->method() == "GET") {
                $all_countries = Country::translatedIn(\App::getLocale())->get();
                $states = "";
                $cities = "";
                $user_country = 0;
                $user_state = 0;
                $user_state = 0;
                $user_city = 0;
                $user_address = "";
                if (isset($arr_user_data->userAddress)) {
                    foreach ($arr_user_data->userAddress as $address) {
                        $user_country = $address->user_country;
                        $user_state = $address->user_state;
                        $user_city = $address->user_city;
                        $user_address = $address->address;
                    }
                }
                $states = State::where('country_id', $user_country)->translatedIn(\App::getLocale())->get();
                $cities = City::where('state_id', $user_state)->where('country_id', $user_country)->translatedIn(\App::getLocale())->get();

                $all_roles = Role::where('level', "<=", 1)->where('slug', '<>', 'superadmin')->get();
                return view("admin::edit-registered-user", array("countries" => $all_countries, "user_state" => $user_state, "user_country" => $user_country, "user_city" => $user_city, "cities" => $cities, "states" => $states, 'user_info' => $arr_user_data, 'roles' => $all_roles));
            } elseif ($request->method() == "POST") {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'country' => 'required',
                            'state' => 'required',
                            'city' => 'required',
                            'user_mobile' => 'required|numeric',
                            'user_status' => 'required|numeric'
                                )
                );

                if ($validate_response->fails()) {
                    return redirect('admin/update-registered-user/' . $arr_user_data->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {/** user information goes here *** */
                    if (isset($data["profile_picture"])) {
                        $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                    }
                    if (isset($data["gender"])) {
                        $arr_user_data->userInformation->gender = $data["gender"];
                    }
                    if (isset($data["user_status"])) {
                        $arr_user_data->userInformation->user_status = $data["user_status"];
                    }

                    if (isset($data["first_name"])) {
                        $arr_user_data->userInformation->first_name = $data["first_name"];
                    }
                    if (isset($data["last_name"])) {
                        $arr_user_data->userInformation->last_name = $data["last_name"];
                    }
                    if (isset($data["about_me"])) {
                        $arr_user_data->userInformation->about_me = $data["about_me"];
                    }
                    if (isset($data["date_of_birth"])) {
                        $arr_user_data->userInformation->user_birth_date = $data["date_of_birth"];
                    }

                    if (isset($data["user_mobile"])) {
                        $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                    }
                    if (isset($data["user_mobile"])) {
                        $arr_user_data->username = $data["user_mobile"];
                    }
                    $arr_user_data->save();

                    $arr_user_data->userInformation->save();

                    //adding address
                    if (isset($data["country"])) {
                        $user_address = UserAddress::where('user_id', $user_id)->where('address_type', '1')->first();
                        if (count($user_address) > 0) {
                            $user_address->user_country = $data["country"];
                            $user_address->user_state = $data["state"];
                            $user_address->user_city = $data["city"];
                            $user_address->save();
                        } else {
                            $arr_userAddress["user_country"] = $data["country"];
                            $arr_userAddress["user_state"] = $data["state"];
                            $arr_userAddress["user_city"] = $data["city"];
                            $arr_userAddress["address_type"] = 1;
                            $arr_userAddress["user_id"] = $user_id;
                            UserAddress::create($arr_userAddress);
                        }
                    }
                    $success_msg = "User profile has been updated successfully!";
                    return redirect("admin/update-registered-user/" . $arr_user_data->id)->with("profile-updated", $success_msg);
                }
            }
        } else {
            return redirect("admin/manage-users");
        }
    }

    protected function updateRegisteredUserEmailInfo(Request $data, $user_id) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));
            if ($validate_response->fails()) {
                return redirect('admin/update-registered-user/' . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();

                if (isset($arr_user_data->email) && $arr_user_data->email != '') {
                    Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                        $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                    });
                }
                $succes_msg = "User email has been updated successfully!";
                return redirect("admin/update-registered-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateRegisteredUserPasswordInfo(Request $data, $user_id) {
        $current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            //  $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'new_password' => 'required|confirmed',
                        'new_password_confirmation' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect("admin/update-registered-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user Password
                $arr_user_data->password = $data->new_password;
                $arr_user_data->save();
                $arr_keyword_values = array();

                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['PASSWORD'] = $data->new_password;
                $arr_keyword_values['SITE_TITLE'] = $site_title;

                if (isset($arr_user_data->email) && $arr_user_data->email != '') {
                    Mail::send('emailtemplate::password-change-en', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                        $message->to($arr_user_data->email)->subject("Password changed Successfully!")->from($site_email, $site_title);
                    });
                }
                $message = "Your password for BAGGI has been reset to:- " . $data->new_password;
                //sending sms to verified user
                $mobile = $arr_user_data->userInformation->user_mobile;

                $mobile_code = str_replace("+", "", $arr_user_data->userInformation->mobile_code);
                $mobile_number_to_send = "+" . $mobile_code . "" . $mobile;
                try {
                    Twilio::message($mobile_number_to_send, $message);
                } catch (TwilioException $e) {
                    $error_msg = "There is an issue in updating user informations!";
                    return redirect("admin/update-registered-user/" . $user_id)->with("profile-error", $error_msg);
                }

                $succes_msg = "User password has been updated successfully!";
                return redirect("admin/update-registered-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    protected function updateStarUserPasswordInfo(Request $data, $user_id) {
        $current_password = $data->current_password;
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            //  $user_password_chk=Hash::check($current_password, $arr_user_data->password);
            $validate_response = Validator::make($data_values, array(
                        'password' => 'required|confirmed',
                        'password_confirmation' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect("admin/update-star-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                //updating user Password
                $arr_user_data->password = $data->password;
                $arr_user_data->save();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['PASSWORD'] = $data->password;
                $arr_keyword_values['SITE_TITLE'] = $site_title;

                if (isset($arr_user_data->email) && $arr_user_data->email != '') {
                    Mail::send('emailtemplate::password-change-en', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                        $message->to($arr_user_data->email)->subject("Password changed Successfully!")->from($site_email, $site_title);
                    });
                }
                //sending message

                $message = "Your password for BAGGI has been reset to:- " . $data->password;
                //sending sms to verified user
                $mobile = $arr_user_data->userInformation->user_mobile;

                $mobile_code = str_replace("+", "", $arr_user_data->userInformation->mobile_code);
                $mobile_number_to_send = "+" . $mobile_code . "" . $mobile;
                try {
                    Twilio::message($mobile_number_to_send, $message);
                } catch (TwilioException $e) {
                    $error_msg = "There is an issue in updating user informations!";
                    return redirect("admin/update-star-user/" . $user_id)->with("profile-error", $error_msg);
                }
                $succes_msg = "User password has been updated successfully!";
                return redirect("admin/update-star-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    public function createRegisteredUser(Request $request) {
        if ($request->method() == "GET") {
            $all_countries = Country::translatedIn(\App::getLocale())->get();
            return view("admin::create-registered-user", array("countries" => $all_countries));
        } elseif ($request->method() == "POST") {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'email' => 'required|email|max:255|unique:users,email',
                        'password' => 'required|min:6|confirmed',
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'mobile_code' => 'required',
                        'country' => 'required',
                        'state' => 'required',
                        'city' => 'required',
                        'user_mobile' => 'required|numeric|unique:users,username',
                            )
            );
            if ($validate_response->fails()) {
                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                $userCheck = User::where('username', ltrim($data['user_mobile'], 0))->first();
                if (count($userCheck) > 0) {

                    return redirect('admin/manage-users')
                                    ->with("mobile_exist", "Mobile number is already exist.");
                } else {
                    $created_user = User::create(array(
                                'email' => $data['email'],
                                'password' => ($data['password']),
                                'username' => ($data['user_mobile']),
                    ));

                    $user_code = $data['user_mobile'] . '-' . $created_user->id;

                    // update User Information

                    /*
                     * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                     */

                    $data["user_status"] = isset($data["user_status"]) ? $data["user_status"] : "0";  // 0 means not active

                    $data["gender"] = isset($data["gender"]) ? $data["gender"] : "3";       // 3 means not specified

                    $data["profile_picture"] = isset($data["profile_picture"]) ? $data["profile_picture"] : "";
                    $data["facebook_id"] = isset($data["facebook_id"]) ? $data["facebook_id"] : "";
                    $data["twitter_id"] = isset($data["twitter_id"]) ? $data["twitter_id"] : "";
                    $data["google_id"] = isset($data["google_id"]) ? $data["google_id"] : "";
                    $data["user_birth_date"] = isset($data["user_birth_date"]) ? $data["user_birth_date"] : "";
                    $data["first_name"] = isset($data["first_name"]) ? $data["first_name"] : "";
                    $data["last_name"] = isset($data["last_name"]) ? $data["last_name"] : "";
                    $data["about_me"] = isset($data["about_me"]) ? $data["about_me"] : "";
                    $data["user_mobile"] = isset($data["user_mobile"]) ? $data["user_mobile"] : "";
                    $data["mobile_code"] = isset($data["mobile_code"]) ? $data["mobile_code"] : "";

                    $arr_userinformation = array();
                    $arr_userinformation["profile_picture"] = $data["profile_picture"];
                    $arr_userinformation["gender"] = $data["gender"];
                    $arr_userinformation["activation_code"] = "";             // By default it'll be no activation code
                    $arr_userinformation["facebook_id"] = $data["facebook_id"];
                    $arr_userinformation["twitter_id"] = $data["twitter_id"];
                    $arr_userinformation["google_id"] = $data["google_id"];
                    $arr_userinformation["user_birth_date"] = $data["user_birth_date"];
                    $arr_userinformation["first_name"] = $data["first_name"];
                    $arr_userinformation["last_name"] = $data["last_name"];
                    $arr_userinformation["about_me"] = $data["about_me"];

                    $arr_userinformation["user_mobile"] = $data["user_mobile"];
                    $arr_userinformation["user_status"] = $data["user_status"];
                    $arr_userinformation["user_type"] = 3;
                    $arr_userinformation["user_id"] = $created_user->id;
                    $arr_userinformation["user_code"] = $user_code;
                    $arr_userinformation["mobile_code"] = str_replace("+", "", $data["mobile_code"]);
                    $updated_user_info = UserInformation::create($arr_userinformation);
                    $created_user->attachRole('2');
                    $created_user->save();
                    $arr_keyword_values = array();
                    $activation_code = $this->generateReferenceNumber();
                    //Assign values to all macros
                    $site_email = GlobalValues::get('site-email');
                    $site_title = GlobalValues::get('site-title');
                    $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                    $arr_keyword_values['LAST_NAME'] = $updated_user_info->last_name;
                    $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                    $arr_keyword_values['SITE_TITLE'] = $site_title;

                    // updating activation code                 
                    $updated_user_info->activation_code = $activation_code;
                    $updated_user_info->save();

                    //adding address
                    if (isset($data["country"])) {
                        $arr_userAddress["user_country"] = $data["country"];
                        $arr_userAddress["user_state"] = $data["state"];
                        $arr_userAddress["user_city"] = $data["city"];
                        $arr_userAddress["address_type"] = 1;
                        $arr_userAddress["user_id"] = $created_user->id;
                        UserAddress::create($arr_userAddress);
                    }
                    Mail::send('emailtemplate::registration-successfull-en', $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {

                        $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
                    });
                    return redirect('admin/manage-users')
                                    ->with("update-user-status", "User has been created successfully");
                }
            }
        }
    }

    protected function downloadPdfFileOnClick(Request $request, $id) {
        $userPaymentRecived = UserPaymentReceivedDetail::where('id', $id)->first();
        $pdf_file_name_download = "Payment_" . $userPaymentRecived->id . ".pdf";
//            $pdf_file_name="Payment_".$userPaymentRecived->id.".pdf";
//            $pdf_file_name='public/star_payment/'.$pdf_file_name;
//            $file_path=  realpath(dirname(__DIR__).'/../../..');
//            $file_path=$file_path."/".$pdf_file_name;
//            PDF::Output($file_path,"F");

        session::put('pdf_file_to_download', $pdf_file_name_download);
        return redirect('admin/users-payments/list')
                        ->with("update-user-payment-pdf", "Please wait, We are downloading the payment slip for you...");
    }

    public function createUserPaymentRecived(Request $request) {
        if ($request->method() == "GET") {
            return view("admin::create-user-payment");
        } elseif ($request->method() == "POST") {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'user_id' => 'required',
                        'payment_mode' => 'required',
                            ), array('user_id.required' => 'Please choose a valid user.')
            );
            if ($validate_response->fails()) {
                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                //check if the wallet and user amount the time of submit match

                $mate_wallet_data = UserWalletDetail::where('user_id', $data['user_id'])->orderBy('id', 'desc')->first(['final_amout']);
                $final_amount = isset($mate_wallet_data->final_amout) ? $mate_wallet_data->final_amout : '0';
                $final_amount = (double) ($final_amount);
                $final_amount_remaining = (double) ($final_amount - $final_amount);
                $arrWalletAmt = array();
                $arrWalletAmt['user_id'] = $data['user_id'];
                $arrWalletAmt['transaction_amount'] = $final_amount;
                $arrWalletAmt['final_amout'] = $final_amount_remaining;
                $arrWalletAmt['trans_desc'] = "Got payment of you orders commission";
                $arrWalletAmt['transaction_type'] = '1';
                if ($data['payment_mode'] == 'Cash') {
                    $arrWalletAmt['payment_type'] = '0';
                } else {
                    $arrWalletAmt['payment_type'] = '1';
                }
                if ($final_amount == $data['amount_to_check']) {
                    UserWalletDetail::create($arrWalletAmt);

                    $payment_received = UserPaymentReceivedDetail::create(array(
                                'user_id' => $data['user_id'],
                                'paid_by' => (Auth::user()->id),
                                'bank_name' => ($data['bank_name']),
                                'cheque_number' => ($data['cheque_number']),
                                'transaction_number' => ($data['transaction_number']),
                                'payment_mode' => ($data['payment_mode']),
                                'amount' => ($final_amount)
                    ));
                    $starBalanceData = DeliveryuserBalanceDetail::where('user_id', $data['user_id'])->where('is_paid', '0')->get();
                    if (count($starBalanceData) > 0) {
                        foreach ($starBalanceData as $starBalance) {
                            //update Driver lanace value
                            $starBalance->is_paid = 1;
                            $starBalance->save();
                        }
                    }
                    //getting user details 
                    $userDetails = UserInformation::where('user_id', $data['user_id'])->first();
                    $user_code = "+" . str_replace("+", "", $userDetails->mobile_code);
                    $countryDetails = Country::where('country_code', $user_code)->first();
                    $currencyCode = '';
                    if (count($countryDetails) > 0) {
                        $currencyCode = $countryDetails->currency_code;
                    }
                    $receipt_id = '';
                    $paidTo = '';
                    $paidToID = '';
                    $paidAmount = (Double) $final_amount;
                    if (count($userDetails) > 0) {
                        $receipt_id = str_replace("+", "", $userDetails->mobile_code);
                        $receipt_id .= Auth::user()->id;
                        $receipt_id .= rand(00000001, 99999999);
                        $paidTo = $userDetails->first_name . " " . $userDetails->last_name;
                        $paidToID = $userDetails->user_id;
                    }

                    $pdf_title = "Payment to Driver user- " . $userDetails->user_id . " on- " . date('Y-m-d H:i:s');
                    $lg = Array();
                    $lg['a_meta_charset'] = 'UTF-8';
                    // set some language-dependent strings (optional)
                    PDF::setLanguageArray($lg);
                    PDF::setTitle($pdf_title);
                    PDF::AddPage();
                    PDF::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                    $logo_images = url('public/media/front/images/logo.png');
                    $back_images = url('public/media/front/images/back_ground.jpg');
                    PDF::Image($back_images, 0, 0, 210, 210, '', '', '', false, 300, '', false, false, 0);
                    PDF::setFont('dejavusans', 'B', 22);
                    PDF::Cell(0, 15, 'JAK COMPUTER GROUP .EST', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    PDF::setFont('dejavusans', '', 10);
                    PDF::Ln(5);

                    PDF::Image($logo_images, 78, 15, 40, '', '', 'http://baggi.in', '', false, 300, '', false);
                    PDF::Ln(17);
                    PDF::Cell(180, 10, 'Kuwait, Farwaniya', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    PDF::Ln(5);
                    PDF::Cell(196, 10, 'Block 54, Bldg 14, Office 17', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    PDF::Ln(5);
                    PDF::Cell(0, 10, '(+965)50 491 480', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    PDF::Ln(8);
                    PDF::setFont('dejavusans', 'B', 18);
                    PDF::Cell(0, 10, 'Payment Receipt', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    PDF::setFont('dejavusans', '', 10);
                    PDF::Ln(15);

                    // set color for background
                    PDF::SetFillColor(255, 255, 255);
                    PDF::MultiCell(72, 10, 'Cash Receipt #: ' . $receipt_id, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::MultiCell(80, 10, 'Date: ' . date('d/m/Y'), 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(10);
                    PDF::MultiCell(93, 10, 'Paid to Mr/Mrs:____' . $paidTo . '_______', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::MultiCell(30, 10, 'ID #: __' . $paidToID . '__', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::MultiCell(53, 10, 'Amount: ' . $paidAmount . '(' . $currencyCode . ')', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(10);
                    PDF::MultiCell(70, 10, 'For ___________________________', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::MultiCell(83, 10, 'During: ' . date('d/m/Y') . ' - ' . date('d/m/Y'), 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');

                    PDF::Ln(14);
                    PDF::MultiCell(82, 10, 'Payment Receved in:', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(2);
                    PDF::MultiCell(82, 10, '', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Cell(35, 6, 'Amount Received', 1, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Cell(35, 6, $paidAmount . '(' . $currencyCode . ')', 1, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(6);
                    PDF::MultiCell(82, 10, '', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Cell(35, 6, 'Paid By', 1, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Cell(35, 6, Auth::user()->userInformation->first_name, 1, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(1);
                    PDF::MultiCell(20, 10, 'Cash', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Cell(40, 6, '', 1, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(10);
                    PDF::MultiCell(20, 10, 'Cheque', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Cell(40, 6, '', 1, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(10);
                    PDF::MultiCell(20, 10, 'Other', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Cell(40, 6, '', 1, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(1);
                    PDF::MultiCell(100, 10, '', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::MultiCell(70, 10, '_____________________________', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::Ln(5);
                    PDF::MultiCell(110, 10, '', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                    PDF::MultiCell(75, 10, 'Signed By STAR', 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');

                    $pdf_file_name_download = "Payment_" . $payment_received->id . ".pdf";
                    $pdf_file_name = "Payment_" . $payment_received->id . ".pdf";
                    $pdf_file_name = 'public/star_payment/' . $pdf_file_name;
                    $file_path = realpath(dirname(__DIR__) . '/../../..');
                    $file_path = $file_path . "/" . $pdf_file_name;
                    PDF::Output($file_path, "F");
                    session::put('pdf_file_to_download', $pdf_file_name_download);
//                Mail::send('emailtemplate::payment-done', $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {
//
//                    $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
//                });

                    return redirect('admin/users-payments/list')
                                    ->with("update-user-payment", "You have sucessfully update the user payment. Please stay on same page, we are preparing the slip for you.");
                } else {
                    return redirect('admin/users-payments/list')
                                    ->with("user-payment-error", "Something is going wrong or we notice some change in selected star account, Please retry!!");
                }
            }
        }
    }

    protected function downloadPdfFilePayment($file_path) {
        $filename = url('public/star_payment') . "/" . $file_path;
        $fileinfo = pathinfo($filename);
        $sendname = $fileinfo['filename'] . '.' . strtoupper($fileinfo['extension']);

        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=\"$sendname\"");
        readfile($filename);
        die;
    }

    public function sendNotificationtoUser(Request $request) {
        if (Auth::user()) {
            if ($request->method() == "GET") {
                $all_countries = Country::translatedIn(\App::getLocale())->get()->sortBy("name");
                return view("admin::send-notification-to-user", array("countries" => $all_countries));
            } elseif ($request->method() == "POST") {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'type' => 'required',
                            'title' => 'required',
                            'message' => 'required',
                                ), array('user_id.required' => 'Please choose a valid user.')
                );
                if ($validate_response->fails()) {
                    return redirect()->back()
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {

                    if ($request->type == '0') {
                        $avalale_user_details = UserInformation::where('user_id', $data['user_id'])->first();

                        if ($avalale_user_details->user_type == '2') {
                            if (isset($avalale_user_details->device_id) && $avalale_user_details->device_id != '') {
                                $arr_push_message = array("sound" => "default", "title" => $request->title, "text" => $request->message, "flag" => 'custom_msg', 'message' => $request->message);
                                $arr_push_message_ios = array();

                                if ($avalale_user_details->device_type == '0') {
                                    //sending push notification star user.

                                    $response = PushNotification::app('appNameAndroid')
                                            ->to($avalale_user_details->device_id)
                                            ->send(json_encode($arr_push_message));
                                } else {
                                    $arr_push_message_ios['to'] = $avalale_user_details->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "default";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $this->IOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                }
                            }
                        } else if ($avalale_user_details->user_type == '3') {
                            if (isset($avalale_user_details->device_id) && $avalale_user_details->device_id != '') {
                                $arr_push_message = array("sound" => "default", "title" => $request->title, "text" => $request->message, "flag" => 'custom_msg', 'message' => $request->message);
                                $arr_push_message_android = array();
                                $arr_push_message_android['to'] = $avalale_user_details->device_id;
                                $arr_push_message_android['priority'] = "high";
                                $arr_push_message_android['sound'] = "default";
                                $arr_push_message_android['notification'] = $arr_push_message;
                                $arr_push_message_ios = array();
                                if ($avalale_user_details->device_type == '0') {
                                    //sending push notification star user.
                                    $response = PushNotification::app('appNameAndroidCustomer')
                                            ->to($avalale_user_details->device_id)
                                            ->send(json_encode($arr_push_message_android));
                                } else {
                                    $arr_push_message_ios['to'] = $avalale_user_details->device_id;
                                    $arr_push_message_ios['priority'] = "high";
                                    $arr_push_message_ios['sound'] = "default";
                                    $arr_push_message_ios['notification'] = $arr_push_message;
                                    $this->IOSPushNotificaton(json_encode($arr_push_message_ios));
                                }
                            }
                        }
                        $arrNotifiicationMessages = array();
                        $arrNotifiicationMessages['user_id'] = $data['user_id'];
                        $arrNotifiicationMessages['sent_by'] = Auth::user()->id;
                        $arrNotifiicationMessages['title'] = $request->title;
                        $arrNotifiicationMessages['description'] = $request->message;
                        UserCustomNotification::create($arrNotifiicationMessages);
                        if (isset($data['user_id'])) {
                            //saving
                            $notiMsg = $request->message;
                            $saveNotification = new AppNotification();
                            $saveNotification->saveNotification($data['user_id'], 0, $request->title, $notiMsg, date("Y-m-d"), 2, 'order');
                        }
                    } else {
                        $avalale_user_details_list = UserInformation::whereIn('user_type', array("2", "3"))->where('user_status', '1')->get();
                        if ($request->type == '1') {

                            $avalale_user_details_list = $avalale_user_details_list->reject(function($users_list) {
                                return $users_list->user_type != 3;
                            });
                        } else if ($request->type == '2') {
                            $avalale_user_details_list = $avalale_user_details_list->reject(function($users_list) {
                                return $users_list->user_type != 2;
                            });
                        }
                        if (Auth::user()->userInformation->user_type == '1') {
                            if ($request->country != '') {
                                $country_info = Country::where('id', $request->country)->first();
                                $mobile_code = isset($country_info->country_code) ? (str_replace("+", "", $country_info->country_code)) : '0';
                                $avalale_user_details_list = $avalale_user_details_list->reject(function($users_list) use($mobile_code) {
                                    $user_mobile_code = str_replace("+", "", $users_list->mobile_code);
                                    return $user_mobile_code != $mobile_code;
                                });
                            }
                        }

                        $arrNotifiicationMessages['user_id'] = 0;
                        $arrNotifiicationMessages['sent_by'] = Auth::user()->id;
                        $arrNotifiicationMessages['title'] = $request->title;
                        $arrNotifiicationMessages['description'] = $request->message;
                        $arrNotifiicationMessages['type'] = $request->type;
                        UserCustomNotification::create($arrNotifiicationMessages);

                        if (Auth::user()->userInformation->user_type == '4') {

                            $country = 0;
                            $state = 0;
                            $city = 0;
                            if (Auth::user()->userAddress) {

                                foreach (Auth::user()->userAddress as $address) {
                                    $country = $address->user_country;
                                    $state = $address->user_state;
                                    $city = $address->user_city;
                                }
                            }
                            $country_info = Country::where('id', $country)->first();
                            $mobile_code = isset($country_info->country_code) ? (str_replace("+", "", $country_info->country_code)) : '0';

                            $avalale_user_details_list = $avalale_user_details_list->reject(function ($user) use($country, $state, $city, $mobile_code) {
                                if ($user->user_type == '2') {
                                    $star_country = 0;
                                    $star_state = 0;
                                    $star_city = 0;
                                    if ($user->user->userAddress) {

                                        foreach ($user->user->userAddress as $address) {
                                            $star_country = $address->user_country;
                                            $star_state = $address->user_state;
                                            $star_city = $address->user_city;
                                        }
                                    }

                                    $contry_passed = false;
                                    $state_passed = false;
                                    $city_passed = false;
                                    if ($country != '3') {
                                        if ($country != '17') {
                                            $contry_passed = ($star_country != $country);
                                        }
                                        if ($state != '32') {
                                            $state_passed = ($star_state != $state);
                                        }
                                        return (($contry_passed || $state_passed));
                                    } else {
                                        $contry_passed = ($star_country != $country);
                                        if ($state != '5') {
                                            return (($contry_passed));
                                        } else {
                                            return (($contry_passed || $state_passed));
                                        }
                                    }
                                } else if ($user->user_type == 3) {
                                    $user_mobile_code = str_replace("+", "", $user->mobile_code);
                                    return $user_mobile_code != $mobile_code;
                                }
                            });
                        }
                        if (Auth::user()->userInformation->user_type == '5') {

                            $country = 0;
                            $state = 0;
                            $city = 0;
                            if (Auth::user()->userAddress) {

                                foreach (Auth::user()->userAddress as $address) {
                                    $country = $address->user_country;
                                    $state = $address->user_state;
                                    $city = $address->user_city;
                                }
                            }
                            $country_info = Country::where('id', $country)->first();
                            $mobile_code = isset($country_info->country_code) ? (str_replace("+", "", $country_info->country_code)) : '0';

                            $avalale_user_details_list = $avalale_user_details_list->reject(function ($user) use($country, $state, $city, $mobile_code) {
                                if ($user->user_type == '2') {
                                    $star_country = 0;
                                    $star_state = 0;
                                    $star_city = 0;
                                    if ($user->user->userAddress) {

                                        foreach ($user->user->userAddress as $address) {
                                            $star_country = $address->user_country;
                                            $star_state = $address->user_state;
                                            $star_city = $address->user_city;
                                        }
                                    }

                                    $contry_passed = false;
                                    $state_passed = false;
                                    $city_passed = false;
                                    if ($country != '3') {
                                        if ($country != '17') {
                                            $contry_passed = ($star_country != $country);
                                        }
                                        if ($state != '32') {
                                            $state_passed = ($star_state != $state);
                                        }
                                        return (($contry_passed || $state_passed));
                                    } else {
                                        $contry_passed = ($star_country != $country);
                                        if ($state != '5') {
                                            return (($contry_passed));
                                        } else {
                                            return (($contry_passed || $state_passed));
                                        }
                                    }
                                } else if ($user->user_type == 3) {
                                    $user_mobile_code = str_replace("+", "", $user->mobile_code);
                                    return $user_mobile_code != $mobile_code;
                                }
                            });
                        }

                        if (count($avalale_user_details_list) > 0) {
                            //$check=1;
                            foreach ($avalale_user_details_list as $avalale_user_details) {
                                //$check++;
                                if ($avalale_user_details->user_type == '2') {
                                    if (isset($avalale_user_details->device_id) && $avalale_user_details->device_id != '') {
                                        $arr_push_message = array("sound" => "default", "title" => $request->title, "text" => $request->message, "flag" => 'custom_msg', 'message' => $request->message);
                                        $arr_push_message_ios = array();
                                        if ($avalale_user_details->device_type == '0') {
                                            $arr_push_message_android = array();
                                            $arr_push_message_android['to'] = $avalale_user_details->device_id;
                                            $arr_push_message_android['priority'] = "high";
                                            $arr_push_message_android['sound'] = "default";
                                            $arr_push_message_android['notification'] = $arr_push_message;
                                            //sending push notification star user.
                                            $response = PushNotification::app('appNameAndroid')
                                                    ->to($avalale_user_details->device_id)
                                                    ->send(json_encode($arr_push_message_android));
                                        } else {
                                            $arr_push_message_ios['to'] = $avalale_user_details->device_id;
                                            $arr_push_message_ios['priority'] = "high";
                                            $arr_push_message_ios['sound'] = "default";
                                            $arr_push_message_ios['notification'] = $arr_push_message;
                                            $this->IOSPushNotificatonStar(json_encode($arr_push_message_ios));
                                        }
                                    }
                                } else if ($avalale_user_details->user_type == '3') {
                                    if (isset($avalale_user_details->device_id) && $avalale_user_details->device_id != '') {
                                        $arr_push_message = array("sound" => "default", "title" => $request->title, "text" => $request->message, "flag" => 'custom_msg', 'message' => $request->message);
                                        $arr_push_message_ios = array();
                                        if ($avalale_user_details->device_type == '0') {
                                            //sending push notification star user.
                                            $arr_push_message_android = array();
                                            $arr_push_message_android['to'] = $avalale_user_details->device_id;
                                            $arr_push_message_android['priority'] = "high";
                                            $arr_push_message_android['sound'] = "default";
                                            $arr_push_message_android['notification'] = $arr_push_message;
                                            $response = PushNotification::app('appNameAndroidCustomer')
                                                    ->to($avalale_user_details->device_id)
                                                    ->send(json_encode($arr_push_message_android));
                                        } else {
                                            $arr_push_message_ios['to'] = $avalale_user_details->device_id;
                                            $arr_push_message_ios['priority'] = "high";
                                            $arr_push_message_ios['sound'] = "default";
                                            $arr_push_message_ios['notification'] = $arr_push_message;
                                            $this->IOSPushNotificaton(json_encode($arr_push_message_ios));
                                        }
                                    }
                                }
                                if (isset($avalale_user_details->user_id)) {
                                    //saving
                                    $notiMsg = $request->message;
                                    $saveNotification = new AppNotification();
                                    $saveNotification->saveNotification($avalale_user_details->user_id, 0, $request->title, $notiMsg, date("Y-m-d"), 2, 'offer');
                                }
                            }
                        }
                    }
                    return redirect('/admin/send-notification-to-user')
                                    ->with("sent-msg", "Your message has been sent to user successfully");
                }
            }
        } else {
            return redirect('admin/login');
        }
    }

    public function editUser(Request $request, $user_id) {
        $user_details = User::find($user_id);

        if ($user_details) {

            if ($request->method() == "GET") {

                if ($user_details->level() <= 1) {
                    // he is admin user, redirect to admin update page
                    return redirect('admin/update-admin-user/' . $user_id);
                }

                return view("admin::edit-user", array('userdata' => $user_details));
            } elseif ($request->method() == "POST") {
                $data = $request->all();

                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email,' . $user_details->id,
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'user_mobile' => 'required|numeric|unique:users,username,' . $user_details->id,
                ));

                if ($validate_response->fails()) {
                    return redirect('admin/update-user/' . $user_details->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {
                    $user_details->email = $request->email;
                    $user_details->userInformation->first_name = $request->first_name;
                    $user_details->userInformation->last_name = $request->last_name;
                    $user_details->userInformation->gender = $request->gender;
                    $user_details->userInformation->user_birth_date = $request->user_birth_date;
                    $user_details->userInformation->about_me = $request->about_me;

                    $user_details->userInformation->user_mobile = $request->user_mobile;
                    $user_details->userInformation->user_type = $request->user_type;
                    //$user_details->userInformation->user_type =  $request->user_type;

                    $user_details->save();
                    $user_details->userInformation->save();

                    return redirect('admin/update-user/' . $user_details->id)
                                    ->with("update-user-status", "User updated successfully");
                }
            }
        } else {
            return redirect("admin/manage-users");
        }
    }

    public function editUserPassword(Request $request, $user_id) {
        $user_details = User::find($user_id);

        if ($user_details) {
            $data = $request->all();
            $validate_response = Validator::make($data, [
                        'new_password' => 'required|min:6|confirmed',
                            ], [
                        'new_password.required' => 'Please enter new password',
                        'new_password.min' => 'Please enter atleast 6 characters',
                        'new_password.confirmed' => 'Confirmation password doesn\'t match',
            ]);

            $return_url = 'admin/update-user/' . $user_details->id;

            if ($user_details->level() <= 1) {
                $return_url = 'admin/update-admin-user/' . $user_details->id;
            }

            if ($validate_response->fails()) {
                return redirect($return_url)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $user_details->password = $request->new_password;
                $user_details->save();

                return redirect($return_url)
                                ->with("update-user-status", "User's password updated successfully");
            }
        } else {
            return redirect()->back();
        }
    }

    public function editUserStatus(Request $request, $user_id) {
        $user_details = User::find($user_id);

        if ($user_details) {
            $user_details->userInformation->user_status = $request->active_status;
            $user_details->userInformation->save();

            $return_url = 'admin/update-user/' . $user_details->id;

            if ($user_details->level() <= 1) {
                $return_url = 'admin/update-admin-user/' . $user_details->id;
            }

            return redirect($return_url)
                            ->with("update-user-status", "User's status updated successfully");
        } else {
            return redirect()->back();
        }
    }

    public function deletAdminUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();

            return redirect('admin/admin-users')->with('delete-user-status', 'admin user has been deleted successfully!');
        } else {
            return redirect("admin/admin-users");
        }
    }

    public function deletCompanyUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();

            return redirect('admin/company-users')->with('delete-user-status', 'company user has been deleted successfully!');
        } else {
            return redirect("admin/company-users");
        }
    }

    public function deletAgentUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();

            return redirect('admin/agent-users')->with('delete-user-status', 'Agent user has been deleted successfully!');
        } else {
            return redirect("admin/agent-users");
        }
    }

    public function deleteFreeToneUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();

            return redirect('admin/free-toner-users')->with('delete-user-status', 'Free Toner user has been deleted successfully!');
        } else {
            return redirect("admin/free-toner-users");
        }
    }

    public function deleteAgentManager($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();

            return redirect('admin/agent-managers-users')->with('delete-user-status', 'Agent manager user has been deleted successfully!');
        } else {
            return redirect("admin/agent-managers-users");
        }
    }

    public function deletSelectedAdminUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function deleteSelectedFreeToneUser($user_id) {
        $user = User::find($user_id);
        if ($user) {
            $user->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function deletSelectedAgentUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function deleteAgentManagerSelected($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function deletSelectedCompanyUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function listRoles() {

        return view("admin::list-roles");
    }

    public function listRolesData() {
        \App::setLocale('en');
        $role_list = Role::all();
        $role_listing = $role_list->reject(function ($role) {
            return ($role->slug == "superadmin") == true;
        });
        return Datatables::collection($role_listing)->make(true);
    }

    public function updateRole(Request $request, $role_id) {

        $role = Role::find($role_id);

        if ($role) {
            if ($request->method() == "GET") {
                return view('admin::edit-role', ['role' => $role]);
            } else {
                $data = $request->all();
                $validate_response = Validator::make($data, [
                            'slug' => 'required|unique:roles,slug,' . $role->id,
                            'name' => 'required'
                                ], [
                            'slug.required' => 'Please enter slug for role',
                            'slug.unique' => 'The entered slug is already in use. Please try another',
                            'name.required' => 'Please enter name'
                                ]
                );

                if ($validate_response->fails()) {
                    return redirect('admin/manage-roles/' . $role->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {

                    $role->name = $request->name;
                    $role->slug = $request->slug;
                    $role->description = $request->description;
                    $role->level = $request->level;
                    $role->save();

                    return redirect('admin/manage-roles')
                                    ->with("update-role-status", "Role informations has been updated successfully");
                }
            }
        } else {
            return redirect('admin/manage-roles');
        }
    }

    public function createRole(Request $request) {
        if ($request->method() == "GET") {
            return view('admin::create-role');
        } else {
            $data = $request->all();
            $validate_response = Validator::make($data, [
                        'slug' => 'required|unique:roles,slug',
                        'name' => 'required'
                            ], [
                        'slug.required' => 'Please enter slug for role',
                        'slug.unique' => 'The entered slug is already in use. Please try another',
                        'name.required' => 'Please enter name'
                            ]
            );

            if ($validate_response->fails()) {
                return redirect('admin/roles/create')
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $role['name'] = $request->name;
                $role['slug'] = $request->slug;
                $role['description'] = $request->description;
                $role['level'] = $request->level;

                Role::create($role);

                return redirect('admin/manage-roles/')
                                ->with("role-status", "Role created successfully");
            }
        }
    }

    public function updateRolePermissions(Request $request, $role_id) {
        $role = Role::find($role_id);

        if ($role) {
            if ($request->method() == "GET") {
                $all_permissions = Permission::orderBy('model')->get();

                $role_permissions = $role->permissions;

                return view("admin::role-permissions", array('role' => $role, 'permissions' => $all_permissions, 'role_permissions' => $role_permissions));
            } else {
                $role->detachAllPermissions();
                $role->save();
                if (count($request->permission) > 0) {
                    foreach ($request->permission as $sel_permission) {
                        $role->attachPermission($sel_permission);
                    }

                    $role->save();
                }

                return redirect('admin/manage-roles')
                                ->with("set-permission-status", "Role permissions has been updated successfully");
            }
        } else {
            return redirect('admin/manage-roles');
        }
    }

    public function deleteRole($role_id) {
        $role = Role::find($role_id);

        if ($role) {
            $role->delete();
            return redirect('admin/manage-roles/')
                            ->with("delete-role-status", "Role has been deleted successfully");
        } else {
            return redirect('admin/manage-roles');
        }
    }

    public function deleteRoleFromSelectAll($role_id) {
        $role = Role::find($role_id);

        if ($role) {
            $role->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function listGlobalSettings() {
        return view("admin::list-global-settings");
    }

    public function listGlobalSettingsData() {
        \App::setLocale('en');
        $global_settings = GlobalSetting::all();
        return Datatables::of($global_settings)
                        ->addColumn('name', function($global) {
                            return $value = $global->name;
                        })
                        ->addColumn('value', function($global) {
                            $value = '';
                            if ($global->slug == 'sitse-logo') {
                                $value = '<img src="' . storage("/storageasset/global-settings/$global->value") . '">';
                            } else {
                                $value = $global->value;
                            }
                            return $value;
                        })
                        ->make(true);
    }

    public function updateGlobalSetting(Request $request, $setting_id) {

        $global_setting = GlobalSetting::find($setting_id);

        if ($global_setting) {
            if ($request->method() == "GET") {
                return view("admin::edit-global-settings", array('setting' => $global_setting));
            } else {
                $data = $request->all();

                $validate_response = Validator::make($data, array(
                            'value' => $global_setting->validate,
                                )
                );

                if ($validate_response->fails()) {
                    return redirect('/admin/update-global-setting/' . $global_setting->id)->withErrors($validate_response)->withInput();
                } else {

                    if (in_array("image", explode("|", $global_setting->validate))) {
                        $extension = $request->file('value')->getClientOriginalExtension();

                        $new_file_name = time() . "." . $extension;
                        Storage::put('public/global-settings/' . $new_file_name, file_get_contents($request->file('value')->getRealPath()));

                        $global_setting->value = $new_file_name;
                    } else {
                        $global_setting->value = $request->value;
                    }

                    $global_setting->save();
                    Cache::forget($global_setting->slug);
                    return redirect('/admin/global-settings')->with('update-setting-status', 'Global setting info has been updated successfully!');
                }
            }
        } else {
            return redirect('admin/global-settings');
        }
    }

    public function uploadUserImage(Request $request, $user_id) {
        $user = User::find($user_id);

        if ($request->file('profile_picture') != '') {

            $extension = $request->file('profile_picture')->getClientOriginalExtension();
            $path = realpath(dirname(__FILE__) . '/../../../../');
            $new_file_name = time() . "." . $extension;
            $old_file = $path . '/storage/app/public/user-images/' . $new_file_name;
            $new_file = $path . '/storage/app/public/user-images/' . $new_file_name;
            Storage::put('public/user-images/' . $new_file_name, file_get_contents($request->file('profile_picture')->getRealPath()));
            $command = "convert " . $old_file . " -resize 300x200^ " . $new_file;
            exec($command);
            $user->userInformation->profile_picture = $new_file_name;
            $user->userInformation->save();
        }
        return redirect('/admin/update-star-user/' . $user_id)->with('update-image-success', 'Image has been uploaded successfully!');
    }

    public function approveUserImage(Request $request, $user_id) {
        $user = User::find($user_id);

        if (isset($user->userInformation->profile_picture_temp) && ($user->userInformation->profile_picture_temp != '')) {
            if ($user->userInformation->profile_picture != '') {
                $path = realpath(dirname(__FILE__) . '/../../../../');
                $old_file = $path . '/storage/app/public/user-images/' . $user->userInformation->profile_picture;
                @unlink($old_file);
                $user->userInformation->profile_picture = "";
                $user->userInformation->save();
            }


            $user->userInformation->profile_picture = $user->userInformation->profile_picture_temp;
            $user->userInformation->profile_picture_temp = "";
            $user->userInformation->save();
        }
        return redirect('/admin/update-star-user/' . $user_id)->with('update-image-success', 'Driver user Image has been approved/updated successfully!');
    }

    public function listAdminUsers() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        return view("admin::list-admin-users", array("all_countries" => $all_countries));
    }

    public function listCompanyUsers() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        return view("admin::list-company-users", array("all_countries" => $all_countries));
    }

    public function listAgentUsers() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        if (Auth::user()) {
            return view("admin::list-agent-users", array("all_countries" => $all_countries));
        } else {
            return redirect("admin/login");
            exit;
        }
    }

    public function listfreeTonnerUsers() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        return view("admin::list-toner-users", array("all_countries" => $all_countries));
    }

    public function listCompanyUsersData(Request $request) {
        \App::setLocale('en');
        $all_users = UserInformation::where('user_type', '5')->get();
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                }
            }

            if ($country != 17) {
                $admin_users = $all_users->reject(function ($user) use ($country) {
                    $user_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $user_country = $address->user_country;
                        }
                    }
                    return ($user->user_type != '5' || $user_country != $country);
                });
            } else {
                $admin_users = $all_users->reject(function ($user) {

                    return (($user->user->hasRole('superadmin') || ($user->user_type != 5)));
                });
            }
        } else if (Auth::user()->userInformation->user_type == '6') {

            $admin_users = $all_users->reject(function ($user) {

                return ($user->user->supervisor_id != Auth::user()->id);
            });
        } else if (Auth::user()->userInformation->user_type == '4') {

            $admin_users = $all_users->reject(function ($user) {

                return ($user->user->supervisor_id != Auth::user()->id);
            });
        } else {
            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 5)));
            });
        }

        $search_country = $request->search_country;
        $search_state = $request->search_state;
        $search_city = $request->search_city;
        //$filter_type=$request->filter_type;
        if ($search_country != "") {
            $admin_users = $all_users->reject(function ($user) use($search_country, $search_state, $search_city) {

                $user_country = 0;
                $user_state = 0;
                $user_city = 0;
                if ($user->user->userAddress) {
                    foreach ($user->user->userAddress as $address) {
                        $user_country = $address->user_country;
                        $user_state = $address->user_state;
                        $user_city = $address->user_city;
                    }
                }
                $flag = 1;
                if ($user_country != $search_country) {
                    $flag = 0;
                }
                if ($search_state != '') {
                    if ($user_state != $search_state) {
                        $flag = 0;
                    }
                }
                if ($search_city != '') {
                    if ($user_city != $search_city) {
                        $flag = 0;
                    }
                }
                return ($flag == 0);
            });
        }

        return Datatables::of($admin_users)
                        ->addColumn('first_name', function($regsiter_user) {
                            return $regsiter_user->first_name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($admin_users) {
                            return $admin_users->user->email;
                        })
                        ->addColumn('role', function($admin_users) {
                            $role = "";
                            if (isset($admin_users->user->getRoles()->first()->name)) {
                                $role = $admin_users->user->getRoles()->first()->name;
                            }
                            return $role;
                        })
                        ->addColumn('location', function($admin_users) {

                            $location = '';

                            if ($admin_users->user->userAddress) {
                                foreach ($admin_users->user->userAddress as $address) {

                                    if (isset($address->countryinfo)) {

                                        $location .= $address->countryinfo->translate()->name;
                                        $location .= " /" . $address->stateInfo->translate()->name;
                                        $location .= " /" . $address->cityInfo->translate()->name;
                                    }
                                }
                            }
                            return $location;
                        })
                        ->addColumn('status', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 0) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="inactive_div' . $admin_users->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Inactive </a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked </a> </div>';
                            } else if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            } else {//                              
                                $html = '<div  id="active_div' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('blocked', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }if ($admin_users->user_status == 1) {//                              
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;

//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                        ->make(true);
    }

    public function listAdminUsersData(Request $request) {
        \App::setLocale('en');
        $all_users = UserInformation::where('user_type', '1')->get();


        $admin_users = $all_users->reject(function ($user) {

            return $user->user->hasRole('superadmin') || ($user->user_type > 1);
        });
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {
            $userAddress = UserAddress::where('user_id', Auth::user()->id)->first();
            $country = 0;
            if (isset($userAddress->user_country) && $userAddress->user_country != 17) {
                $country = $userAddress->user_country;
                $admin_users = $all_users->reject(function ($user) use($country) {

                    $user_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $user_country = $address->user_country;
                        }
                    }
                    return $user->user->hasRole('superadmin') || ($user->user_type > 1) || ($user_country != $country) || ($user->id == Auth::user()->id) || ($user->user->supervisor_id != Auth::user()->id);
                });
            }
        }
        $search_value = $request->search_value;
        //$filter_type=$request->filter_type;
        if ($search_value != "") {
            $admin_users = $all_users->reject(function ($user) use($search_value) {

                $user_country = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $user_country = $address->user_country;
                    }
                }
                return ($user_country != $search_value);
            });
        }
        return Datatables::of($admin_users)
                        ->addColumn('first_name', function($regsiter_user) {
                            return $regsiter_user->first_name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($admin_users) {
                            return $admin_users->user->email;
                        })
                        ->addColumn('country', function($admin_users) {
                            $location = "";
                            if (isset($admin_users->user->userAddress)) {
                                foreach ($admin_users->user->userAddress as $address) {
                                    if (isset($address->countryinfo)) {

                                        $location .= $address->countryinfo->translate()->name;
                                    }
                                }
                                return $location;
                            } else {
                                return "--";
                            }
                        })
                        ->addColumn('role', function($admin_users) {
                            $role = "";
                            if (isset($admin_users->user->getRoles()->first()->name)) {
                                $role = $admin_users->user->getRoles()->first()->name;
                            }
                            return $role;
                        })
                        ->addColumn('status', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 0) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="inactive_div' . $admin_users->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Inactive </a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked </a> </div>';
                            } else if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            } else {//                              
                                $html = '<div  id="active_div' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('blocked', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }if ($admin_users->user_status == 1) {//                              
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;

//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                        ->make(true);
    }

    public function listAgentUsersData(Request $request) {
        \App::setLocale('en');

        $all_users = UserInformation::where('user_type', '4')->get();
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                }
            }

            if ($country != 17) {
                $admin_users = $all_users->reject(function ($user) use ($country) {
                    $agent_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $agent_country = $address->user_country;
                        }
                    }
                    return (($user->user->hasRole('superadmin') || ($user->user_type != 4) || ($agent_country != $country)));
                });
            } else {
                $admin_users = $all_users;
            }
        } else if (Auth::user()->userInformation->user_type == '5') {
            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 4) || ($user->user->supervisor_id != Auth::user()->id)));
            });
        } else if (Auth::user()->userInformation->user_type == '6') {
            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 4) || ($user->user->supervisor_id != Auth::user()->id)));
            });
        } else {

            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 4)));
            });
        }


        $search_country = $request->search_country;
        $search_state = $request->search_state;
        $search_city = $request->search_city;
        //$filter_type=$request->filter_type;
        if ($search_country != "") {
            $admin_users = $all_users->reject(function ($user) use($search_country, $search_state, $search_city) {

                $user_country = 0;
                $user_state = 0;
                $user_city = 0;
                if ($user->user->userAddress) {
                    foreach ($user->user->userAddress as $address) {
                        $user_country = $address->user_country;
                        $user_state = $address->user_state;
                        $user_city = $address->user_city;
                    }
                }
                $flag = 1;
                if ($user_country != $search_country) {
                    $flag = 0;
                }
                if ($search_state != '') {
                    if ($user_state != $search_state) {
                        $flag = 0;
                    }
                }
                if ($search_city != '') {
                    if ($user_city != $search_city) {
                        $flag = 0;
                    }
                }
                return ($flag == 0);
            });
        }


        $admin_users = $admin_users->sortByDesc('id');


        return Datatables::of($admin_users)
                        ->addColumn('first_name', function($regsiter_user) {
                            return $regsiter_user->first_name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($admin_users) {
                            return $admin_users->user->email;
                        })
                        ->addColumn('location', function($admin_users) {

                            $location = '';

                            if ($admin_users->user->userAddress) {
                                foreach ($admin_users->user->userAddress as $address) {
                                    if (isset($address->countryinfo)) {

                                        $location .= $address->countryinfo->translate()->name;
                                        if (isset($address->stateInfo)) {
                                            if (isset($address->stateInfo->translate()->name)) {
                                                $location .= " /" . $address->stateInfo->translate()->name;
                                            }
                                            if (isset($address->cityInfo)) {
                                                if (isset($address->cityInfo->translate()->name)) {
                                                    $location .= " /" . $address->cityInfo->translate()->name;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            return $location;
                        })
                        ->addColumn('status', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 0) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="inactive_div' . $admin_users->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Inactive </a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked </a> </div>';
                            } else if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            } else {//                              
                                $html = '<div  id="active_div' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('blocked', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }if ($admin_users->user_status == 1) {//                              
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;

//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                        ->make(true);
    }

    public function listfreeTonnerUsersData(Request $request) {
        \App::setLocale('en');
        $all_users = UserInformation::where('user_type', '7')->get();
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                }
            }

            if ($country != 17) {
                $admin_users = $all_users->reject(function ($user) use ($country) {
                    $agent_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $agent_country = $address->user_country;
                        }
                    }
                    return (($user->user->hasRole('superadmin') || ($user->user_type != 7) || ($agent_country != $country)));
                });
            } else {
                $admin_users = $all_users;
            }
        } else if (Auth::user()->userInformation->user_type == '5' || Auth::user()->userInformation->user_type == '4') {
            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 7) || ($user->user->supervisor_id != Auth::user()->id)));
            });
        } else {

            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 7)));
            });
        }
        $search_country = $request->search_country;
        $search_state = $request->search_state;
        $search_city = $request->search_city;
        //$filter_type=$request->filter_type;
        if ($search_country != "") {
            $admin_users = $all_users->reject(function ($user) use($search_country, $search_state, $search_city) {

                $user_country = 0;
                $user_state = 0;
                $user_city = 0;
                if ($user->user->userAddress) {
                    foreach ($user->user->userAddress as $address) {
                        $user_country = $address->user_country;
                        $user_state = $address->user_state;
                        $user_city = $address->user_city;
                    }
                }
                $flag = 1;
                if ($user_country != $search_country) {
                    $flag = 0;
                }
                if ($search_state != '') {
                    if ($user_state != $search_state) {
                        $flag = 0;
                    }
                }
                if ($search_city != '') {
                    if ($user_city != $search_city) {
                        $flag = 0;
                    }
                }
                return ($flag == 0);
            });
        }

        $admin_users = $admin_users->sortByDesc('id');
        return Datatables::of($admin_users)
                        ->addColumn('first_name', function($regsiter_user) {
                            return $regsiter_user->first_name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($admin_users) {
                            return $admin_users->user->email;
                        })
                        ->addColumn('location', function($admin_users) {

                            $location = '';

                            if ($admin_users->user->userAddress) {
                                foreach ($admin_users->user->userAddress as $address) {
                                    if (isset($address->countryinfo)) {

                                        $location .= $address->countryinfo->translate()->name;
                                        $location .= " /" . $address->stateInfo->translate()->name;
                                        $location .= " /" . $address->cityInfo->translate()->name;
                                    }
                                }
                            }
                            return $location;
                        })
                        ->addColumn('status', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 0) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="inactive_div' . $admin_users->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Inactive </a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked </a> </div>';
                            } else if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            } else {//                              
                                $html = '<div  id="active_div' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('blocked', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }if ($admin_users->user_status == 1) {//                              
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;

//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                        ->make(true);
    }

    public function listAgentManagerUsersData(Request $request) {
        \App::setLocale('en');
        $all_users = UserInformation::where('user_type', '6')->get();
        $all_users = $all_users->sortByDesc("id");
        if (Auth::user()->userInformation->user_type == '4') {
            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 6) || ($user->user->supervisor_id != Auth::user()->id)));
            });
        } else {
            $admin_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 6)));
            });
        }
        $search_country = $request->search_country;
        $search_state = $request->search_state;
        $search_city = $request->search_city;
        //$filter_type=$request->filter_type;
        if ($search_country != "") {
            $admin_users = $all_users->reject(function ($user) use($search_country, $search_state, $search_city) {

                $user_country = 0;
                $user_state = 0;
                $user_city = 0;
                if ($user->user->userAddress) {
                    foreach ($user->user->userAddress as $address) {
                        $user_country = $address->user_country;
                        $user_state = $address->user_state;
                        $user_city = $address->user_city;
                    }
                }
                $flag = 1;
                if ($user_country != $search_country) {
                    $flag = 0;
                }
                if ($search_state != '') {
                    if ($user_state != $search_state) {
                        $flag = 0;
                    }
                }
                if ($search_city != '') {
                    if ($user_city != $search_city) {
                        $flag = 0;
                    }
                }
                return ($flag == 0);
            });
        }


        return Datatables::of($admin_users)
                        ->addColumn('first_name', function($regsiter_user) {
                            return $regsiter_user->first_name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($admin_users) {
                            return $admin_users->user->email;
                        })
                        ->addColumn('username', function($admin_users) {

                            return $admin_users->user->username;
                        })
                        ->addColumn('status', function($admin_users) {
                            return ($admin_users->user_status > 0) ? 'Active' : 'Inactive';
                        })
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                        ->make(true);
    }

    public function updateAdminUser(Request $request, $user_id) {
        $arr_user_data = User::find($user_id);

        if ($arr_user_data) {

            if ($request->method() == "GET") {
                $user_country = "0";
                if (isset($arr_user_data->userAddress)) {
                    foreach ($arr_user_data->userAddress as $address) {
                        $user_country = $address->user_country;
                    }
                }

                $all_countries = Country::translatedIn(\App::getLocale())->get()->sortBy("name");

                $all_roles = Role::where('level', "<=", 1)->where('slug', '<>', 'superadmin')->get();

                return view("admin::edit-admin-user", array('user_country' => $user_country, 'user_info' => $arr_user_data, 'countries' => $all_countries, 'roles' => $all_roles));
            } elseif ($request->method() == "POST") {
                $data = $request->all();

                $validate_response = Validator::make($data, array(
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'user_mobile' => 'numeric',
                            'country' => 'required',
                            'user_status' => 'required|numeric',
                                ), array(
                            'role.numeric' => 'Invalid Role! Please reselect'
                                )
                );

                if ($validate_response->fails()) {
                    return redirect('admin/update-admin-user/' . $arr_user_data->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {/** user information goes here *** */
                    if (isset($data["profile_picture"])) {
                        $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                    }
                    if (isset($data["gender"])) {
                        $arr_user_data->userInformation->gender = $data["gender"];
                    }
                    if (isset($data["user_status"])) {
                        $arr_user_data->userInformation->user_status = $data["user_status"];
                    }

                    if (isset($data["first_name"])) {
                        $arr_user_data->userInformation->first_name = $data["first_name"];
                    }
                    if (isset($data["last_name"])) {
                        $arr_user_data->userInformation->last_name = $data["last_name"];
                    }
                    if (isset($data["about_me"])) {
                        $arr_user_data->userInformation->about_me = $data["about_me"];
                    }

                    if (isset($data["user_mobile"])) {
                        $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                    }
                    if (isset($data["country"])) {

                        $userAddress = UserAddress::where('user_id', $user_id)->first();
                        if (count($userAddress) <= 0) {
                            UserAddress::create(array("user_id" => $user_id));
                            $userAddress = UserAddress::where('user_id', $user_id)->first();
                        }
                        $userAddress->user_country = $data["country"];
                        $userAddress->save();
                    }

                    $arr_user_data->userInformation->save();

                    $succes_msg = "Admin user profile has been updated successfully!";
                    return redirect("admin/update-admin-user/" . $arr_user_data->id)->with("profile-updated", $succes_msg);
                }
            }
        } else {
            return redirect("admin/manage-admin-users");
        }
    }

    public function updateCompanyUser(Request $request, $user_id) {
        $arr_user_data = User::find($user_id);
        if (Auth::user()->userInformation->user_type == 6 || Auth::user()->userInformation->user_type == 4) {
            if ($arr_user_data->supervisor_id != Auth::user()->id) {
                return redirect("admin/company-users");
                exit;
            }
        }
        if ($arr_user_data) {

            if ($request->method() == "GET") {
                return view("admin::edit-company-user", array('user_info' => $arr_user_data));
            } elseif ($request->method() == "POST") {
                $data = $request->all();

                $validate_response = Validator::make($data, array(
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'user_mobile' => 'numeric',
                            'user_status' => 'required|numeric',
                                )
                );

                if ($validate_response->fails()) {
                    return redirect('admin/update-company-user/' . $arr_user_data->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {/** user information goes here *** */
                    if (isset($data["profile_picture"])) {
                        $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                    }
                    if (isset($data["gender"])) {
                        $arr_user_data->userInformation->gender = $data["gender"];
                    }
                    if (isset($data["user_status"])) {
                        $arr_user_data->userInformation->user_status = $data["user_status"];
                    }

                    if (isset($data["first_name"])) {
                        $arr_user_data->userInformation->first_name = $data["first_name"];
                    }
                    if (isset($data["last_name"])) {
                        $arr_user_data->userInformation->last_name = $data["last_name"];
                    }
                    if (isset($data["about_me"])) {
                        $arr_user_data->userInformation->about_me = $data["about_me"];
                    }

                    if (isset($data["user_mobile"])) {
                        $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                    }

                    $arr_user_data->userInformation->save();
                    $succes_msg = "Company user profile has been updated successfully!";
                    return redirect("admin/update-company-user/" . $user_id)->with("profile-updated", $succes_msg);
                }
            }
        } else {
            return redirect("admin/company-users");
        }
    }

    public function updateAgentUser(Request $request, $user_id) {
        $arr_user_data = User::find($user_id);

        if (Auth::user()->userInformation->user_type == 6) {
            if ($arr_user_data->supervisor_id != Auth::user()->id) {
                return redirect("admin/agent-users");
                exit;
            }
        }
        $country_login = 0;
        $state_login = 0;
        $city_login = 0;
        if (Auth::user()->userAddress) {

            foreach (Auth::user()->userAddress as $address) {
                $country_login = $address->user_country;
                $state_login = $address->user_state;
                $city_login = $address->user_city;
            }
        }

        if ($arr_user_data) {

            if ($request->method() == "GET") {
                $country = 0;
                $state = 0;
                $city = 0;
                if ($arr_user_data->userAddress) {

                    foreach ($arr_user_data->userAddress as $address) {
                        $country = $address->user_country;
                        $state = $address->user_state;
                        $city = $address->user_city;
                    }
                }

                $all_countries = Country::translatedIn(\App::getLocale())->get();
                $all_company_info = CompanyInformation::where('user_id', $user_id)->first();
                $states = State::where('country_id', $country)->translatedIn(\App::getLocale())->get();
                $cities = City::where('country_id', $country)->where('state_id', $state)->translatedIn(\App::getLocale())->get();
                return view("admin::edit-agent-user", array("city_login" => $city_login, "state_login" => $state_login, "country_login" => $country_login, 'user_info' => $arr_user_data, "user_country" => $country, "user_city" => $city, "user_state" => $state, "countries" => $all_countries, "states" => $states, "cities" => $cities, "company_info" => $all_company_info));
            } elseif ($request->method() == "POST") {
                $data = $request->all();

//				$validate_response = Validator::make($data, array(
//                                    'gender' => 'required',
//                                    'first_name' => 'required',
//                                    'last_name' => 'required',
//                                    'user_mobile' => 'numeric',
//                                    'user_status' => 'required|numeric',
//                                    'country' => 'required|numeric',
//                                    'state' => 'required|numeric',
//                                    'city' => 'required|numeric'
//
//				)
//                                );

                if (isset($data["type"]) && $data["type"] == '1') {
                    $validate_response = Validator::make($data, array(
                                'user_status' => 'required|numeric',
                                'user_mobile' => 'numeric',
                                'comp_name' => 'required',
                                'comp_reg_no' => 'required',
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'country' => 'required',
                                'state' => 'required',
                                'city' => 'required'
                                    )
                    );
                } else {
                    $validate_response = Validator::make($data, array(
                                'gender' => 'required',
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'user_mobile' => 'numeric',
                                'user_status' => 'required|numeric',
                                'country' => 'required|numeric',
                                'state' => 'required|numeric',
                                'city' => 'required|numeric'
                                    ), array(
                                'state.required' => 'Region is required',
                                    )
                    );
                }

                if ($validate_response->fails()) {

                    return redirect('admin/update-agent-user/' . $arr_user_data->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {/** user information goes here *** */

                    if (isset($data["profile_picture"])) {
                        $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                    }
                    if (isset($data["gender"])) {
                        $arr_user_data->userInformation->gender = $data["gender"];
                    }
                    if (isset($data["user_status"])) {
                        $arr_user_data->userInformation->user_status = $data["user_status"];
                    }

                    if (isset($data["first_name"])) {
                        $arr_user_data->userInformation->first_name = $data["first_name"];
                    }
                    if (isset($data["last_name"])) {
                        $arr_user_data->userInformation->last_name = $data["last_name"];
                    }
                    if (isset($data["about_me"])) {
                        $arr_user_data->userInformation->about_me = $data["about_me"];
                    }

                    if (isset($data["user_mobile"])) {
                        $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                    }

                    if (isset($data["type"])) {
                        $arr_user_data->userInformation->is_company = $data["type"];
                    }
                    //get address data
                    $user_address = UserAddress::where('user_id', $user_id)->first();

                    if (count($user_address) <= 0) {
                        $userAddressData = array();
                        $userAddressData['user_id'] = $user_id;
                        if (isset($data["country"])) {
                            $userAddressData['user_country'] = $data["country"];
                        }
                        if (isset($data["state"])) {
                            $userAddressData['user_country'] = $data["state"];
                        }
                        if (isset($data["city"])) {
                            $userAddressData['user_country'] = $data["city"];
                        }
                        UserAddress::create($userAddressData);
                    } else {

                        if (isset($data["country"])) {
                            $user_address->user_country = $data["country"];
                        }
                        if (isset($data["state"])) {
                            $user_address->user_state = $data["state"];
                        }
                        if (isset($data["city"])) {
                            $user_address->user_city = $data["city"];
                        }
                        $user_address->save();
                    }
                    $arr_user_data->userInformation->save();

                    // addding user company details 
                    if (isset($data["type"]) && $data["type"] == '1') {
                        $arr_CompanyDetails = CompanyInformation::where('user_id', $user_id)->first();
                        if (isset($arr_CompanyDetails) && count($arr_CompanyDetails) > 0) {

                            $arr_CompanyDetails->name = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
                            $arr_CompanyDetails->description = isset($data["description"]) ? $data["description"] : "NULL";
                            $arr_CompanyDetails->comp_reg_no = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
                            $arr_CompanyDetails->save();
                        } else {
                            $arr_CompanyDetails["name"] = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
                            $arr_CompanyDetails["description"] = isset($data["description"]) ? $data["description"] : "NULL";
                            $arr_CompanyDetails["comp_reg_no"] = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
                            $arr_CompanyDetails["user_id"] = $user_id;
                            CompanyInformation::create($arr_CompanyDetails);
                        }
                    } else {
                        $arr_CompanyDetails = CompanyInformation::where('user_id', $user_id)->first();
                        if (isset($arr_CompanyDetails) && count($arr_CompanyDetails) > 0) {
                            $arr_CompanyDetails->name = "";
                            $arr_CompanyDetails->description = "";
                            $arr_CompanyDetails->comp_reg_no = "";
                            $arr_CompanyDetails->save();
                        }
                    }



                    $succes_msg = "Agent user profile has been updated successfully!";
                    return redirect("admin/update-agent-user/" . $user_id)->with("profile-updated", $succes_msg);
                }
            }
        } else {
            return redirect("admin/agent-users");
        }
    }

    public function updateFreeTonerUser(Request $request, $user_id) {
        $arr_user_data = User::find($user_id);

        if ($arr_user_data) {

            if ($request->method() == "GET") {
                $country = 0;
                $state = 0;
                $city = 0;
                if ($arr_user_data->userAddress) {

                    foreach ($arr_user_data->userAddress as $address) {
                        $country = $address->user_country;
                        $state = $address->user_state;
                        $city = $address->user_city;
                    }
                }

                $all_countries = Country::translatedIn(\App::getLocale())->get();
                $all_company_info = CompanyInformation::where('user_id', $user_id)->first();
                $states = State::where('country_id', $country)->translatedIn(\App::getLocale())->get();
                $cities = City::where('country_id', $country)->where('state_id', $state)->translatedIn(\App::getLocale())->get();
                return view("admin::edit-toner-user", array('user_info' => $arr_user_data, "user_country" => $country, "user_city" => $city, "user_state" => $state, "countries" => $all_countries, "states" => $states, "cities" => $cities, "company_info" => $all_company_info));
            } elseif ($request->method() == "POST") {
                $data = $request->all();

//				$validate_response = Validator::make($data, array(
//                                    'gender' => 'required',
//                                    'first_name' => 'required',
//                                    'last_name' => 'required',
//                                    'user_mobile' => 'numeric',
//                                    'user_status' => 'required|numeric',
//                                    'country' => 'required|numeric',
//                                    'state' => 'required|numeric',
//                                    'city' => 'required|numeric'
//
//				)
//                                );

                if (isset($data["type"]) && $data["type"] == '1') {
                    $validate_response = Validator::make($data, array(
                                'user_status' => 'required|numeric',
                                'user_mobile' => 'numeric',
                                'comp_name' => 'required',
                                'comp_reg_no' => 'required',
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'country' => 'required',
                                'state' => 'required',
                                'city' => 'required'
                                    )
                    );
                } else {
                    $validate_response = Validator::make($data, array(
                                'gender' => 'required',
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'user_mobile' => 'numeric',
                                'user_status' => 'required|numeric',
                                'country' => 'required|numeric',
                                'state' => 'required|numeric',
                                'city' => 'required|numeric'
                                    ), array(
                                'state.required' => 'Region is required',
                                    )
                    );
                }

                if ($validate_response->fails()) {

                    return redirect('admin/update-free-toner-user/' . $arr_user_data->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {/** user information goes here *** */

                    if (isset($data["profile_picture"])) {
                        $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                    }
                    if (isset($data["gender"])) {
                        $arr_user_data->userInformation->gender = $data["gender"];
                    }
                    if (isset($data["user_status"])) {
                        $arr_user_data->userInformation->user_status = $data["user_status"];
                    }

                    if (isset($data["first_name"])) {
                        $arr_user_data->userInformation->first_name = $data["first_name"];
                    }
                    if (isset($data["last_name"])) {
                        $arr_user_data->userInformation->last_name = $data["last_name"];
                    }
                    if (isset($data["about_me"])) {
                        $arr_user_data->userInformation->about_me = $data["about_me"];
                    }

                    if (isset($data["user_mobile"])) {
                        $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                    }

                    if (isset($data["type"])) {
                        $arr_user_data->userInformation->is_company = $data["type"];
                    }
                    //get address data
                    $user_address = UserAddress::where('user_id', $user_id)->first();

                    if (count($user_address) <= 0) {
                        $userAddressData = array();
                        $userAddressData['user_id'] = $user_id;
                        if (isset($data["country"])) {
                            $userAddressData['user_country'] = $data["country"];
                        }
                        if (isset($data["state"])) {
                            $userAddressData['user_country'] = $data["state"];
                        }
                        if (isset($data["city"])) {
                            $userAddressData['user_country'] = $data["city"];
                        }
                        UserAddress::create($userAddressData);
                    } else {

                        if (isset($data["country"])) {
                            $user_address->user_country = $data["country"];
                        }
                        if (isset($data["state"])) {
                            $user_address->user_state = $data["state"];
                        }
                        if (isset($data["city"])) {
                            $user_address->user_city = $data["city"];
                        }
                        $user_address->save();
                    }
                    $arr_user_data->userInformation->save();

                    // addding user company details 
                    if (isset($data["type"]) && $data["type"] == '1') {
                        $arr_CompanyDetails = CompanyInformation::where('user_id', $user_id)->first();
                        if (isset($arr_CompanyDetails) && count($arr_CompanyDetails) > 0) {

                            $arr_CompanyDetails->name = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
                            $arr_CompanyDetails->description = isset($data["description"]) ? $data["description"] : "NULL";
                            $arr_CompanyDetails->comp_reg_no = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
                            $arr_CompanyDetails->save();
                        } else {
                            $arr_CompanyDetails["name"] = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
                            $arr_CompanyDetails["description"] = isset($data["description"]) ? $data["description"] : "NULL";
                            $arr_CompanyDetails["comp_reg_no"] = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
                            $arr_CompanyDetails["user_id"] = $user_id;
                            CompanyInformation::create($arr_CompanyDetails);
                        }
                    } else {
                        $arr_CompanyDetails = CompanyInformation::where('user_id', $user_id)->first();
                        if (isset($arr_CompanyDetails) && count($arr_CompanyDetails) > 0) {
                            $arr_CompanyDetails->name = "";
                            $arr_CompanyDetails->description = "";
                            $arr_CompanyDetails->comp_reg_no = "";
                            $arr_CompanyDetails->save();
                        }
                    }



                    $succes_msg = "Free Toner user profile has been updated successfully!";
                    return redirect("admin/update-free-toner-user/" . $user_id)->with("profile-updated", $succes_msg);
                }
            }
        } else {
            return redirect("admin/free-toner-users");
        }
    }

    public function updateAgentManagerUsers(Request $request, $user_id) {
        $arr_user_data = User::find($user_id);

        if ($arr_user_data) {

            if ($request->method() == "GET") {
                $country = 0;
                $state = 0;
                $city = 0;
                if ($arr_user_data->userAddress) {

                    foreach ($arr_user_data->userAddress as $address) {
                        $country = $address->user_country;
                        $state = $address->user_state;
                        $city = $address->user_city;
                    }
                }
                $all_countries = Country::translatedIn(\App::getLocale())->get();
                $all_company_info = CompanyInformation::where('user_id', $user_id)->first();
                $states = State::where('country_id', $country)->translatedIn(\App::getLocale())->get();
                $cities = City::where('country_id', $country)->where('state_id', $state)->translatedIn(\App::getLocale())->get();
                return view("admin::edit-agent-manager-user", array('user_info' => $arr_user_data, "user_country" => $country, "user_city" => $city, "user_state" => $state, "countries" => $all_countries, "states" => $states, "cities" => $cities, "company_info" => $all_company_info));
                //   return view("admin::edit-agent-manager-user", array('user_info' => $arr_user_data));
            } elseif ($request->method() == "POST") {
                $data = $request->all();

                $validate_response = Validator::make($data, array(
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'user_mobile' => 'numeric',
                            'user_status' => 'required|numeric',
                            'user_mobile' => 'required|numeric',
                            'country' => 'required|numeric',
                            'state' => 'required|numeric',
                            'city' => 'required|numeric'
                                ), array(
                            'state.required' => 'Region is required',
                                )
                );

                if ($validate_response->fails()) {

                    return redirect('admin/update-agent-manager-user/' . $arr_user_data->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {/** user information goes here *** */

                    if (isset($data["profile_picture"])) {
                        $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                    }
                    if (isset($data["gender"])) {
                        $arr_user_data->userInformation->gender = $data["gender"];
                    }
                    if (isset($data["user_status"])) {
                        $arr_user_data->userInformation->user_status = $data["user_status"];
                    }

                    if (isset($data["first_name"])) {
                        $arr_user_data->userInformation->first_name = $data["first_name"];
                    }
                    if (isset($data["last_name"])) {
                        $arr_user_data->userInformation->last_name = $data["last_name"];
                    }
                    if (isset($data["about_me"])) {
                        $arr_user_data->userInformation->about_me = $data["about_me"];
                    }

                    if (isset($data["user_mobile"])) {
                        $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                    }
                    //get address data
                    $user_address = UserAddress::where('user_id', $user_id)->first();

                    if (count($user_address) <= 0) {
                        $userAddressData = array();
                        $userAddressData['user_id'] = $user_id;
                        if (isset($data["country"])) {
                            $userAddressData['user_country'] = $data["country"];
                        }
                        if (isset($data["state"])) {
                            $userAddressData['user_country'] = $data["state"];
                        }
                        if (isset($data["city"])) {
                            $userAddressData['user_country'] = $data["city"];
                        }
                        UserAddress::create($userAddressData);
                    } else {

                        if (isset($data["country"])) {
                            $user_address->user_country = $data["country"];
                        }
                        if (isset($data["state"])) {
                            $user_address->user_state = $data["state"];
                        }
                        if (isset($data["city"])) {
                            $user_address->user_city = $data["city"];
                        }
                        $user_address->save();
                    }

//                    $arr_user_data->userAddress->save();
                    $arr_user_data->userInformation->save();
                    $succes_msg = "Agent manager user profile has been updated successfully!";
                    return redirect("admin/update-agent-manager-user/" . $user_id)->with("profile-updated", $succes_msg);
                }
            }
        } else {
            return redirect("admin/agent-managers-users");
        }
    }

    public function createUser(Request $request, $is_admin = false) {
        if ($request->method() == "GET") {
            $all_countries = Country::translatedIn(\App::getLocale())->get()->sortBy("name");
            $all_roles = Role::where('slug', '<>', 'superadmin')->where('slug', '<>', 'registered.user')->get();
            $filtered_reg_role = $all_roles->filter(function($value, $key) {
                        return $value->slug == 'registered.user';
                    })->first();
//            $role_id_registered_users = $filtered_reg_role->id;

            return view("admin::create-admin-user", array('roles' => $all_roles, 'is_admin' => $is_admin, "countries" => $all_countries));
        } elseif ($request->method() == "POST") {
            $data = $request->all();
            if ((Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin')))) {
                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required'
                                ), array(
                            'role.numeric' => 'Invalid Role! Please reselect'
                                )
                );
            } else {
                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'gender' => 'required',
                            'role' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'country' => 'required'
                                ), array(
                            'role.numeric' => 'Invalid Role! Please reselect'
                                )
                );
            }
            if ($validate_response->fails()) {
                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $created_user = User::create(array(
                            'email' => $data['email'],
                            'password' => ($data['password']),
                            'username' => ($data['email']),
                            'supervisor_id' => Auth::user()->id
                ));


                // update User Information

                /*
                 * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                 */
                $data["user_type"] = isset($data["user_type"]) ? $data["user_type"] : "1";    // 1 may have several mean as per enum stored in the database. Here we 
                // took 1 means one of the front end registered users													


                $data["user_status"] = isset($data["user_status"]) ? $data["user_status"] : "0";  // 0 means not active

                $data["gender"] = isset($data["gender"]) ? $data["gender"] : "3";       // 3 means not specified

                $data["profile_picture"] = isset($data["profile_picture"]) ? $data["profile_picture"] : "";
                $data["facebook_id"] = isset($data["facebook_id"]) ? $data["facebook_id"] : "";
                $data["twitter_id"] = isset($data["twitter_id"]) ? $data["twitter_id"] : "";
                $data["google_id"] = isset($data["google_id"]) ? $data["google_id"] : "";
                $data["user_birth_date"] = isset($data["user_birth_date"]) ? $data["user_birth_date"] : "";
                $data["first_name"] = isset($data["first_name"]) ? $data["first_name"] : "";
                $data["last_name"] = isset($data["last_name"]) ? $data["last_name"] : "";
                $data["about_me"] = isset($data["about_me"]) ? $data["about_me"] : "";
                $data["user_mobile"] = isset($data["user_mobile"]) ? $data["user_mobile"] : "";
                $arr_userinformation = array();
                $arr_userinformation["profile_picture"] = $data["profile_picture"];
                $arr_userinformation["gender"] = $data["gender"];
                $arr_userinformation["activation_code"] = "";             // By default it'll be no activation code
                $arr_userinformation["facebook_id"] = $data["facebook_id"];
                $arr_userinformation["twitter_id"] = $data["twitter_id"];
                $arr_userinformation["google_id"] = $data["google_id"];
                $arr_userinformation["user_birth_date"] = $data["user_birth_date"];
                $arr_userinformation["first_name"] = $data["first_name"];
                $arr_userinformation["last_name"] = $data["last_name"];
                $arr_userinformation["about_me"] = $data["about_me"];
                $arr_userinformation["user_mobile"] = $data["user_mobile"];
                $arr_userinformation["user_status"] = $data["user_status"];
                $arr_userinformation["user_type"] = $data["user_type"];
                $arr_userinformation["user_id"] = $created_user->id;

                $updated_user_info = UserInformation::create($arr_userinformation);

                if (!(Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin')))) {
                    $arr_userAddress["user_country"] = $request->country;
                } else {
                    if (Auth::user()->userAddress) {

                        foreach (Auth::user()->userAddress as $address) {
                            $country = $address->user_country;
                            $arr_userAddress["user_country"] = $country;
                        }
                    }
                }

                $arr_userAddress["user_id"] = $created_user->id;

                $updated_user_address = UserAddress::create($arr_userAddress);

                $userRole = Role::where("slug", $data['role'])->first();
                $created_user->attachRole($userRole);
                $created_user->save();
                $arr_keyword_values = array();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $activation_code = $this->generateReferenceNumber();
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                $arr_keyword_values['LAST_NAME'] = $updated_user_info->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $updated_user_info->activation_code = $activation_code;
                $updated_user_info->save();
                if (isset($created_user->email) && $created_user->email != '') {
                    Mail::send('emailtemplate::admin-registration-successfull', $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {

                        $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
                    });
                }
                return redirect('admin/admin-users')
                                ->with("update-user-status", "admin user has been created successfully");
            }
        }
    }

    public function createCompanyUser(Request $request, $is_admin = false) {

        if ($request->method() == "GET") {
            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }

            $cities = array();
            $states = array();

            if ($country != '17') {
                $states = State::where('country_id', $country)->translatedIn(\App::getLocale())->get();

                if ($state != '32') {
                    $cities = City::where('state_id', $state)->translatedIn(\App::getLocale())->get();

                    if ($city == '22') {
                        $cities = City::where('country_id', $country)->where('state_id', $state)->translatedIn(\App::getLocale())->get();
                    }
                }
            }


            $all_countries = Country::translatedIn(\App::getLocale())->get()->sortBy("name");
            return view("admin::create-company-user", array("city" => $city, "state" => $state, "cities" => $cities, "states" => $states, "country" => $country, "countries" => $all_countries));
        } elseif ($request->method() == "POST") {
            $data = $request->all();
            if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {
                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required'
                ));
            } else {
                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'country' => 'required',
                            'state' => 'required',
                            'city' => 'required'
                                )
                );
            }
            if ($validate_response->fails()) {

                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $created_user = User::create(array(
                            'email' => $data['email'],
                            'password' => ($data['password']),
                            'username' => ($data['email']),
                            'supervisor_id' => Auth::user()->id,
                ));


                // update User Information

                /*
                 * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                 */
                $data["user_type"] = isset($data["user_type"]) ? $data["user_type"] : "1";    // 1 may have several mean as per enum stored in the database. Here we 
                // took 1 means one of the front end registered users													


                $data["user_status"] = isset($data["user_status"]) ? $data["user_status"] : "0";  // 0 means not active

                $data["gender"] = isset($data["gender"]) ? $data["gender"] : "3";       // 3 means not specified

                $data["profile_picture"] = isset($data["profile_picture"]) ? $data["profile_picture"] : "";
                $data["facebook_id"] = isset($data["facebook_id"]) ? $data["facebook_id"] : "";
                $data["twitter_id"] = isset($data["twitter_id"]) ? $data["twitter_id"] : "";
                $data["google_id"] = isset($data["google_id"]) ? $data["google_id"] : "";
                $data["user_birth_date"] = isset($data["user_birth_date"]) ? $data["user_birth_date"] : "";
                $data["first_name"] = isset($data["first_name"]) ? $data["first_name"] : "";
                $data["last_name"] = isset($data["last_name"]) ? $data["last_name"] : "";
                $data["about_me"] = isset($data["about_me"]) ? $data["about_me"] : "";
                $data["user_mobile"] = isset($data["user_mobile"]) ? $data["user_mobile"] : "";
                $arr_userinformation = array();
                $arr_userinformation["profile_picture"] = $data["profile_picture"];
                $arr_userinformation["gender"] = $data["gender"];
                $arr_userinformation["activation_code"] = "";             // By default it'll be no activation code
                $arr_userinformation["facebook_id"] = $data["facebook_id"];
                $arr_userinformation["twitter_id"] = $data["twitter_id"];
                $arr_userinformation["google_id"] = $data["google_id"];
                $arr_userinformation["user_birth_date"] = $data["user_birth_date"];
                $arr_userinformation["first_name"] = $data["first_name"];
                $arr_userinformation["last_name"] = $data["last_name"];
                $arr_userinformation["about_me"] = $data["about_me"];
                $arr_userinformation["user_mobile"] = $data["user_mobile"];
                $arr_userinformation["user_status"] = $data["user_status"];
                $arr_userinformation["user_type"] = 5;
                $arr_userinformation["user_id"] = $created_user->id;

                $updated_user_info = UserInformation::create($arr_userinformation);

                $userRole = Role::where("slug", "role.company")->first();
                $created_user->attachRole($userRole);

                $created_user->save();

                $country = $data["country"];
                $state = $data["state"];
                $city = $data["city"];
                $arr_userAddress["user_country"] = $country;
                $arr_userAddress["user_state"] = $state;
                $arr_userAddress["user_city"] = $city;
                $arr_userAddress["user_id"] = $created_user->id;

                UserAddress::create($arr_userAddress);

                $arr_keyword_values = array();
                $activation_code = $this->generateReferenceNumber();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                $arr_keyword_values['LAST_NAME'] = $updated_user_info->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $updated_user_info->activation_code = $activation_code;
                $updated_user_info->save();

                Mail::send('emailtemplate::admin-registration-successfull', $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {

                    $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
                });

                return redirect('admin/company-users')
                                ->with("update-user-status", "company user has been created successfully");
            }
        }
    }

    public function createAgentUser(Request $request, $is_admin = false) {

        if ($request->method() == "GET") {
            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $states = array();
            $cities = array();
            if ($country != '17') {
                $states = State::where('country_id', $country)->translatedIn(\App::getLocale())->get();

                if ($state != '32') {
                    $cities = City::where('state_id', $state)->translatedIn(\App::getLocale())->get();
                }
            }

            $all_countries = Country::translatedIn(\App::getLocale())->get()->sortBy("name");

            return view("admin::create-agent-user", array("city" => $city, "state" => $state, "cities" => $cities, "states" => $states, "country" => $country, "countries" => $all_countries));
        } elseif ($request->method() == "POST") {


            $data = $request->all();
            if (isset($data["type"]) && $data["type"] == '1') {

                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'comp_name' => 'required',
                            'comp_reg_no' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'country' => 'required',
                            'state' => 'required',
                            'mobile_code' => 'required',
                            'city' => 'required'
                                ), array(
                            'state.required' => 'Region is required',
                                )
                );
            } else {
                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'country' => 'required',
                            'state' => 'required',
                            'mobile_code' => 'required',
                            'city' => 'required'
                                ), array(
                            'state.required' => 'Region is required',
                                )
                );
            }
            if ($validate_response->fails()) {
                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $created_user = User::create(array(
                            'email' => $data['email'],
                            'password' => ($data['password']),
                            'username' => ($data['email']),
                            'supervisor_id' => Auth::user()->id
                ));
                $user_code = $data['user_mobile'] . '-' . $created_user->id;


                // update User Information

                /*
                 * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                 */
                $data["user_type"] = isset($data["user_type"]) ? $data["user_type"] : "1";    // 1 may have several mean as per enum stored in the database. Here we 
                // took 1 means one of the front end registered users													


                $data["user_status"] = isset($data["user_status"]) ? $data["user_status"] : "0";  // 0 means not active

                $data["gender"] = isset($data["gender"]) ? $data["gender"] : "3";       // 3 means not specified

                $data["profile_picture"] = isset($data["profile_picture"]) ? $data["profile_picture"] : "";
                $data["facebook_id"] = isset($data["facebook_id"]) ? $data["facebook_id"] : "";
                $data["twitter_id"] = isset($data["twitter_id"]) ? $data["twitter_id"] : "";
                $data["google_id"] = isset($data["google_id"]) ? $data["google_id"] : "";
                $data["user_birth_date"] = isset($data["user_birth_date"]) ? $data["user_birth_date"] : "";
                $data["first_name"] = isset($data["first_name"]) ? $data["first_name"] : "";
                $data["last_name"] = isset($data["last_name"]) ? $data["last_name"] : "";
                $data["about_me"] = isset($data["about_me"]) ? $data["about_me"] : "";
                $data["user_mobile"] = isset($data["user_mobile"]) ? $data["user_mobile"] : "";
                $data["mobile_code"] = isset($data["mobile_code"]) ? $data["mobile_code"] : "";
                $arr_userinformation = array();
                $arr_userinformation["profile_picture"] = $data["profile_picture"];
                $arr_userinformation["gender"] = $data["gender"];
                $arr_userinformation["activation_code"] = "";             // By default it'll be no activation code
                $arr_userinformation["facebook_id"] = $data["facebook_id"];
                $arr_userinformation["twitter_id"] = $data["twitter_id"];
                $arr_userinformation["google_id"] = $data["google_id"];
                $arr_userinformation["user_birth_date"] = $data["user_birth_date"];
                $arr_userinformation["first_name"] = $data["first_name"];
                $arr_userinformation["last_name"] = $data["last_name"];
                $arr_userinformation["about_me"] = $data["about_me"];
                $arr_userinformation["user_mobile"] = $data["user_mobile"];
                $arr_userinformation["mobile_code"] = isset($data["mobile_code"]) ? str_replace("+", "", $data["mobile_code"]) : '';
                $arr_userinformation["user_status"] = $data["user_status"];
                $arr_userinformation["user_type"] = 4;
                $arr_userinformation["is_company"] = $data["type"];
                $arr_userinformation["user_id"] = $created_user->id;
                $arr_userinformation["user_code"] = $user_code;

                $updated_user_info = UserInformation::create($arr_userinformation);

                //addding user country state city 

                $arr_userAddress["user_country"] = isset($data["country"]) ? $data["country"] : $country;
                $arr_userAddress["user_state"] = isset($data["state"]) ? $data["state"] : $state;
                $arr_userAddress["user_city"] = isset($data["city"]) ? $data["city"] : $city;
                $arr_userAddress["user_id"] = $created_user->id;
                UserAddress::create($arr_userAddress);

                // addding user company details 
                if (isset($data["type"]) && $data["type"] == '1') {
                    $arr_CompanyDetails["name"] = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
                    $arr_CompanyDetails["description"] = isset($data["description"]) ? $data["description"] : "NULL";
                    $arr_CompanyDetails["comp_reg_no"] = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
                    $arr_CompanyDetails["user_id"] = $created_user->id;
                    CompanyInformation::create($arr_CompanyDetails);
                }
                $userRole = Role::where("slug", "role.agent")->first();
                $created_user->attachRole($userRole);

                $created_user->save();
                //

                $arr_keyword_values = array();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $activation_code = $this->generateReferenceNumber();
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                $arr_keyword_values['LAST_NAME'] = $updated_user_info->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $updated_user_info->activation_code = $activation_code;
                $updated_user_info->save();

                Mail::send('emailtemplate::admin-registration-successfull', $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {

                    $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
                });

                return redirect('admin/agent-users')
                                ->with("update-user-status", "Agent user has been created successfully");
            }
        }
    }

    public function createfreeTonnerUser(Request $request, $is_admin = false) {
        if ($request->method() == "GET") {

            $all_countries = Country::translatedIn(\App::getLocale())->get()->sortBy("name");

            return view("admin::create-toner-user", array("countries" => $all_countries));
        } elseif ($request->method() == "POST") {


            $data = $request->all();
            if (isset($data["type"]) && $data["type"] == '1') {

                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'comp_name' => 'required',
                            'comp_reg_no' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'country' => 'required',
                            'state' => 'required',
                            'mobile_code' => 'required',
                            'city' => 'required'
                                ), array(
                            'state.required' => 'Region is required',
                                )
                );
            } else {
                $validate_response = Validator::make($data, array(
                            'email' => 'required|email|max:255|unique:users,email',
                            'password' => 'required|min:6|confirmed',
                            'gender' => 'required',
                            'first_name' => 'required',
                            'last_name' => 'required',
                            'country' => 'required',
                            'state' => 'required',
                            'mobile_code' => 'required',
                            'city' => 'required'
                                ), array(
                            'state.required' => 'Region is required',
                                )
                );
            }
            if ($validate_response->fails()) {
                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $created_user = User::create(array(
                            'email' => $data['email'],
                            'password' => ($data['password']),
                            'username' => ($data['email']),
                            'supervisor_id' => Auth::user()->id
                ));
                $user_code = $data['user_mobile'] . '-' . $created_user->id;


                // update User Information

                /*
                 * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                 */
                $data["user_type"] = isset($data["user_type"]) ? $data["user_type"] : "1";    // 1 may have several mean as per enum stored in the database. Here we 
                // took 1 means one of the front end registered users													


                $data["user_status"] = isset($data["user_status"]) ? $data["user_status"] : "0";  // 0 means not active

                $data["gender"] = isset($data["gender"]) ? $data["gender"] : "3";       // 3 means not specified

                $data["profile_picture"] = isset($data["profile_picture"]) ? $data["profile_picture"] : "";
                $data["facebook_id"] = isset($data["facebook_id"]) ? $data["facebook_id"] : "";
                $data["twitter_id"] = isset($data["twitter_id"]) ? $data["twitter_id"] : "";
                $data["google_id"] = isset($data["google_id"]) ? $data["google_id"] : "";
                $data["user_birth_date"] = isset($data["user_birth_date"]) ? $data["user_birth_date"] : "";
                $data["first_name"] = isset($data["first_name"]) ? $data["first_name"] : "";
                $data["last_name"] = isset($data["last_name"]) ? $data["last_name"] : "";
                $data["about_me"] = isset($data["about_me"]) ? $data["about_me"] : "";
                $data["user_mobile"] = isset($data["user_mobile"]) ? $data["user_mobile"] : "";
                $data["mobile_code"] = isset($data["mobile_code"]) ? $data["mobile_code"] : "";
                $arr_userinformation = array();
                $arr_userinformation["profile_picture"] = $data["profile_picture"];
                $arr_userinformation["gender"] = $data["gender"];
                $arr_userinformation["activation_code"] = "";             // By default it'll be no activation code
                $arr_userinformation["facebook_id"] = $data["facebook_id"];
                $arr_userinformation["twitter_id"] = $data["twitter_id"];
                $arr_userinformation["google_id"] = $data["google_id"];
                $arr_userinformation["user_birth_date"] = $data["user_birth_date"];
                $arr_userinformation["first_name"] = $data["first_name"];
                $arr_userinformation["last_name"] = $data["last_name"];
                $arr_userinformation["about_me"] = $data["about_me"];
                $arr_userinformation["user_mobile"] = $data["user_mobile"];
                $arr_userinformation["mobile_code"] = isset($data["mobile_code"]) ? str_replace("+", "", $data["mobile_code"]) : '';
                $arr_userinformation["user_status"] = $data["user_status"];
                $arr_userinformation["user_type"] = 7;
                $arr_userinformation["is_company"] = $data["type"];
                $arr_userinformation["user_id"] = $created_user->id;
                $arr_userinformation["user_code"] = $user_code;

                $updated_user_info = UserInformation::create($arr_userinformation);

                //addding user country state city 

                $arr_userAddress["user_country"] = isset($data["country"]) ? $data["country"] : "NULL";
                $arr_userAddress["user_state"] = isset($data["state"]) ? $data["state"] : "NULL";
                $arr_userAddress["user_city"] = isset($data["city"]) ? $data["city"] : "NULL";
                $arr_userAddress["user_id"] = $created_user->id;
                UserAddress::create($arr_userAddress);

                // addding user company details 
                if (isset($data["type"]) && $data["type"] == '1') {
                    $arr_CompanyDetails["name"] = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
                    $arr_CompanyDetails["description"] = isset($data["description"]) ? $data["description"] : "NULL";
                    $arr_CompanyDetails["comp_reg_no"] = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
                    $arr_CompanyDetails["user_id"] = $created_user->id;
                    CompanyInformation::create($arr_CompanyDetails);
                }
                $userRole = Role::where("slug", "role.free_toner")->first();
                $created_user->attachRole($userRole);

                $created_user->save();
                //

                $arr_keyword_values = array();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $activation_code = $this->generateReferenceNumber();
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                $arr_keyword_values['LAST_NAME'] = $updated_user_info->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $updated_user_info->activation_code = $activation_code;
                $updated_user_info->save();

                Mail::send('emailtemplate::admin-registration-successfull', $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {

                    $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
                });

                return redirect('admin/free-toner-users')
                                ->with("update-user-status", "Free Toner user has been created successfully");
            }
        }
    }

    public function createAgentManagerUsers(Request $request, $is_admin = false) {
        if ($request->method() == "GET") {

            $all_countries = Country::translatedIn(\App::getLocale())->get();

            return view("admin::create-agent-manager-user", array("countries" => $all_countries));
        } elseif ($request->method() == "POST") {


            $data = $request->all();

            $validate_response = Validator::make($data, array(
                        'email' => 'required|email|max:255|unique:users,email',
                        'password' => 'required|min:6|confirmed',
                        'gender' => 'required',
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'user_mobile' => 'required',
                        'mobile_code' => 'required'
                            )
            );
            if ($validate_response->fails()) {
                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $created_user = User::create(array(
                            'email' => $data['email'],
                            'password' => ($data['password']),
                            'username' => ($data['user_mobile']),
                            'supervisor_id' => Auth::user()->id
                ));


                // update User Information

                /*
                 * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                 */


                $data["user_status"] = isset($data["user_status"]) ? $data["user_status"] : "0";  // 0 means not active

                $data["gender"] = isset($data["gender"]) ? $data["gender"] : "3";       // 3 means not specified

                $data["profile_picture"] = isset($data["profile_picture"]) ? $data["profile_picture"] : "";
                $data["facebook_id"] = isset($data["facebook_id"]) ? $data["facebook_id"] : "";
                $data["twitter_id"] = isset($data["twitter_id"]) ? $data["twitter_id"] : "";
                $data["google_id"] = isset($data["google_id"]) ? $data["google_id"] : "";
                $data["user_birth_date"] = isset($data["user_birth_date"]) ? $data["user_birth_date"] : "";
                $data["first_name"] = isset($data["first_name"]) ? $data["first_name"] : "";
                $data["last_name"] = isset($data["last_name"]) ? $data["last_name"] : "";
                $data["about_me"] = isset($data["about_me"]) ? $data["about_me"] : "";
                $data["user_mobile"] = isset($data["user_mobile"]) ? $data["user_mobile"] : "";
                $data["mobile_code"] = isset($data["mobile_code"]) ? $data["mobile_code"] : "";
                $arr_userinformation = array();
                $arr_userinformation["profile_picture"] = $data["profile_picture"];
                $arr_userinformation["gender"] = $data["gender"];
                $arr_userinformation["activation_code"] = "";             // By default it'll be no activation code
                $arr_userinformation["facebook_id"] = $data["facebook_id"];
                $arr_userinformation["twitter_id"] = $data["twitter_id"];
                $arr_userinformation["google_id"] = $data["google_id"];
                $arr_userinformation["user_birth_date"] = $data["user_birth_date"];
                $arr_userinformation["first_name"] = $data["first_name"];
                $arr_userinformation["last_name"] = $data["last_name"];
                $arr_userinformation["about_me"] = $data["about_me"];
                $arr_userinformation["user_mobile"] = $data["user_mobile"];
                $arr_userinformation["mobile_code"] = $data["mobile_code"];
                $arr_userinformation["user_status"] = $data["user_status"];
                $arr_userinformation["user_type"] = 6;
                $arr_userinformation["user_id"] = $created_user->id;

                $updated_user_info = UserInformation::create($arr_userinformation);

                //addding user country state city 

                $arr_userAddress["user_country"] = isset($data["country"]) ? $data["country"] : "NULL";
                $arr_userAddress["user_state"] = isset($data["state"]) ? $data["state"] : "NULL";
                $arr_userAddress["user_city"] = isset($data["city"]) ? $data["city"] : "NULL";
                $arr_userAddress["user_id"] = $created_user->id;
                UserAddress::create($arr_userAddress);


                $userRole = Role::where("slug", "role.agentmanager")->first();
                $created_user->attachRole($userRole);

                $created_user->save();
                //

                $arr_keyword_values = array();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $activation_code = $this->generateReferenceNumber();
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                $arr_keyword_values['LAST_NAME'] = $updated_user_info->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;
                // updating activation code                 
                $updated_user_info->activation_code = $activation_code;
                $updated_user_info->save();

                Mail::send('emailtemplate::admin-registration-successfull', $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {

                    $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
                });

                return redirect('admin/agent-managers-users')
                                ->with("update-user-status", "Agent Manager user has been created successfully");
            }
        }
    }

//        public function createCompanyUser(Request $request,$is_admin=false)
//	{
//		if($request->method() == "GET" )
//		 {
//			
//				
//				
//				return view("admin::create-company-user");
//		}
//                elseif($request->method() == "POST")
//                {
//				$data = $request->all();
//				$validate_response = Validator::make($data, array(
//                                'email' => 'required|email|max:255|unique:users,email',
//                                'password'=>'required|min:6|confirmed',
//                                'gender' => 'required',
//                                'first_name' => 'required',
//                                'last_name' => 'required'
//                                )
//				
//                                );
//				if($validate_response->fails())
//				{
//					return redirect()->back()
//                                         ->withErrors($validate_response)
//						->withInput();
//				}
//				else
//				{
//                                    
//					$created_user = User::create(array(
//                                        'email' => $data['email'],
//                                        'password' => ($data['password']),
//                                        'username' => ($data['email'])
//                                        ));
//
//
//					// update User Information
//					
//					/*
//					* Adjusted user specific columns, which may not passed on front end and adjusted with the default values
//					*/
//					$data["user_type"] = isset($data["user_type"])?$data["user_type"]:"1";				// 1 may have several mean as per enum stored in the database. Here we 
//																																										// took 1 means one of the front end registered users													
//					
//					
//					$data["user_status"] = isset($data["user_status"])?$data["user_status"]:"0";		// 0 means not active
//					
//					$data["gender"] = isset($data["gender"])?$data["gender"]:"3";							// 3 means not specified
//					
//					$data["profile_picture"]= isset($data["profile_picture"])?$data["profile_picture"]:"";
//					$data["facebook_id"]= isset($data["facebook_id"])?$data["facebook_id"]:"";
//					$data["twitter_id"]= isset($data["twitter_id"])?$data["twitter_id"]:"";
//					$data["google_id"]= isset($data["google_id"])?$data["google_id"]:"";
//					$data["user_birth_date"]= isset($data["user_birth_date"])?$data["user_birth_date"]:"";
//					$data["first_name"]= isset($data["first_name"])?$data["first_name"]:"";
//					$data["last_name"]= isset($data["last_name"])?$data["last_name"]:"";
//					$data["about_me"]= isset($data["about_me"])?$data["about_me"]:"";
//					$data["user_mobile"]= isset($data["user_mobile"])?$data["user_mobile"]:"";					
//					$arr_userinformation = array();					
//					$arr_userinformation["profile_picture"] = $data["profile_picture"];
//					$arr_userinformation["gender"] = $data["gender"];
//					$arr_userinformation["activation_code"] = "";													// By default it'll be no activation code
//					$arr_userinformation["facebook_id"] = $data["facebook_id"];
//					$arr_userinformation["twitter_id"] = $data["twitter_id"];
//					$arr_userinformation["google_id"] = $data["google_id"];
//					$arr_userinformation["user_birth_date"] = $data["user_birth_date"];
//					$arr_userinformation["first_name"] = $data["first_name"];
//					$arr_userinformation["last_name"] = $data["last_name"];
//					$arr_userinformation["about_me"] = $data["about_me"];
//					$arr_userinformation["user_mobile"] = $data["user_mobile"];
//					$arr_userinformation["user_status"] = $data["user_status"];			
//					$arr_userinformation["user_type"] = 5;
//					$arr_userinformation["user_id"] = $created_user->id;
//					
//					$updated_user_info = UserInformation::create($arr_userinformation);
//					
//                                        $userRole = Role::where("slug","role.company")->first();        
//					$created_user->attachRole($userRole);
//					
//					$created_user->save();
//					
//                                         $arr_keyword_values = array();
//                                         $activation_code=$this->generateReferenceNumber();
//                                        //Assign values to all macros
//                                         $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
//                                         $arr_keyword_values['LAST_NAME'] =  $updated_user_info->last_name;
//                                         $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/'.$activation_code);
//
//                                        // updating activation code                 
//                                         $updated_user_info->activation_code=$activation_code;
//                                         $updated_user_info->save();   
//
//                                         Mail::send('emailtemplate::admin-registration-successfull',$arr_keyword_values, function ($message) use ($created_user)  {
//
//                                                        $message->to( $created_user->email, $created_user->name )->subject("Registration Successful!");
//
//                                        });
//					
//					return redirect('admin/company-users')
//                                                  ->with("update-user-status","company user has been created successfully");
//				}
//				
//			}
//		
//	}

    public function listCountries() {

        return view('admin::list-countries');
    }

    public function createGeoCityetting(Request $request) {
        if ($request->method() == "GET") {
            $cityData = City::all();
            return view("admin::create-gei-city-limit", array("city_data" => $cityData));
        } else {
            // validate and proceed
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'location1' => 'required',
                        'location2' => 'required',
                        'city' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect()->back()->withErrors($validate_response)->withInput();
            } else {

                $location1_lat = $request->location1_lat;
                $location1_long = $request->location1_long;

                $location2_lat = $request->location2_lat;
                $location2_long = $request->location2_long;
                $url = "https://maps.googleapis.com/maps/api/directions/json?origin=$location1_lat,$location1_long&destination=$location2_lat,$location2_long&key=AIzaSyD58tVuK_zOf_mw-VWm-rLRWPK5RgGucco";
                // $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$pick_up_lat.",".$pick_up_long."&sensor=false&key=10";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $response = curl_exec($ch);
                curl_close($ch);
                $response_a = json_decode($response, true);
                $northeast_lat = isset($response_a['routes'][0]['bounds']['northeast']['lat']) ? $response_a['routes'][0]['bounds']['northeast']['lat'] : '';
                $northeast_long = isset($response_a['routes'][0]['bounds']['northeast']['lat']) ? $response_a['routes'][0]['bounds']['northeast']['lng'] : '';
                $southwest_lat = isset($response_a['routes'][0]['bounds']['southwest']['lat']) ? $response_a['routes'][0]['bounds']['southwest']['lat'] : '';
                $southwest_long = isset($response_a['routes'][0]['bounds']['southwest']['lat']) ? $response_a['routes'][0]['bounds']['southwest']['lng'] : '';

                $arrGEOCity = array();
                $arrGEOCity['city_id'] = $request->city;
                $arrGEOCity['location1'] = $request->location1;
                $arrGEOCity['location2'] = $request->location2;
                $arrGEOCity['southwest_lat'] = $southwest_lat;
                $arrGEOCity['southwest_long'] = $southwest_long;
                $arrGEOCity['northeast_lat'] = $northeast_lat;
                $arrGEOCity['northeast_long'] = $northeast_long;
                $arrGEOCity['location1_lat'] = $location1_lat;
                $arrGEOCity['location1_long'] = $location1_long;
                $arrGEOCity['location2_lat'] = $location2_lat;
                $arrGEOCity['location2_long'] = $location2_long;

                GeoLimit::create($arrGEOCity);
                return redirect('admin/city-geo-settings/list')->with('country-status', 'Country has been created Successfully!');
            }
        }
    }

    public function updateGeoCitySetting(Request $request, $geo_id = 0) {

        if ($request->method() == "GET") {
            $cityData = City::all();
            $geoLimitData = GeoLimit::where('id', $geo_id)->first();
            return view("admin::update-geo-city-limit", array("geo_id" => $geo_id, "geo_limit_data" => $geoLimitData, "city_data" => $cityData));
        } else {
            // validate and proceed
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'location1' => 'required',
                        'location2' => 'required',
                        'city' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect()->back()->withErrors($validate_response)->withInput();
            } else {

                $location1_lat = $request->location1_lat;
                $location1_long = $request->location1_long;

                $location2_lat = $request->location2_lat;
                $location2_long = $request->location2_long;
                $url = "https://maps.googleapis.com/maps/api/directions/json?origin=$location1_lat,$location1_long&destination=$location2_lat,$location2_long&key=AIzaSyD58tVuK_zOf_mw-VWm-rLRWPK5RgGucco";
                // $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$pick_up_lat.",".$pick_up_long."&sensor=false&key=10";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $response = curl_exec($ch);
                curl_close($ch);
                $response_a = json_decode($response, true);
                $northeast_lat = isset($response_a['routes'][0]['bounds']['northeast']['lat']) ? $response_a['routes'][0]['bounds']['northeast']['lat'] : '';
                $northeast_long = isset($response_a['routes'][0]['bounds']['northeast']['lat']) ? $response_a['routes'][0]['bounds']['northeast']['lng'] : '';
                $southwest_lat = isset($response_a['routes'][0]['bounds']['southwest']['lat']) ? $response_a['routes'][0]['bounds']['southwest']['lat'] : '';
                $southwest_long = isset($response_a['routes'][0]['bounds']['southwest']['lat']) ? $response_a['routes'][0]['bounds']['southwest']['lng'] : '';

                $geoLimitData = GeoLimit::where('id', $geo_id)->first();
                $geoLimitData->city_id = $request->city;
                $geoLimitData->location1 = $request->location1;
                $geoLimitData->location2 = $request->location2;
                $geoLimitData->southwest_lat = $southwest_lat;
                $geoLimitData->southwest_long = $southwest_long;
                $geoLimitData->northeast_lat = $northeast_lat;
                $geoLimitData->northeast_long = $northeast_long;
                $geoLimitData->location1_lat = $location1_lat;
                $geoLimitData->location1_long = $location1_long;
                $geoLimitData->location2_lat = $location2_lat;
                $geoLimitData->location2_long = $location2_long;
                $geoLimitData->save();
                return redirect('admin/city-geo-settings/list')->with('country-status', 'Country has been created Successfully!');
            }
        }
    }

    public function listGeoCitiesSettings() {
        $user = Auth::user();
        if ($user) {
            return view('admin::list-geo-city-limits');
        }
    }

    public function listGeoSettingsData() {
        \App::setLocale('en');
        $user = Auth::user();
        if ($user) {
            $allCityGeoSettings = GeoLimit::all();
            return Datatables::collection($allCityGeoSettings)
                            ->addColumn('city', function($setting) {
                                $city = City::where('id', $setting->city_id)->first();
                                return (isset($city->name) ? $city->name : '');
                            })->make(true);
        }
    }

    public function listCountriesData() {
        \App::setLocale('en');
        $user = Auth::user();
        if ($user->userInformation->user_type == '1' && $user->hasRole('superadmin')) {
            $all_countries = Country::translatedIn(\App::getLocale())->get();
        } else {
            $userAddress = UserAddress::where('user_id', $user->id)->first();

            if (isset($userAddress->user_country)) {
                if ($userAddress->user_country == '17') {
                    $all_countries = Country::translatedIn(\App::getLocale())->get();
                } else {
                    $all_countries = Country::translatedIn(\App::getLocale())->get();
                    $all_countries = $all_countries->reject(function($country) use($userAddress) {
                        return ($country->id != $userAddress->user_country);
                    });
                }
            }
        }
        $all_countries = $all_countries->reject(function($country) {
            return ($country->id == 17);
        });

        return Datatables::collection($all_countries)
                        ->addColumn('Language', function($country) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language .= '<li class="dropdown-item"> <a href="update-language/' . $country->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })->make(true);
    }

    public function createCountry(Request $request) {
        if ($request->method() == "GET") {
            return view("admin::create-country");
        } else {
            // validate and proceed
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'name' => 'required|unique:country_translations,name',
            ));

            if ($validate_response->fails()) {
                return redirect()->back()->withErrors($validate_response)->withInput();
            } else {
                $country = Country::create();
                $en_country = $country->translateOrNew(\App::getLocale());

                $en_country->name = $request->name;
                $en_country->country_id = $country->id;
                $en_country->save();

                return redirect('admin/countries/list')->with('country-status', 'Country has been created Successfully!');
            }
        }
    }

    public function getCountryInfo(Request $request, $country_id) {
        $country = Country::find($country_id);
        $arr_to_return = array("error" => 0, "data" => $country);
        return response()->json($arr_to_return);
    }

    public function updateCountry(Request $request, $country_id) {
        $country = Country::find($country_id);

        if ($country) {
            $is_new_entry = !($country->hasTranslation());

            $translated_country = $country->translate();

            if ($request->method() == "GET") {
                $arr_service_category = Category::translatedIn(\App::getLocale())->get();
                $arr_service = Service::translatedIn(\App::getLocale())->get();
                $countryServices = CountryServices::where('country_id', $country_id)->get();
                return view("admin::update-country", array('country_info' => $translated_country, 'main_info' => $country, "categories" => $arr_service_category, "services" => $arr_service, "country_services" => $countryServices));
            } else {
                // validate and proceed
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required|unique:country_translations,name,' . $translated_country->id,
                            'iso' => 'required',
                            'country_code' => 'required',
                            'support_number' => 'required',
                            'currency_code' => 'required',
                            'max_mobile_digit' => 'required',
                            'cancellation_charge' => 'required',
                            'time_zone' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
                    $country->iso = $request->iso;
                    $country->country_code = $request->country_code;
                    $country->currency_code = $request->currency_code;
                    $country->cancellation_charge = $request->cancellation_charge;
                    $country->time_zone = $request->time_zone;
                    $country->max_mobile_digit = $request->max_mobile_digit;
                    $country->payment_gateway = $request->payment_gateway;
                    $country->save();

                    $translated_country->name = $request->name;
                    $translated_country->support_number = $request->support_number;

                    if ($is_new_entry) {
                        $translated_country->country_id = $country_id;
                    }

                    $translated_country->save();

                    /* save country services */
////                                                                services
//                    CountryServices::where('country_id', $country_id)->delete();
//                    if (isset($data['services']) && !empty($data['services'])) {
//
//                        for ($i = 0; $i < count($data['services']); $i++) {
//
//                            $arr_countryServices = array();
//
//
//                            $arr_countryServices["service_id"] = $data['services'][$i];
//                            if (isset($data['price_type_' . $data['services'][$i]])) {
//                                $arr_countryServices["price_type"] = $data['price_type_' . $data['services'][$i]];
//                            }
//                            if (isset($data['base_price_' . $data['services'][$i]]) && !empty($data['base_price_' . $data['services'][$i]])) {
//                                $arr_countryServices["base_price"] = $data['base_price_' . $data['services'][$i]];
//                            }
//                            if (isset($data['price_per_km_' . $data['services'][$i]]) && !empty($data['price_per_km_' . $data['services'][$i]])) {
//                                $arr_countryServices["price_per_km"] = $data['price_per_km_' . $data['services'][$i]];
//                            }
//                            if (isset($data['price_per_min_' . $data['services'][$i]]) && !empty($data['price_per_min_' . $data['services'][$i]])) {
//                                $arr_countryServices["price_per_min"] = $data['price_per_min_' . $data['services'][$i]];
//                            }
//                            if (isset($data['sort_index_' . $data['services'][$i]]) && !empty($data['sort_index_' . $data['services'][$i]])) {
//                                $arr_countryServices["sort_index"] = $data['sort_index_' . $data['services'][$i]];
//                            }
//                            if (isset($data['sort_index_arabic_' . $data['services'][$i]]) && !empty($data['sort_index_arabic_' . $data['services'][$i]])) {
//                                $arr_countryServices["sort_index_arabic"] = $data['sort_index_arabic_' . $data['services'][$i]];
//                            }
//                            if (isset($data['check_point_distance_' . $data['services'][$i]]) && !empty($data['check_point_distance_' . $data['services'][$i]])) {
//                                $arr_countryServices["check_point_distance"] = $data['check_point_distance_' . $data['services'][$i]];
//                            }
//                            if (isset($data['flat_price_' . $data['services'][$i]]) && !empty($data['flat_price_' . $data['services'][$i]])) {
//                                $arr_countryServices["flat_price"] = $data['flat_price_' . $data['services'][$i]];
//                            }
//                            if (isset($data['base_km_' . $data['services'][$i]]) && !empty($data['base_km_' . $data['services'][$i]])) {
//                                $arr_countryServices["base_km"] = $data['base_km_' . $data['services'][$i]];
//                            }
//                            $arr_countryServices["country_id"] = $country_id;
//                            CountryServices::create($arr_countryServices);
//                        }
//                    }
//                    /* save country services */


                    return redirect('admin/countries/list')->with('update-country-status', 'Country has been updated successfully!');
                }
            }
        } else {
            return redirect("admin/countries/list");
        }
    }

    public function updateCountryLanguage(Request $request, $country_id, $locale) {
        $country = Country::find($country_id);

        if ($country) {
            $is_new_entry = !($country->hasTranslation($locale));

            $translated_country = $country->translateOrNew($locale);

            if ($request->method() == "GET") {
                return view("admin::update-country-language", array('country_info' => $translated_country));
            } else {
                // validate and proceed
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
                    $translated_country->name = $request->name;

                    if ($is_new_entry) {
                        $translated_country->country_id = $country_id;
                    }

                    $translated_country->save();

                    return redirect('admin/countries/list')->with('update-country-status', 'Country updated successfully!');
                }
            }
        } else {
            return redirect("admin/countries/list");
        }
    }

    public function deleteCountry($country_id) {
        $country = Country::find($country_id);

        if ($country) {
            $country->delete();

            return redirect('admin/countries/list')->with('country-status', 'Country has been deleted successfully!');
        } else {
            return redirect("admin/countries/list");
        }
    }

    public function deleteCountrySelected($country_id) {
        $country = Country::find($country_id);

        if ($country) {
            $country->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function listStates() {
        return view('admin::list-states');
    }

    public function getAllStatesByCountry($country_id) {
        $states = State::where('country_id', $country_id)->translatedIn(\App::getLocale())->get();
        $select_value = '<option value="">--Select--</option>';
        if ($country_id != '17') {
            $select_value .= '<option value="32">--ALL--</option>';
        }
        if ($states) {
            foreach ($states as $key => $value) {

                $select_value .= '<option value="' . $value->id . '">' . $value->name . '</option>';
            }
        }
        echo $select_value;
        exit;

        //return view('admin::list-states');
    }

    public function getAllStatesByCountryRegistration($country_id) {
        $states = State::where('country_id', $country_id)->translatedIn(\App::getLocale())->get();
        $select_value = '<option value="">--' . Lang::choice('website_keywords.region', \App::getLocale()) . '--</option>';
        if ($country_id != '17') {
            if ($states) {
                foreach ($states as $key => $value) {
                    if ($value != '32') {
                        $select_value .= '<option value="' . $value->id . '">' . $value->name . '</option>';
                    }
                }
            }
        }
        echo $select_value;
        exit;

        //return view('admin::list-states');
    }

    public function getAllCitiesByCountryState($country_id, $state_id) {
        $cities = City::where('country_id', $country_id)->where('state_id', $state_id)->translatedIn(\App::getLocale())->get();
        $select_value = '<option value="">--Select--</option>';
        if ($state_id == '32') {
            $select_value .= '<option value="22">--ALL--</option>';
        } else {
            $select_value .= '<option value="22">--ALL--</option>';
            if ($cities) {
                foreach ($cities as $key => $value) {

                    $select_value .= '<option value="' . $value->id . '">' . $value->name . '</option>';
                }
            }
        }
        echo $select_value;
        exit;
    }

    public function getAllCitiesByCountryStateStar($country_id, $state_id) {
        $cities = City::where('country_id', $country_id)->where('state_id', $state_id)->translatedIn(\App::getLocale())->get();
        $select_value = '<option value="">--Select--</option>';
        if ($state_id == '32') {
            $select_value .= '<option value="22">--ALL--</option>';
        } else {
            //$select_value.='<option value="22">--ALL--</option>';
            if ($cities) {
                foreach ($cities as $key => $value) {

                    $select_value .= '<option value="' . $value->id . '">' . $value->name . '</option>';
                }
            }
        }
        echo $select_value;
        exit;
    }

    public function getAllCitiesByCountryStateRegistration($country_id, $state_id) {
        $cities = City::where('country_id', $country_id)->where('state_id', $state_id)->translatedIn(\App::getLocale())->get();

        $select_value = '<option value="">--' . Lang::choice('website_keywords.City', \App::getLocale()) . '--</option>';
        if ($state_id != '32') {
            if ($cities) {
                foreach ($cities as $key => $value) {
                    if ($value != '22') {
                        $select_value .= '<option value="' . $value->id . '">' . $value->name . '</option>';
                    }
                }
            }

            echo $select_value;
            exit;

            //return view('admin::list-states');
        }
    }

    public function listStatesData(Request $request) {
        \App::setLocale('en');
        $all_states = State::translatedIn(\App::getLocale())->get();
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {
            $userAddress = UserAddress::where('user_id', Auth::user()->id)->first();
            $country = 0;
            if (isset($userAddress->user_country) && $userAddress->user_country != 17) {
                $country = $userAddress->user_country;
                $all_states = $all_states->reject(function($state) use ($country) {
                    return ($state->country_id != $country);
                });
            }
        }
        $search_value = $request->search_value;
        $filter_type = $request->filter_type;
        if ($search_value != "" && $filter_type != "") {
            if ($filter_type == 'region') {
                $all_states = $all_states->reject(function($state) use($search_value) {


                    $state_text = strstr(strtoupper($state->name), strtoupper($search_value));


                    if ($state_text == '' || $state_text == '0') {
                        return $state;
                    }
                });
            } else if ($filter_type == 'country') {
                $all_states = $all_states->reject(function($state) use($search_value) {


                    $state_text = strstr(strtoupper($state->country->name), strtoupper($search_value));


                    if ($state_text == '' || $state_text == '0') {
                        return $state;
                    }
                });
            }
        }
        //return Datatables::collection($all_states)->make(true);
        return Datatables::of($all_states)
                        ->addColumn('Language', function($state) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language .= '<li class="dropdown-item"> <a href="update-language/' . $state->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })
                        ->addColumn('country', function($state) {
                            return $state->country->translate()->name;
                        })
                        ->make(true);
    }

    public function createState(Request $request) {
        if ($request->method() == "GET") {
            $all_countries = Country::translatedIn(\App::getLocale())->get();
            return view("admin::create-state", array('countries' => $all_countries));
        } else {
            // validate and proceed
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'name' => 'required|unique:state_translations,name',
                        'country' => 'required|numeric'
            ));

            if ($validate_response->fails()) {
                return redirect()->back()->withErrors($validate_response)->withInput();
            } else {

                $state = State::create(['country_id' => $request->country]);

                $en_state = $state->translateOrNew(\App::getLocale());

                $en_state->name = $request->name;
                $en_state->state_id = $state->id;
                $en_state->save();

                return redirect('admin/states/list')->with('state-status', 'State Created Successfully!');
            }
        }
    }

    public function updateState(Request $request, $state_id) {
        $state = State::find($state_id);

        if ($state) {
            $is_new_entry = !($state->hasTranslation());

            $translated_state = $state->translate();

            if ($request->method() == "GET") {
                $all_countries = Country::translatedIn(\App::getLocale())->get();
                return view("admin::update-state", array('state_info' => $translated_state, 'state' => $state, 'countries' => $all_countries));
            } else {
                // validate and proceed
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required|unique:state_translations,name,' . $translated_state->id,
                            'country' => 'required|numeric'
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
                    $translated_state->name = $request->name;
                    $state->country_id = $request->country;

                    if ($is_new_entry) {
                        $translated_state->state_id = $state_id;
                    }

                    $translated_state->save();
                    $state->save();
                    return redirect('admin/states/list')->with('update-state-status', 'Regions has been updated Successfully!');
                }
            }
        } else {
            return redirect("admin/states/list");
        }
    }

    public function updateStateLanguage(Request $request, $state_id, $locale) {
        $state = State::find($state_id);

        if ($state) {
            $is_new_entry = !($state->hasTranslation($locale));

            $translated_state = $state->translateOrNew($locale);

            if ($request->method() == "GET") {
                return view("admin::update-state-language", array('state_info' => $translated_state));
            } else {
                // validate and proceed
                $data = $request->all();

                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
                    $translated_state->name = $request->name;

                    if ($is_new_entry) {
                        $translated_state->state_id = $state_id;
                    }

                    $translated_state->save();

                    return redirect('admin/states/list')->with('update-state-status', 'Region has been updated Successfully!');
                }
            }
        } else {
            return redirect("admin/states/list");
        }
    }

    public function deleteState($state_id) {
        $state = State::find($state_id);

        if ($state) {
            $state->delete();
            return redirect('admin/states/list')->with('state-status', 'State deleted successfully!');
        } else {
            return redirect('admin/states/list');
        }
    }

    public function deleteStateSelected($state_id) {
        $state = State::find($state_id);

        if ($state) {
            $state->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {

            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function listCities() {
        \App::setLocale('en');
        return view('admin::list-cities');
    }

    public function listCitiesData(Request $request) {
        $all_cities = City::translatedIn(\App::getLocale())->get();
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {
            $userAddress = UserAddress::where('user_id', Auth::user()->id)->first();
            $country = 0;
            if (isset($userAddress->user_country) && $userAddress->user_country != 17) {
                $country = $userAddress->user_country;
                $all_cities = $all_cities->reject(function($city) use ($country) {
                    return ($city->country_id != $country);
                });
            }
        }
        $search_value = $request->search_value;
        $filter_type = $request->filter_type;
        if ($search_value != "" && $filter_type != "") {
            if ($filter_type == 'city') {

                $all_cities = $all_cities->reject(function($city) use($search_value) {


                    $state_text = strstr(strtoupper($city->name), strtoupper($search_value));


                    if ($state_text == '' || $state_text == '0') {
                        return $city;
                    }
                });
            } else if ($filter_type == 'region') {
                $all_cities = $all_cities->reject(function($city) use($search_value) {


                    $state_text = strstr(strtoupper($city->state->name), strtoupper($search_value));


                    if ($state_text == '' || $state_text == '0') {
                        return $city;
                    }
                });
            } else if ($filter_type == 'country') {
                $all_cities = $all_cities->reject(function($city) use($search_value) {


                    $state_text = strstr(strtoupper($city->country->name), strtoupper($search_value));


                    if ($state_text == '' || $state_text == '0') {
                        return $city;
                    }
                });
            }
        }
        //return Datatables::collection($all_states)->make(true);
        return Datatables::of($all_cities)
                        ->addColumn('Language', function($city) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language .= '<li class="dropdown-item"> <a href="update-language/' . $city->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })
                        ->addColumn('country', function($city) {
                            return $city->country->translate()->name;
                        })
                        ->addColumn('state', function($cities) {
                            return $cities->state->translate()->name;
                        })
                        ->make(true);
    }

    public function createCity(Request $request) {
        if ($request->method() == "GET") {
            $countries = Country::translatedIn(\App::getLocale())->get();
            $countries = $countries->reject(function($country) {
                return ($country == '17');
            });
            $all_states = State::translatedIn(\App::getLocale())->get();
            return view("admin::create-cities", array('states' => $all_states, "countries" => $countries));
        } else {
            // validate and proceed
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'name' => 'required',
                        'state' => 'required|numeric',
                        'country' => 'required|numeric',
            ));

            if ($validate_response->fails()) {
                return redirect()->back()->withErrors($validate_response)->withInput();
            } else {

                $city = City::create(['state_id' => $request->state, "country_id" => $request->country]);

                $en_city = $city->translateOrNew(\App::getLocale());

                $en_city->name = $request->name;
                $en_city->city_id = $city->id;
                $en_city->save();

                return redirect('admin/cities/list')->with('city-status', 'City has been created Successfully!');
            }
        }
    }

    public function updateCity(Request $request, $city_id) {
        $city = City::find($city_id);
        $city_values = City::where('id', $city_id)->first();

        $country_id = 0;
        if ($city_values) {
            $country_id = $city_values->country_id;
        }
        if ($city) {
            $is_new_entry = !($city->hasTranslation());

            $translated_city = $city->translate();

            if ($request->method() == "GET") {
                $countries = Country::translatedIn(\App::getLocale())->get();
                $states_info = State::where('country_id', $country_id)->translatedIn(\App::getLocale())->get();
                $arr_service = Service::translatedIn(\App::getLocale())->get();
                $countryServices = CountryServices::where('city_id', $city_id)->get();
                $arr_service_category = Category::translatedIn(\App::getLocale())->get();
                return view("admin::update-city", array("categories" => $arr_service_category, 'city' => $city, 'city_info' => $translated_city, 'city' => $city, 'states' => $states_info, 'countries' => $countries, "services" => $arr_service, "country_services" => $countryServices));
            } else {
                // validate and proceed
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                            'state' => 'required',
                            'country' => 'required',
                            'support_number' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
                    $translated_city->name = $request->name;
                    $city->state_id = $request->state;
                    $city->country_id = $request->country;
                    $city->support_number = $request->support_number;
                    $translated_city->save();
                    $city->save();

                    $country_id = $request->country;
                    CountryServices::where('city_id', $city_id)->delete();
                    if (isset($data['services']) && !empty($data['services'])) {

                        for ($i = 0; $i < count($data['services']); $i++) {
                            $arr_countryServices = array();
                            $arr_countryServices["service_id"] = $data['services'][$i];
                            if (isset($data['price_type_' . $data['services'][$i]])) {
                                $arr_countryServices["price_type"] = $data['price_type_' . $data['services'][$i]];
                            }
                            if (isset($data['base_price_' . $data['services'][$i]]) && !empty($data['base_price_' . $data['services'][$i]])) {
                                $arr_countryServices["base_price"] = $data['base_price_' . $data['services'][$i]];
                            }
                            if (isset($data['price_per_km_' . $data['services'][$i]]) && !empty($data['price_per_km_' . $data['services'][$i]])) {
                                $arr_countryServices["price_per_km"] = $data['price_per_km_' . $data['services'][$i]];
                            }
                            if (isset($data['price_per_min_' . $data['services'][$i]]) && !empty($data['price_per_min_' . $data['services'][$i]])) {
                                $arr_countryServices["price_per_min"] = $data['price_per_min_' . $data['services'][$i]];
                            }
                            if (isset($data['sort_index_' . $data['services'][$i]]) && !empty($data['sort_index_' . $data['services'][$i]])) {
                                $arr_countryServices["sort_index"] = $data['sort_index_' . $data['services'][$i]];
                            }
                            if (isset($data['sort_index_arabic_' . $data['services'][$i]]) && !empty($data['sort_index_arabic_' . $data['services'][$i]])) {
                                $arr_countryServices["sort_index_arabic"] = $data['sort_index_arabic_' . $data['services'][$i]];
                            }
                            if (isset($data['check_point_distance_' . $data['services'][$i]]) && !empty($data['check_point_distance_' . $data['services'][$i]])) {
                                $arr_countryServices["check_point_distance"] = $data['check_point_distance_' . $data['services'][$i]];
                            }
                            if (isset($data['flat_price_' . $data['services'][$i]]) && !empty($data['flat_price_' . $data['services'][$i]])) {
                                $arr_countryServices["flat_price"] = $data['flat_price_' . $data['services'][$i]];
                            }
                            if (isset($data['base_km_' . $data['services'][$i]]) && !empty($data['base_km_' . $data['services'][$i]])) {
                                $arr_countryServices["base_km"] = $data['base_km_' . $data['services'][$i]];
                            }
                            if (isset($data['night_percentage_' . $data['services'][$i]]) && !empty($data['night_percentage_' . $data['services'][$i]])) {
                                $arr_countryServices["night_percentage"] = $data['night_percentage_' . $data['services'][$i]];
                            }
                            if (isset($data['night_time_from_' . $data['services'][$i]]) && !empty($data['night_time_from_' . $data['services'][$i]])) {
                                $arr_countryServices["night_time_from"] = $data['night_time_from_' . $data['services'][$i]];
                            }
                            if (isset($data['night_time_to_' . $data['services'][$i]]) && !empty($data['night_time_to_' . $data['services'][$i]])) {
                                $arr_countryServices["night_time_to"] = $data['night_time_to_' . $data['services'][$i]];
                            }
                            $arr_countryServices["country_id"] = $country_id;
                            $arr_countryServices["city_id"] = $city_id;
                            CountryServices::create($arr_countryServices);
                        }
                    }
                    /* save country services */
                    return redirect("admin/cities/list")->with('update-city-status', 'City updated successfully!');
                }
            }
        } else {
            return redirect("admin/cities/list");
        }
    }

    public function updateCityLanguage(Request $request, $city_id, $locale) {
        $city = City::find($city_id);

        if ($city) {
            $is_new_entry = !($city->hasTranslation($locale));

            $translated_city = $city->translateOrNew($locale);

            if ($request->method() == "GET") {
                return view("admin::update-city-language", array('city_info' => $translated_city));
            } else {
                // validate and proceed
                $data = $request->all();

                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
                    $translated_city->name = $request->name;

                    if ($is_new_entry) {
                        $translated_city->city_id = $city_id;
                    }

                    $translated_city->save();
                    return redirect("admin/cities/list")->with('update-city-status', 'City updated successfully!');
                    //return redirect()->back()->with('update-city-status','City updated successfully!');
                }
            }
        } else {
            return redirect("admin/cities/list");
        }
    }

    public function deleteCity($city_id) {
        $city = City::find($city_id);

        if ($city) {
            $city->delete();
            return redirect('admin/cities/list')->with('city-status', 'City has been deleted successfully!');
        } else {
            return redirect('admin/cities/list');
        }
    }

    public function deleteCitySelected($city_id) {
        $city = City::find($city_id);
        if ($city) {
            $city->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    private function generateReferenceNumber() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    /* manage star users */

    public function deletSelectedStarUser($user_id) {
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function listStarUsers() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        $pending_amit = DriverPendingAmount::where('status', 0)->sum('amount');
        return view("admin::list-star-users", array("all_countries" => $all_countries, "total_pending_amount" => $pending_amit));
    }

    public function listStarUsersToPay() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        $pending_amit = DriverPendingAmount::where('status', 0)->sum('amount');
        return view("admin::list-star-users-to-pay", array("all_countries" => $all_countries, "total_pending_amount" => $pending_amit));
    }

    public function listAgentManagerUsers() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        return view("admin::list-agent-manager-users", array("all_countries" => $all_countries));
    }

    public function listStarUsersByAgent($agent_id) {
        $userData = User::find($agent_id);
        if (Auth::user()->userInformation->user_type == 6) {
            if ($userData->supervisor_id != Auth::user()->id) {
                return redirect("admin/agent-users");
                exit;
            }
        }

        return view("admin::list-star-users-agent", array("agent_id" => $agent_id));
    }

    public function listStarUsersByCompany($company_id) {
        $userData = User::find($company_id);
        if (Auth::user()->userInformation->user_type == 6) {
            if ($userData->supervisor_id != Auth::user()->id) {
                return redirect("admin/company-users");
                exit;
            }
        }
        return view("admin::list-star-users-company", array("agent_id" => $company_id));
    }

    public function listStarUsersDataByAgent($agent_id) {
        if ($agent_id != '') {
            $agent_data = UserAddress::where('user_id', $agent_id)->first();
            $agent_country = $agent_data->user_country;
            $agent_state = $agent_data->user_state;
            $agent_city = $agent_data->user_city;

            $all_users = UserInformation::where("user_type", 2)->get();

            $all_users = $all_users->reject(function ($user) use($agent_id) {

                return ($user->user->supervisor_id != $agent_id);
            });
            $star_users = $all_users->reject(function ($user) use($agent_country, $agent_state, $agent_city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($agent_country != '3') {
                    if ($agent_country != '17') {
                        $contry_passed = ($star_country != $agent_country);
                    }
                    if ($agent_state != '32') {
                        $state_passed = ($star_state != $agent_state);
                    }
                    return ($condition || ($contry_passed || $state_passed));
                } else {
                    $contry_passed = ($star_country != $agent_country);
                    if ($state != '5') {
                        return ($condition || ($contry_passed));
                    } else {
                        return ($condition || ($contry_passed || $state_passed));
                    }
                }
            });


            return Datatables::of($star_users)
                            ->addColumn('first_name', function($regsiter_user) {
                                return $regsiter_user->first_name;
                            })
                            ->addColumn('user_mobile', function($regsiter_user) {
                                return "+" . str_replace("+", "", $regsiter_user->mobile_code) . " " . $regsiter_user->user_mobile;
                            })
                            ->addColumn('last_name', function($regsiter_user) {
                                return $regsiter_user->last_name;
                            })
                            ->addColumn('email', function($admin_users) {
                                return $admin_users->user->email;
                            })
                            ->addColumn('location', function($admin_users) {
                                $location = '';

                                if ($admin_users->user->userAddress) {

                                    foreach ($admin_users->user->userAddress as $address) {
                                        $location .= $address->countryInfo->name;
                                        $location .= " /" . $address->stateInfo->name;
                                        $location .= " /" . $address->cityInfo->name;
                                    }
                                }
                                return $location;
                            })
                            ->addColumn('status', function($admin_users) {

                                $html = '';
                                if ($admin_users->user_status == 0) {
                                    $html = '<div  id="active_div' . $admin_users->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                    $html = $html . '<div id="inactive_div' . $admin_users->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Inactive </a> </div>';
                                    $html = $html . '<div id="blocked_div' . $admin_users->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Block </a> </div>';
                                } else if ($admin_users->user_status == 2) {
                                    $html = '<div  id="active_div' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                    $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Block</a> </div>';
                                } else {//                              
                                    $html = '<div  id="active_div' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                    $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Block</a> </div>';
                                }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                                return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                            })
                            ->addColumn('created_at', function($admin_users) {
                                return $admin_users->user->created_at;
                            })
                            ->make(true);
        }
    }

    public function listStarUsersData(Request $request) {
        \App::setLocale('en');
        $country_name = $request->country_name;
        $filter_by_week = $request->week_filter;
        $order_filter_by = $request->order_filter_by;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;

        $all_users = UserInformation::where("user_type", 2)->get();
        $all_users = $all_users->sortByDesc('id');
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                }
            }
            if ($country != 17) {

                $star_users = $all_users->reject(function ($user) use ($country) {
                    $star_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $star_country = $address->user_country;
                        }
                    }
                    return (($user->user->hasRole('superadmin') || ($user->user_type != 2) || ($star_country != $country)));
                });
            } else {
                $star_users = $all_users->reject(function ($user) use ($country) {
                    $star_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $star_country = $address->user_country;
                        }
                    }
                    return (($user->user->hasRole('superadmin') || ($user->user_type != 2)));
                });
            }
        } else if (Auth::user()->userInformation->user_type == '7' || Auth::user()->userInformation->user_type == '5') {

            $star_users = $all_users->reject(function ($user) {

                return ($user->user->supervisor_id != Auth::user()->id);
            });
        } else if (Auth::user()->userInformation->user_type == '1') {
            $star_users = $all_users->reject(function ($user) {
                if (isset($user->user)) {
                    return (($user->user->hasRole('superadmin') || ($user->user_type != 2)));
                }
            });
        } else if (Auth::user()->userInformation->user_type == '4') {

            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $star_users = $all_users->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '3') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    if ($city != '22') {
                        $city_passed = ($star_city != $city);
                    }
                    return ($condition || ($contry_passed || $state_passed || $city_passed));
                } else {
                    $contry_passed = ($star_country != $country);
                    if ($state != '5') {
                        return ($condition || ($contry_passed));
                    } else {
                        return ($condition || ($contry_passed || $state_passed));
                    }
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
        } else if (Auth::user()->userInformation->user_type == '6') {

            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $star_users = $all_users->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '3') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    if ($city != '22') {
                        $city_passed = ($star_city != $city);
                    }
                    return ($condition || ($contry_passed || $state_passed || $city_passed));
                } else {
                    $contry_passed = ($star_country != $country);
                    if ($state != '5') {
                        return ($condition || ($contry_passed));
                    } else {
                        return ($condition || ($contry_passed || $state_passed));
                    }
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
        }
        if ($filter_by_week != "") {
            $star_users = $star_users->filter(function($user) {
                if (isset($user->user)) {
                    $today_date = date("Y-m-d");
                    $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                    return date("Y-m-d", strtotime($user->user->created_at)) <= $today_date && date("Y-m-d", strtotime($user->user->created_at)) >= $seven_days_back;
                }
            });
        }
        if ($order_country_id != "") {
            if ($order_country_id != "17") {
                $star_users = $star_users->filter(function($user)use($order_country_id) {
                    // return $user->country_id == $order_country_id;

                    $star_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $star_country = $address->user_country;
                        }
                    }

                    return ($star_country == $order_country_id);
                });
            }
        }
        if ($order_start_date != "" && $order_end_date != "") {
            $star_users = $star_users->filter(function($user)use($order_start_date, $order_end_date) {
                return date("Y-m-d", strtotime($user->created_at)) >= $order_start_date && date("Y-m-d", strtotime($user->created_at)) <= $order_end_date;
            });
        }
        if ($order_filter_by != "") {

            $star_users = $star_users->filter(function($all_data)use($order_filter_by, $order_country_id) {
                return ($all_data->user_status == $order_filter_by);
            });
        }


        return Datatables::of($star_users)
                        ->addColumn('first_name', function($regsiter_user) {
                            $name = "";
                            $name = $regsiter_user->first_name;
                            $file_name = url('print-pdf/' . $regsiter_user->user_id);

//                              $name.="<br><a target='blank' href='".$file_name."'>Download Pdf</a>";
                            return $name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($admin_users) {
                            return $admin_users->user->email;
                        })
                        ->addColumn('username', function($admin_users) {
                            return $admin_users->user->username;
                        })
                        ->addColumn('user_mobile', function($regsiter_user) {
                            return "+" . str_replace("+", "", $regsiter_user->mobile_code) . " " . $regsiter_user->user_mobile;
                        })
                        ->addColumn('pending_amount', function($admin_users) {
                            $pending_amit = DriverPendingAmount::where(['user_id' => $admin_users->user_id, 'status' => 0])->sum('amount');
                            return $pending_amit;
                        })
                        ->addColumn('location', function($admin_users) {
//                   $location='';
//                   
//                   if(!empty($admin_users->user->userAddress)&&($admin_users->user->userAddress!=='null' && $admin_users->user->userAddress!=='[]'))
//                   {
//                      
//                    
//                       if(isset($admin_users->user->userAddress->country) && !is_null($admin_users->user->userAddress->country))
//                       {
//                           $userAddress=$admin_users->user->userAddress->country;
//                           $location=$userAddress->countryInfo;
//                       }
//                     
//                   }
//                   
//                  
//                    return $location;
//                })
                            $location = '';
                            if ($admin_users->user->userAddress) {
                                foreach ($admin_users->user->userAddress as $address)
                                    if (isset($address->countryinfo)) {

                                        if (isset($admin_users->nationality) && $admin_users->nationality != '0') {
                                            $nationality = Nationality::where('id', $admin_users->nationality)->first();
                                            if (isset($nationality->country_name)) {
                                                $location .= $nationality->country_name;
                                                $location .= " / ";
                                            }
                                        }

                                        if (isset($address->countryinfo->translate()->name)) {
                                            $location .= $address->countryinfo->translate()->name;
                                        }

                                        if (isset($address->stateInfo)) {
                                            $location .= " /" . $address->stateInfo->translate()->name;
                                        }
                                        if (isset($address->cityInfo)) {
                                            $location .= " /" . $address->cityInfo->translate()->name;
                                        }
                                    }
                            }

                            return $location;
                        })
                        ->addColumn('status', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 0) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="inactive_div' . $admin_users->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Inactive </a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked </a> </div>';
                            } else if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            } else {//                              
                                $html = '<div  id="active_div' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('blocked', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }if ($admin_users->user_status == 1) {//                              
                                $html = '<div  id="active_div_block' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 2);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Block</a> </div>';
                                $html = $html . '<div id="blocked_div_block' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-success" title="Click to Change Status" onClick="javascript:changeStatus(' . $admin_users->user->id . ', 1);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Click to Activate</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;

//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('available', function($admin_users) {
                            return (isset($admin_users->user->driverUserInformation->availability) && ($admin_users->user->driverUserInformation->availability == 1) ? 'Yes' : 'No');
                        })
                        ->addColumn('having_active_order', function($admin_users) {
                            $current_order = "No";
                            $orderCount = Order::where('driver_id', $admin_users->user_id)->where('status', '1')->first();
                            if (count($orderCount) > 0) {
                                $current_order = "Yes";
                            }
                            return $current_order;
                        })
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                        ->addColumn('device', function($admin_users) {
                            return ((isset($admin_users->device_type) && ($admin_users->device_type == '0')) ? 'Android' : 'IOS');
                        })
                        ->addColumn('rating', function($regsiter_user) {
                            //finding avg rating
                            $userRatingInfo = UserRatingInformation::where('to_id', $regsiter_user->user_id)->where('status', '1')->get();
                            $avg_rating = ($userRatingInfo->avg('rating')) ? $userRatingInfo->avg('rating') : '0';
                            return round($avg_rating);
                        })
                        ->make(true);
    }

    public function listStarUsersDataToPay(Request $request) {
        \App::setLocale('en');
        $country_name = $request->country_name;
        $filter_by_week = $request->week_filter;
        $order_filter_by = $request->order_filter_by;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;

        $all_users = UserInformation::where("user_type", 2)->get();
        $all_users = $all_users->sortByDesc('id');
        $amount_limit = isset(Auth::user()->userInformation->limit_to_pay_to_star) ? Auth::user()->userInformation->limit_to_pay_to_star : '0';
        $all_users = $all_users->reject(function($user) use ($amount_limit) {
            //get user wallet amount
            $mate_wallet_data = UserWalletDetail::where('user_id', $user->user_id)->orderBy('id', 'desc')->first(['final_amout']);
            $final_amount = isset($mate_wallet_data->final_amout) ? $mate_wallet_data->final_amout : '0';
            if ($final_amount < $amount_limit) {
                return $user;
            }
        });
        if (Auth::user()->userInformation->user_type == '4') {

            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {
                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $star_users = $all_users->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {
                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '3') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    if ($city != '22') {
                        $city_passed = ($star_city != $city);
                    }

                    return ($condition || ($contry_passed || $state_passed || $city_passed));
                } else {
                    $contry_passed = ($star_country != $country);
                    if ($state != '5') {
                        return ($condition || ($contry_passed));
                    } else {
                        return ($condition || ($contry_passed || $state_passed));
                    }
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
        } else if (Auth::user()->userInformation->user_type == '6') {

            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $star_users = $all_users->reject(function ($user) use($country, $state, $city) {
                $star_country = 0;
                $star_state = 0;
                $star_city = 0;
                if ($user->user->userAddress) {

                    foreach ($user->user->userAddress as $address) {
                        $star_country = $address->user_country;
                        $star_state = $address->user_state;
                        $star_city = $address->user_city;
                    }
                }

                $condition = ($user->user->hasRole('superadmin') || ($user->user_type != 2));
                $contry_passed = false;
                $state_passed = false;
                $city_passed = false;
                if ($country != '3') {
                    if ($country != '17') {
                        $contry_passed = ($star_country != $country);
                    }
                    if ($state != '32') {
                        $state_passed = ($star_state != $state);
                    }
                    return ($condition || ($contry_passed || $state_passed));
                } else {
                    $contry_passed = ($star_country != $country);
                    if ($state != '5') {
                        return ($condition || ($contry_passed));
                    } else {
                        return ($condition || ($contry_passed || $state_passed));
                    }
                }
//                if ($city != '22') {
//                    $city_passed = ($star_city != $city);
//                }
            });
        } else {
            $star_users = $all_users->reject(function($user) {
                return $user;
            });
        }

//        if ($filter_by_week != "") {
//            $star_users = $star_users->filter(function($user) {
//                $today_date = date("Y-m-d");
//                $seven_days_back = date('Y-m-d', strtotime('-7 days'));
//                return date("Y-m-d", strtotime($user->user->created_at)) <= $today_date && date("Y-m-d", strtotime($user->user->created_at)) >= $seven_days_back;
//            });
//        }
//        if ($order_country_id != "") {
//                if ($order_country_id != "17") {
//                    $star_users = $star_users->filter(function($user)use($order_country_id) {
//                       // return $user->country_id == $order_country_id;
//                 
//                    $star_country = 0;
//                    if ($user->user->userAddress) {
//
//                        foreach ($user->user->userAddress as $address) {
//                            $star_country = $address->user_country;
//                        }
//                    }
//                   
//                    return ($star_country==$order_country_id);
//                });
//            }
//        }
//        if ($order_start_date != "" && $order_end_date != "") {
//               $star_users = $star_users->filter(function($user)use($order_start_date, $order_end_date) {
//                   return date("Y-m-d", strtotime($user->created_at)) >= $order_start_date && date("Y-m-d", strtotime($user->created_at)) <= $order_end_date;
//               });
//        }
//        if ($order_filter_by != "") {
//
//                $star_users = $star_users->filter(function($all_data)use($order_filter_by, $order_country_id) {
//                    if ($order_filter_by == "today") {
//                        $filter_data = date("Y-m-d");
//                        return date("Y-m-d", strtotime($all_data->created_at)) == $filter_data;
//                    } else if ($order_filter_by == "week") {
//                        $today_date = date("Y-m-d");
//                        $seven_days_back = date('Y-m-d', strtotime('-7 days'));
//                        return date("Y-m-d", strtotime($all_data->created_at)) <= $today_date && date("Y-m-d", strtotime($all_data->created_at)) >= $seven_days_back;
//                    } else if ($order_filter_by == "month") {
//                        $d = new DateTime('first day of this month');
//                        $month_start_date = $d->format('Y-m-d');
//                        $current_date = date("Y-m-d");
//                        return date("Y-m-d", strtotime($all_data->created_at)) <= $current_date && date("Y-m-d", strtotime($all_data->created_at)) >= $month_start_date;
//                    } else if ($order_filter_by == "year") {
//                        $current_year = date("o");
//                        return date("o", strtotime($all_data->created_at)) == $current_year;
//                    }
//                });
//            }
//            

        return Datatables::of($star_users)
                        ->addColumn('first_name', function($regsiter_user) {

                            return $name = $regsiter_user->first_name;
                        })
                        ->addColumn('last_name', function($regsiter_user) {
                            return $regsiter_user->last_name;
                        })
                        ->addColumn('email', function($admin_users) {
                            return $admin_users->user->email;
                        })
                        ->addColumn('username', function($admin_users) {
                            return $admin_users->user->username;
                        })
                        ->addColumn('user_mobile', function($regsiter_user) {
                            return "+" . str_replace("+", "", $regsiter_user->mobile_code) . " " . $regsiter_user->user_mobile;
                        })
                        ->addColumn('location', function($admin_users) {
//                   $location='';
//                   
//                   if(!empty($admin_users->user->userAddress)&&($admin_users->user->userAddress!=='null' && $admin_users->user->userAddress!=='[]'))
//                   {
//                      
//                    
//                       if(isset($admin_users->user->userAddress->country) && !is_null($admin_users->user->userAddress->country))
//                       {
//                           $userAddress=$admin_users->user->userAddress->country;
//                           $location=$userAddress->countryInfo;
//                       }
//                     
//                   }
//                   
//                  
//                    return $location;
//                })
                            $location = '';
                            if ($admin_users->user->userAddress) {
                                foreach ($admin_users->user->userAddress as $address)
                                    if (isset($address->countryinfo)) {

                                        if (isset($admin_users->nationality) && $admin_users->nationality != '0') {
                                            $nationality = Nationality::where('id', $admin_users->nationality)->first();
                                            if (isset($nationality->country_name)) {
                                                $location .= $nationality->country_name;
                                                $location .= " / ";
                                            }
                                        }

                                        if (isset($address->countryinfo->translate()->name)) {
                                            $location .= $address->countryinfo->translate()->name;
                                        }

                                        if (isset($address->stateInfo)) {
                                            $location .= " /" . $address->stateInfo->translate()->name;
                                        }
                                        if (isset($address->cityInfo)) {
                                            $location .= " /" . $address->cityInfo->translate()->name;
                                        }
                                    }
                            }

                            return $location;
                        })
                        ->addColumn('status', function($admin_users) {

                            $html = '';
                            if ($admin_users->user_status == 0) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"    style="display:none;"  >
                                                <a class="label label-success" title="Click to Change changeStarUserStatus"  href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="inactive_div' . $admin_users->user->id . '"  style="display:inline-block" >
                                                <a class="label label-warning" title="Click to Change Status" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Inactive </a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '" style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status);" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked </a> </div>';
                            } else if ($admin_users->user_status == 2) {
                                $html = '<div  id="active_div' . $admin_users->user->id . '"  style="display:none;" >
                                                <a class="label label-success" title="Click to Change Status" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"    style="display:inline-block" >
                                                <a class="label label-danger" title="Click to Change Status" href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            } else {//                              
                                $html = '<div  id="active_div' . $admin_users->user->id . '"   style="display:inline-block" >
                                                <a class="label label-success" title="Click to Change Status"  href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Active</a> </div>';
                                $html = $html . '<div id="blocked_div' . $admin_users->user->id . '"  style="display:none;"  >
                                                <a class="label label-danger" title="Click to Change Status"  href="javascript:void(0);" id="status_' . $admin_users->user->id . '">Blocked</a> </div>';
                            }
////                            return ($regsiter_user->user_status > 0) ? 'Active' : 'Inactive';
                            return $html;



//                     return ($admin_users ->user_status>0)? 'Active': 'Inactive';
                        })
                        ->addColumn('wallet_amount', function($admin_users) {
                            $mate_wallet_data = UserWalletDetail::where('user_id', $admin_users->user_id)->orderBy('id', 'desc')->first(['final_amout']);
                            $final_amount = isset($mate_wallet_data->final_amout) ? $mate_wallet_data->final_amout : '0.00';
                            if ($final_amount == '0') {
                                $final_amount = 0.00;
                            }
                            return $final_amount;
//                
                        })
                        ->addColumn('having_active_order', function($admin_users) {
                            $current_order = "No";
                            $orderCount = Order::where('driver_id', $admin_users->user_id)->where('status', '1')->first();
                            if (count($orderCount) > 0) {
                                $current_order = "Yes";
                            }
                            return $current_order;
                        })
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                        ->addColumn('device', function($admin_users) {
                            return ((isset($admin_users->device_type) && ($admin_users->device_type == '0')) ? 'Android' : 'IOS');
                        })
                        ->addColumn('rating', function($regsiter_user) {
                            //finding avg rating
                            $userRatingInfo = UserRatingInformation::where('to_id', $regsiter_user->user_id)->where('status', '1')->get();
                            $avg_rating = ($userRatingInfo->avg('rating')) ? $userRatingInfo->avg('rating') : '0';
                            return round($avg_rating);
                        })
                        ->make(true);
    }

    public function createStarUser(Request $request, $is_admin = false) {
        if ($request->method() == "GET") {
            $country = 0;
            $state = 0;
            $city = 0;
            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                    $state = $address->user_state;
                    $city = $address->user_city;
                }
            }
            $states = array();
            $cities = array();
            if ($country != '17') {
                $states = State::where('country_id', $country)->translatedIn(\App::getLocale())->get();

                if ($state != '32') {
                    $cities = City::where('state_id', $state)->translatedIn(\App::getLocale())->get();
                }
            }

            $agent_users = User::where('supervisor_id', Auth::user()->id)->get();
            $agent_users = $agent_users->reject(function($user) {
                return ($user->userInformation->user_type != 4 || $user->userInformation->user_status != 1);
            });
            $allCompanies = array();
            if (Auth::user()->userInformation->user_type == '4') {
                $compnies = UserInformation::where('user_type', '5')->get();
                $allCompanies = $compnies->reject(function($company) {
                    return ($company->user->supervisor_id != Auth::user()->id);
                });
            }
            if (Auth::user()->userInformation->user_type == '6') {

                $compnies = UserInformation::where('user_type', '5')->get();
                $allAgents = array();
                $agents = User::where('supervisor_id', Auth::user()->id)->get();
                if (count($compnies) > 0) {
                    foreach ($agents as $agent) {
                        $allAgents[] = $agent->id;
                    }
                    $allAgents[] = Auth::user()->id;
                }
                $allCompanies = $compnies->reject(function($company) use($allAgents) {
                    return ((!in_array($company->user->supervisor_id, $allAgents)));
                });
            }
            if (Auth::user()->userInformation->user_type == '5') {

                $allCompanies = UserInformation::where('user_id', Auth::user()->id)->get();
            }
            if (Auth::user()->userInformation->user_type == '1') {
                $country = 0;
                $state = 0;
                $city = 0;
                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                        $state = $address->user_state;
                        $city = $address->user_city;
                    }
                }
                $compnies = UserInformation::where('user_type', '5')->get();
                $allCompanies = $compnies->reject(function($user) use($country, $state, $city) {
                    $star_country = 0;
                    $star_state = 0;
                    $star_city = 0;
                    if ($user->user->userAddress) {
                        foreach ($user->user->userAddress as $address) {
                            $star_country = $address->user_country;
                            $star_state = $address->user_state;
                            $star_city = $address->user_city;
                        }
                    }


                    $condition = false;
                    $contry_passed = false;
                    $state_passed = false;
                    $city_passed = false;
                    if ($country != '3') {
                        if ($country != '17' && $country != 0) {
                            $contry_passed = ($star_country != $country);
                        }
                        if ($state != '32' && $state != 0) {
                            $state_passed = ($star_state != $state);
                        }
                        if ($city != '22' && $city != 0) {
                            $city_passed = ($star_city != $city);
                        }
                        return ($condition || ($contry_passed || $state_passed || $city_passed));
                    } else {
                        $contry_passed = ($star_country != $country);
                        if ($state != '5' && $state != 0) {
                            return ($condition || ($contry_passed));
                        } else {
                            return ($condition || ($contry_passed || $state_passed));
                        }
                    }
                });
            }

            $all_countries = Country::translatedIn(\App::getLocale())->get();
            return view("admin::create-star-user", array("companies" => $allCompanies, "agent_users" => $agent_users, "city" => $city, "state" => $state, "cities" => $cities, "states" => $states, "country" => $country, "countries" => $all_countries));
        } elseif ($request->method() == "POST") {
            $data = $request->all();
            if (Auth::user()->userInformation->user_type != 4) {
                if ($data["type"] == '1') {
                    $validate_response = Validator::make($data, array(
                                'email' => 'email|max:255|unique:users,email',
//                            'gender' => 'required',
                                'first_name' => 'required',
                                'owner_name' => 'required',
//                            'owner_number' => 'required',
                                'last_name' => 'required',
                                'country' => 'required',
                                'state' => 'required',
                                'city' => 'required',
//                            'locale' => 'required',
                                'mobile_code' => 'required',
//                            'user_mobile' => 'required|unique:users,username'
                                'user_mobile' => 'required'
                                    ), array(
                                'state.required' => 'Region is required',
                                    )
                    );
                } else {
                    $validate_response = Validator::make($data, array(
                                'email' => 'email|max:255|unique:users,email',
                                'gender' => 'required',
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'country' => 'required',
                                'state' => 'required',
                                'city' => 'required',
//                            'locale' => 'required',
                                'mobile_code' => 'required',
                                'user_mobile' => 'required'
                                    ), array(
                                'state.required' => 'Region is required',
                                    )
                    );
                }
            } else {

                if ($data["type"] == '1') {
                    $validate_response = Validator::make($data, array(
                                'email' => 'email|max:255|unique:users,email',
                                'owner_name' => 'required',
//                            'comp_reg_no' => 'required',
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'locale' => 'required',
                                'user_mobile' => 'required'
                                    ), array(
                                'state.required' => 'Region is required',
                                    )
                    );
                } else {
                    $validate_response = Validator::make($data, array(
                                'email' => 'email|max:255|unique:users,email',
                                'gender' => 'required',
                                'first_name' => 'required',
                                'last_name' => 'required',
                                'locale' => 'required',
                                'user_mobile' => 'required'
                                    ), array(
                                'state.required' => 'Region is required',
                                    )
                    );
                }
            }
            if ($validate_response->fails()) {
                return redirect()->back()
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                $mobile_code = 0;
                if (isset($data["mobile_code"]) ? $data["mobile_code"] : "") {
                    $mobile_code = $data["mobile_code"];
                } else {
                    $mobile_code = isset(Auth::user()->userInformation->mobile_code) ? Auth::user()->userInformation->mobile_code : '0';
                }
                $userCheck = UserInformation::where('user_mobile', ltrim($data['user_mobile'], 0))->get();
                $userCheck = $userCheck->reject(function($user_data) use($mobile_code) {
                    $flag = 0;
                    if (str_replace("+", "", $user_data->mobile_code) != str_replace("+", "", $mobile_code)) {
                        $flag = 1;
                    }
                    return $flag;
                });

                if (count($userCheck) > 0) {
                    return redirect('admin/star-users')
                                    ->with("mobile_exist", "Mobile number is already exist.");
                } else {
                    $agent_id = 0;
                    $company_id = 0;
                    if (isset($data["agent"])) {
                        $agent_id = $data["agent"];
                    }
                    if (isset($data["comp_name"])) {
                        $company_id = $data["comp_name"];
                    }
                    if (Auth::user()->userInformation->user_type == '4') {
                        $agent_id = Auth::user()->id;
                    }
                    if (Auth::user()->userInformation->user_type == '5') {
                        $userCheckInfo = UserInformation::where('user_type', '4')->where('user_id', Auth::user()->supervisor_id)->first();
                        if (count($userCheckInfo) > 0) {
                            $agent_id = Auth::user()->supervisor_id;
                        }
                    }

//                $auto_gen_pass = rand();
                    $created_user = User::create(array(
                                'email' => $data['email'],
//                            'password' => ($auto_gen_pass),
                                'username' => ltrim($data['user_mobile'], 0),
                                'supervisor_id' => Auth::user()->id,
                                'agent_id_val' => $agent_id,
                                'company_id' => $company_id,
                    ));
                    $user_code = $data['user_mobile'] . '-' . $created_user->id;

                    // update User Information

                    /*
                     * Adjusted user specific columns, which may not passed on front end and adjusted with the default values
                     */
                    $data["user_type"] = isset($data["user_type"]) ? $data["user_type"] : "1";    // 1 may have several mean as per enum stored in the database. Here we 

                    $data["user_status"] = isset($data["user_status"]) ? $data["user_status"] : "0";  // 0 means not active

                    $data["gender"] = isset($data["gender"]) ? $data["gender"] : "3";       // 3 means not specified
                    $data["agent_id_val"] = isset($data["agent"]) ? $data["agent"] : "3";       // 3 means not specified

                    $data["profile_picture"] = isset($data["profile_picture"]) ? $data["profile_picture"] : "";
                    $data["facebook_id"] = isset($data["facebook_id"]) ? $data["facebook_id"] : "";
                    $data["twitter_id"] = isset($data["twitter_id"]) ? $data["twitter_id"] : "";
                    $data["google_id"] = isset($data["google_id"]) ? $data["google_id"] : "";
                    $data["user_birth_date"] = isset($data["user_birth_date"]) ? $data["user_birth_date"] : "";
                    $data["first_name"] = isset($data["first_name"]) ? $data["first_name"] : "";
                    $data["last_name"] = isset($data["last_name"]) ? $data["last_name"] : "";
                    $data["about_me"] = isset($data["about_me"]) ? $data["about_me"] : "";
                    $data["user_mobile"] = isset($data["user_mobile"]) ? $data["user_mobile"] : "";
                    $data["mobile_code"] = isset($data["mobile_code"]) ? $data["mobile_code"] : "";
                    $data["owner_number"] = isset($data["owner_number"]) ? $data["owner_number"] : "";
                    $data["owner_name"] = isset($data["owner_name"]) ? $data["owner_name"] : "";
                    $arr_userinformation = array();
                    $arr_userinformation["profile_picture"] = $data["profile_picture"];
                    $arr_userinformation["owner_number"] = $data["owner_number"];
                    $arr_userinformation["owner_name"] = $data["owner_name"];
                    $arr_userinformation["gender"] = $data["gender"];
                    $arr_userinformation["activation_code"] = "";             // By default it'll be no activation code
                    $arr_userinformation["facebook_id"] = $data["facebook_id"];
                    $arr_userinformation["twitter_id"] = $data["twitter_id"];
                    $arr_userinformation["google_id"] = $data["google_id"];
                    $arr_userinformation["user_birth_date"] = $data["user_birth_date"];
                    $arr_userinformation["first_name"] = $data["first_name"];
                    $arr_userinformation["last_name"] = $data["last_name"];
                    $arr_userinformation["about_me"] = $data["about_me"];
                    $arr_userinformation["user_mobile"] = ltrim($data["user_mobile"], 0);
                    $arr_userinformation["user_status"] = $data["user_status"];
                    $arr_userinformation["user_type"] = 2;
                    $arr_userinformation["is_company"] = $data["type"];
                    $arr_userinformation["user_id"] = $created_user->id;
                    $arr_userinformation["user_code"] = $user_code;


                    //addding user country state city 
                    if (Auth::user()->userInformation->user_type == 1) {
                        $arr_userAddress["user_country"] = isset($data["country"]) ? $data["country"] : "NULL";
                        $arr_userAddress["user_state"] = isset($data["state"]) ? $data["state"] : "NULL";
                        $arr_userAddress["user_city"] = isset($data["city"]) ? $data["city"] : "NULL";
                        $arr_userinformation["mobile_code"] = isset($data["mobile_code"]) ? (str_replace("+", "", $data["mobile_code"])) : '';
                    } else {
                        if (Auth::user()->userAddress) {
                            $country = 0;
                            $state = 0;
                            $city = 0;
                            foreach (Auth::user()->userAddress as $address) {
                                $country = $address->user_country;
                                $state = $address->user_state;
                                $city = $address->user_city;
                            }
                        }
                        $arr_userAddress["user_country"] = $country;
                        $arr_userAddress["user_state"] = $state;
                        $arr_userAddress["user_city"] = $city;
                        $arr_userinformation["mobile_code"] = isset(Auth::user()->userInformation->mobile_code) ? Auth::user()->userInformation->mobile_code : '0';
                    }

                    $updated_user_info = UserInformation::create($arr_userinformation);
                    $arr_userAddress["user_id"] = $created_user->id;
                    UserAddress::create($arr_userAddress);

                    $userRole = Role::where("slug", "registered.user")->first();
                    $created_user->attachRole($userRole);
                    $created_user->save();

//                 // addding user company details 
//                if (isset($data["type"]) && $data["type"] == '1') {
//                    $arr_CompanyDetails["name"] = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
//                    $arr_CompanyDetails["description"] = isset($data["description"]) ? $data["description"] : "NULL";
//                    $arr_CompanyDetails["comp_reg_no"] = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
//                    $arr_CompanyDetails["user_id"] = $created_user->id;
//                    CompanyInformation::create($arr_CompanyDetails);
//                }
                    $arr_star_userinformation = array();
                    $arr_star_userinformation['user_id'] = $created_user->id;
                    $updated_star_user_info = DriverUserInformation::create($arr_star_userinformation);


                    $updated_star_user_info->save();
                    if ($data['email'] != '') {
                        $site_email = GlobalValues::get('site-email');
                        $site_title = GlobalValues::get('site-title');
                        $arr_keyword_values = array();
                        $activation_code = $this->generateReferenceNumber();
                        //Assign values to all macros
                        $arr_keyword_values['FIRST_NAME'] = $updated_user_info->first_name;
                        $arr_keyword_values['LAST_NAME'] = $updated_user_info->last_name;
//                    $arr_keyword_values['PASSWORD'] = $auto_gen_pass;
                        $arr_keyword_values['USER_NAME'] = $data['email'];
                        $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                        $arr_keyword_values['SITE_TITLE'] = $site_title;
                        // updating activation code                 
                        $updated_user_info->activation_code = $activation_code;
                        $updated_user_info->save();

                        //
                        $locale = isset($data['locale']) ? $data['locale'] : 'en';
                        $email_title = "emailtemplate::star-registration-successfull-" . $locale;
                        Mail::send($email_title, $arr_keyword_values, function ($message) use ($created_user, $site_email, $site_title) {

                            $message->to($created_user->email, $created_user->name)->subject("Registration Successful!")->from($site_email, $site_title);
                        });
                    }
//                                        $msg = "Driver user user has been created successfully";
//                                        Twilio::message($data["user_mobile"], $msg);

                    return redirect('admin/star-users')
                                    ->with("update-user-status", "Driver user user has been created successfully");
                }
            }
        }
    }

    public function updateStarUser(Request $request, $user_id) {
        \App::setLocale('en');
        $arr_user_data = User::find($user_id);
        if ($arr_user_data) {
            if ($request->method() == "GET") {
                $country = 0;
                $state = 0;
                $city = 0;
                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                        $state = $address->user_state;
                        $city = $address->user_city;
                    }
                }
                $all_countries = Country::translatedIn(\App::getLocale())->get();
                $states = "";
                $cities = "";
                $user_country = 0;
                $user_state = 0;
                $user_state = 0;
                $user_address = "";
                if (isset($arr_user_data->userAddress)) {
                    foreach ($arr_user_data->userAddress as $address) {
                        $user_country = $address->user_country;
                        $user_state = $address->user_state;
                        $user_city = $address->user_city;
                        $user_address = $address->address;
                    }
                }
                if ($country == '10' && ($user_country != $country)) {
                    return redirect('admin/star-users');
                    exit;
                }
                $states = State::where('country_id', $user_country)->translatedIn(\App::getLocale())->get();
                $cities = City::where('state_id', $user_state)->where('country_id', $user_country)->translatedIn(\App::getLocale())->get();

                $arr_service_category = Category::translatedIn(\App::getLocale())->get();

                $arr_service = Service::translatedIn(\App::getLocale())->get();

                $userServices = UserServiceInformation::where('user_id', $user_id)->get();
                $userLanguages = UserSpokenlanguageinformation::where('user_id', $user_id)->get();
                $paymentMethods = PaymentMethod::all();
                $userPaymentMethods = UserPaymentMethod::where('user_id', $user_id)->get();
                $all_Spokenlangusge = SpokenLanguage::translatedIn(\App::getLocale())->get();
                $all_company_info = CompanyInformation::where('user_id', $user_id)->first();
                $nationality = Nationality::all();
                $allCompanies = array();
                if (Auth::user()->userInformation->user_type == '4') {
                    $compnies = UserInformation::where('user_type', '5')->get();
                    $allCompanies = $compnies->reject(function($company) {
                        return ($company->user->supervisor_id != Auth::user()->id);
                    });
                }
                if (Auth::user()->userInformation->user_type == '6') {

                    $compnies = UserInformation::where('user_type', '5')->get();
                    $allAgents = array();
                    $agents = User::where('supervisor_id', Auth::user()->id)->get();
                    if (count($compnies) > 0) {
                        foreach ($agents as $agent) {
                            $allAgents[] = $agent->id;
                        }
                        $allAgents[] = Auth::user()->id;
                    }
                    $allCompanies = $compnies->reject(function($company) use($allAgents) {
                        return ((!in_array($company->user->supervisor_id, $allAgents)));
                    });
                }
                if (Auth::user()->userInformation->user_type == '5') {

                    $allCompanies = UserInformation::where('user_id', Auth::user()->id)->get();
                }
                if (Auth::user()->userInformation->user_type == '1') {
                    $country = 0;
                    $state = 0;
                    $city = 0;
                    if (Auth::user()->userAddress) {

                        foreach (Auth::user()->userAddress as $address) {
                            $country = $address->user_country;
                            $state = $address->user_state;
                            $city = $address->user_city;
                        }
                    }
                    $compnies = UserInformation::where('user_type', '5')->get();
                    $allCompanies = $compnies->reject(function($user) use($country, $state, $city) {
                        $star_country = 0;
                        $star_state = 0;
                        $star_city = 0;
                        if ($user->user->userAddress) {
                            foreach ($user->user->userAddress as $address) {
                                $star_country = $address->user_country;
                                $star_state = $address->user_state;
                                $star_city = $address->user_city;
                            }
                        }


                        $condition = false;
                        $contry_passed = false;
                        $state_passed = false;
                        $city_passed = false;
                        if ($country != '3') {
                            if ($country != '17' && $country != 0) {
                                $contry_passed = ($star_country != $country);
                            }
                            if ($state != '32' && $state != 0) {
                                $state_passed = ($star_state != $state);
                            }
                            if ($city != '22' && $city != 0) {
                                $city_passed = ($star_city != $city);
                            }
                            return ($condition || ($contry_passed || $state_passed || $city_passed));
                        } else {
                            $contry_passed = ($star_country != $country);
                            if ($state != '5' && $state != 0) {
                                return ($condition || ($contry_passed));
                            } else {
                                return ($condition || ($contry_passed || $state_passed));
                            }
                        }
                    });
                }

                $user_vehicles = UserVehicleInformation::where('user_id', Auth::user()->id)->get();
                $user_vehicles = $user_vehicles->reject(function($vehicle_info) {
                    $is_assigned = DriverAssignedDetail::where('vehicle_id', $vehicle_info->id)->first();
                    if (count($is_assigned) > 0) {
                        return true;
                    }
                });
                //get  star assigned vehicle details
                $driverVehicle = DriverAssignedDetail::where('user_id', $user_id)->first();
                $driverOtherInfo = DriverUserInformation::where('user_id', $user_id)->first();
                $driverDocuments = DriverDocument::where('user_id', $user_id)->get();
                return view("admin::edit-star-user", array("driverDocuments" => $driverDocuments, "driverOtherInfo" => $driverOtherInfo, "companies" => $allCompanies, 'company_info' => $all_company_info, 'nationality' => $nationality, 'max_range' => '0', 'user_payment_methods' => $userPaymentMethods, 'payment_methods' => $paymentMethods, 'user_info' => $arr_user_data, "countries" => $all_countries, "user_state" => $user_state, "user_country" => $user_country, "user_city" => $user_city, "address" => $user_address, "cities" => $cities, "states" => $states, "categories" => $arr_service_category, "services" => $arr_service, "user_services" => $userServices, "languages" => $all_Spokenlangusge, "user_languages" => $userLanguages, "user_vehicles" => $user_vehicles, "driverVehicle" => $driverVehicle));

//                                  return view("admin::edit-star-user",array('user_info'=>$arr_user_data,"countries"=>$all_countries,"states"=>$states,"cities"=>$cities));
//				return view("admin::edit-star-user",array('user_info'=>$arr_user_data));
            } elseif ($request->method() == "POST") {
                $data = $request->all();
//                if (Auth::user()->userInformation->user_type != 4) {
//                    $validate_response = Validator::make($data, array(
//                                'gender' => 'required',
//                                'first_name' => 'required',
//                                'last_name' => 'required',
//                                'user_mobile' => 'numeric',
//                                'country' => 'required|numeric',
//                                'state' => 'required|numeric',
//                                'working_time' => 'required|numeric',
//                                'nationality' => 'required|numeric',
//                                
//                                'city' => 'required|numeric'
//                                    )
//                    );
//                } else {
//                    $validate_response = Validator::make($data, array(
//                                'gender' => 'required',
//                                'first_name' => 'required',
//                                'last_name' => 'required',
//                                'user_mobile' => 'numeric',
//                                'working_time' => 'required|numeric',
//                                    )
//                    );
//                }
                if (Auth::user()->userInformation->user_type != 4) {
                    if ($data["type"] == '1') {
                        $validate_response = Validator::make($data, array(
                                    'email' => 'email|max:255|unique:users,email',
//                            'gender' => 'required',
                                    'first_name' => 'required',
//                            'comp_name' => 'required',
//                            'comp_reg_no' => 'required',
                                    'last_name' => 'required',
                                    'country' => 'required',
                                    'state' => 'required',
                                    'city' => 'required',
//                            'locale' => 'required',
//                            'mobile_code' => 'required',
                                    'user_mobile' => 'required|numeric'
                                        ), array(
                                    'state.required' => 'Region is required',
                                        )
                        );
                    } else {
                        $validate_response = Validator::make($data, array(
                                    'email' => 'email|max:255|unique:users,email',
                                    'gender' => 'required',
                                    'first_name' => 'required',
                                    'last_name' => 'required',
                                    'country' => 'required',
                                    'state' => 'required',
                                    'city' => 'required',
//                            'locale' => 'required',
//                            'mobile_code' => 'required',
                                    'user_mobile' => 'required|numeric'
                                        ), array(
                                    'state.required' => 'Region is required',
                                        )
                        );
                    }
                } else {

                    if ($data["type"] == '1') {
                        $validate_response = Validator::make($data, array(
                                    'email' => 'email|max:255|unique:users,email',
//                            'comp_name' => 'required',
//                            'comp_reg_no' => 'required',
                                    'first_name' => 'required',
                                    'last_name' => 'required',
//                            'locale' => 'required',
                                    'user_mobile' => 'required|numeric'
                                        ), array(
                                    'state.required' => 'Region is required',
                                        )
                        );
                    } else {
                        $validate_response = Validator::make($data, array(
                                    'email' => 'email|max:255|unique:users,email',
                                    'gender' => 'required',
                                    'first_name' => 'required',
                                    'last_name' => 'required',
//                            'locale' => 'required',
                                    'user_mobile' => 'required|numeric'
                                        ), array(
                                    'state.required' => 'Region is required',
                                        )
                        );
                    }
                }
                if ($validate_response->fails()) {

                    return redirect('admin/update-star-user/' . $arr_user_data->id)
                                    ->withErrors($validate_response)
                                    ->withInput();
                } else {/** user information goes here *** */
                    if (isset($data["profile_picture"])) {
                        $arr_user_data->userInformation->profile_picture = $data["profile_picture"];
                    }
                    if (isset($data["gender"])) {
                        $arr_user_data->userInformation->gender = $data["gender"];
                    }
                    if (isset($data["user_status"])) {
                        $arr_user_data->userInformation->user_status = $data["user_status"];
                    }

                    if (isset($data["first_name"])) {
                        $arr_user_data->userInformation->first_name = $data["first_name"];
                    }
                    if (isset($data["working_time"])) {
                        $arr_user_data->userInformation->working_time = $data["working_time"];
                    }
                    if (isset($data["last_name"])) {
                        $arr_user_data->userInformation->last_name = $data["last_name"];
                    }
                    if (isset($data["about_me"])) {
                        $arr_user_data->userInformation->about_me = $data["about_me"];
                    }
                    if (isset($data["nationality"])) {
                        $arr_user_data->userInformation->nationality = $data["nationality"];
                    }

                    if (isset($data["user_mobile"])) {
                        $arr_user_data->userInformation->user_mobile = $data["user_mobile"];
                    }

                    if (isset($data["date_of_birth"])) {
                        $arr_user_data->userInformation->user_birth_date = $data["date_of_birth"];
                    }

                    if (isset($data["type"])) {
                        $arr_user_data->userInformation->is_company = $data["type"];
                    }
                    if (isset($data["owner_name"])) {
                        $arr_user_data->userInformation->owner_name = $data["owner_name"];
                    }
                    if (isset($data["owner_number"])) {
                        $arr_user_data->userInformation->owner_number = $data["owner_number"];
                    }

                    if (Auth::user()->userInformation->user_type != 4) {
                        $user_address = UserAddress::where('user_id', $user_id)->first();
                        $user_address->user_country = isset($data["country"]) ? $data["country"] : "NULL";
                        $user_address->address = isset($data["address"]) ? $data["address"] : "";
                        $user_address->user_state = isset($data["state"]) ? $data["state"] : "NULL";
                        $user_address->user_city = isset($data["city"]) ? $data["city"] : "NULL";

                        $user_address->save();
                    }

                    $arr_user_data->userInformation->save();
                    // addding user company details 
                    if (isset($data["type"]) && $data["type"] == '1') {
//                        $arr_CompanyDetails = CompanyInformation::where('user_id', $user_id)->first();
//                        if (isset($arr_CompanyDetails) && count($arr_CompanyDetails) > 0) {
//
//                            $arr_CompanyDetails->name = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
//                            $arr_CompanyDetails->description = isset($data["description"]) ? $data["description"] : "NULL";
//                            $arr_CompanyDetails->comp_reg_no = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
//                            $arr_CompanyDetails->save();
//                        } else {
//                            $arr_CompanyDetails["name"] = isset($data["comp_name"]) ? $data["comp_name"] : "NULL";
//                            $arr_CompanyDetails["description"] = isset($data["description"]) ? $data["description"] : "NULL";
//                            $arr_CompanyDetails["comp_reg_no"] = isset($data["comp_reg_no"]) ? $data["comp_reg_no"] : "NULL";
//                            $arr_CompanyDetails["user_id"] = $user_id;
//                            CompanyInformation::create($arr_CompanyDetails);
//                        }
                        $company_id = isset($data["comp_name"]) ? $data["comp_name"] : "0";
                        if ($company_id > 0) {
                            $user_data = User::where('id', $user_id)->first();
                            $user_data->company_id = $company_id;
                            $user_data->save();
                        }
                    } else {
                        $arr_CompanyDetails = CompanyInformation::where('user_id', $user_id)->first();
                        if (isset($arr_CompanyDetails) && count($arr_CompanyDetails) > 0) {
                            $arr_CompanyDetails->name = "";
                            $arr_CompanyDetails->description = "";
                            $arr_CompanyDetails->comp_reg_no = "";
                            $arr_CompanyDetails->save();
                        }
                    }
                    $succes_msg = "Driver user user profile has been updated successfully!";
                    return redirect("admin/star-users")->with("profile-updated", $succes_msg);
                }
            }
        } else {
            return redirect("admin/star-users");
        }
    }

    public function updateStarUserVehicle(Request $request, $user_id) {
        \App::setLocale('en');
        $data = $request->all();
        if ($data['type'] == '0') {
            $validate_response = Validator::make($data, array(
                        'vehicle_name' => 'required',
                        'vehicle_desc' => 'required',
                        'plate_number' => 'required',
                        'vehicle_desc' => 'required',
                        'year_manufacture' => 'required',
//                            'status' => 'required'
                            ), array(
                        'vehicle_name.required' => 'Please enter car make',
            ));
        } else {
            $validate_response = Validator::make($data, array(
                        'vehicle_list' => 'required'
                            ), array(
                        'vehicle_list.required' => 'Please select a vehicle',
            ));
        }
        if ($validate_response->fails()) {

            return redirect($request->url())->withErrors($validate_response)->withInput();
        } else {
            if ($data['type'] == '0') {
                $arrVehicleInformationData = array();
                if ($request->hasFile('vehicle_image')) {
                    $uploaded_file = $request->file('vehicle_image');
                    $extension = $uploaded_file->getClientOriginalExtension();
                    $new_file_name = str_replace(".", "-", microtime(true)) . "." . $extension;
                    $path = realpath(dirname(__FILE__) . '/../../../../');
                    $old_file = $path . '/storage/app/public/vehicle-images/' . $new_file_name;
                    $new_file = $path . '/storage/app/public/vehicle-images/' . $new_file_name;
                    Storage::put('public/vehicle-images/' . $new_file_name, file_get_contents($request->file('vehicle_image')->getRealPath()));
                    $command = "convert " . $old_file . " -resize 300x200^ " . $new_file;
                    exec($command);
                    //  Storage::put('public/vehicle-images/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));
                    $arrVehicleInformationData['vehicle_image'] = $new_file_name;
                }
                if ($request->hasFile('plate_number_image')) {

                    $uploaded_file = $request->file('plate_number_image');
                    $extension = $uploaded_file->getClientOriginalExtension();
                    $new_file_name1 = str_replace(".", "-", microtime(true)) . "." . $extension;
                    $path = realpath(dirname(__FILE__) . '/../../../../');
                    $old_file1 = $path . '/storage/app/public/vehicle-number-images/' . $new_file_name1;
                    $new_file1 = $path . '/storage/app/public/vehicle-number-images/' . $new_file_name1;
                    Storage::put('public/vehicle-number-images/' . $new_file_name1, file_get_contents($request->file('plate_number_image')->getRealPath()));
                    $command = "convert " . $old_file1 . " -resize 300x200^ " . $new_file1;
                    exec($command);
                    //  Storage::put('public/vehicle-images/' . $new_file_name, file_get_contents($uploaded_file->getRealPath()));
                    $arrVehicleInformationData['plate_number_image'] = $new_file_name1;
                }
                $arrVehicleInformationData['user_id'] = Auth::user()->id;

                $arrVehicleInformationData['vehicle_name'] = $request->vehicle_name;
                $arrVehicleInformationData['plate_number'] = $request->plate_number;
                $arrVehicleInformationData['year_manufacture'] = $request->year_manufacture;
                $arrVehicleInformationData['financial_type'] = $request->financial_type;
                $arrVehicleInformationData['vehicle_desc'] = $request->vehicle_desc;
                $arrVehicleInformationData['status'] = 1;

                $userVehicleInfo = UserVehicleInformation::create($arrVehicleInformationData);

                $arrDriverData = array();

                if ($user_id != '' && $user_id != '0') {
                    $arrDriverData['user_id'] = $user_id;
                    $arrDriverData['vehicle_id'] = $userVehicleInfo->id;

                    DriverAssignedDetail::create($arrDriverData);
                }
            } else {
                $userVehicleInfo = UserVehicleInformation::where('id', $request->vehicle_list)->first();
                $arrVehicleInformationData = array();
                if ($user_id != '' && $user_id != '0') {
                    $arrVehicleInformationData['user_id'] = $user_id;
                    $arrVehicleInformationData['vehicle_id'] = $userVehicleInfo->id;
                }
                DriverAssignedDetail::create($arrVehicleInformationData);
            }
        }
        $succes_msg = "Driver user user vehicle has been updated successfully!";
        return redirect("admin/update-star-user/" . $user_id)->with("vehicle-updated", $succes_msg);
    }

    public function deletStarUser($user_id) {
        $user = User::find($user_id);
        if ($user) {
            $user->delete();
            return redirect('admin/star-users')->with('delete-user-status', 'Driver user user has been deleted successfully!');
        } else {
            return redirect("admin/star-users");
        }
    }

    protected function updateStarUserEmailInfo(Request $data, $user_id) {
        $data_values = $data->all();
        if (Auth::user()) {
            $arr_user_data = User::find($user_id);
            $validate_response = Validator::make($data_values, array(
                        'email' => 'required|email|max:500|unique:users',
                        'confirm_email' => 'required|email|same:email',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/update-star-user/' . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {
                //updating user email
                $arr_user_data->email = $data->email;
                $arr_user_data->save();

                //updating user status to inactive
                $arr_user_data->userInformation->user_status = 0;
                $arr_user_data->userInformation->save();
                //sending email with verification link
                //sending an email to the user on successfull registration.

                $arr_keyword_values = array();
                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $activation_code = $this->generateReferenceNumber();
                //Assign values to all macros
                $arr_keyword_values['FIRST_NAME'] = $arr_user_data->userInformation->first_name;
                $arr_keyword_values['LAST_NAME'] = $arr_user_data->userInformation->last_name;
                $arr_keyword_values['VERIFICATION_LINK'] = url('verify-user-email/' . $activation_code);
                $arr_keyword_values['SITE_TITLE'] = $site_title;

                // updating activation code                 
                $arr_user_data->userInformation->activation_code = $activation_code;
                $arr_user_data->userInformation->save();

                Mail::send('emailtemplate::admin-email-change', $arr_keyword_values, function ($message) use ($arr_user_data, $site_email, $site_title) {

                    $message->to($arr_user_data->email)->subject("Email Changed Successfully!")->from($site_email, $site_title);
                });

                $succes_msg = "Driver user user email has been updated successfully!";
                return redirect("admin/update-star-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function StarUserDocument(Request $data, $file_id) {

        $file = url('/public/media/backend/images/driver-license/') . '/' . $file_id;
        return response()->download($file, $file_id);
    }

    public function changeStarUserStatus(Request $request) {
        $data = $request->all();
        $user_details = UserInformation::where('user_id', '=', $data['user_id'])->first();
        if ($user_details->user_type == '2') {
            $star_user_details = DriverUserInformation::where('user_id', '=', $data['user_id'])->first();
            $star_vehicle_details = DriverAssignedDetail::where('user_id', '=', $data['user_id'])->first();
            $star_payment_methods = UserPaymentMethod::where('user_id', '=', $data['user_id'])->first();
            if ($user_details) {
                if (($user_details->user_status == 0) && ((count($star_payment_methods) <= 0) || (count($star_vehicle_details) <= 0) || empty($star_user_details->driver_license) || empty($star_user_details->driver_license_flle))) {
                    echo json_encode(array("error" => "1", "error_message" => "Please update required details (licence details, vehicle details, payment accepted methods etc) before you active this user."));
                } else {
                    $rand = 0;
                    if ($data['user_status'] == '1' && ($user_details->user_status == 0)) {
                        if (!isset($user_details->user->password)) {
                            $rand = rand(10000, 999999);
                            $message = "Your password for BAGGI Driver App is: " . $rand;
                            //sending sms to verified user
                            $mobile = $user_details->user_mobile;
                            $mobile = $mobile;
                            $mobile_code = str_replace("+", "", $user_details->mobile_code);
                            $mobile_number_to_send = "+" . $mobile_code . "" . $mobile;
                            try {
                                Twilio::message($mobile_number_to_send, $message);
                            } catch (TwilioException $e) {
                                echo json_encode(array("error" => "1", "error_message" => "These is an issue in sending sms"));
                                exit;
                            }
                            $user_details->user->password = $rand;
                            $user_details->user->save();
                        }
                        if ($rand > 0) {                            
                            echo json_encode(array("error" => "0", "message" => "Account status has been changed successfully. Password sent is " . $rand));
                        } else {
                            $user_details->user_status = $data['user_status'];
                            $user_details->save();
                            echo json_encode(array("error" => "0", "message" => "Account status has been changed successfully."));
                        }
                    } else {
                        $user_details->user_status = $data['user_status'];
                        $user_details->save();
                        echo json_encode(array("error" => "0", "message" => "Account status has been changed successfully"));
                    }
                }
            } else {
                /* if something going wrong providing error message.  */
                echo json_encode(array("error" => "1", "error_message" => "Please update required details (licence details etc) before you active this user."));
            }
        } else {
            $user_details->user_status = $data['user_status'];
            $user_details->save();
            echo json_encode(array("error" => "0", "message" => "Account status has been changed successfully"));
        }
    }

    public function listCitiesContstraint() {
        \App::setLocale('en');
        return view('admin::list-cities-constraint');
    }

    public function updateCityConstarint(Request $request, $city_id) {
        $city = Route::find($city_id);
        // $city_values = Route::where('id', $city_id)->first();
        $user_id_login = Auth::user()->id;
        $country_id = 0;
//        if ($city_values) {
//            $country_id = $city_values->country_id;
//        }

        if ($city) {
//            $is_new_entry = !($city->hasTranslation());
//            $translated_city = $city->translate();

            if ($request->method() == "GET") {
                $countries = Country::translatedIn(\App::getLocale())->get();
                $states_info = State::where('country_id', $country_id)->translatedIn(\App::getLocale())->get();
                $arr_service_category = Category::translatedIn(\App::getLocale())->get();
                $arr_service = Service::translatedIn(\App::getLocale())->get();
                $countryServices = CountryServices::where('city_id', $city_id)->where('user_id', $user_id_login)->get();

                return view("admin::update-city-constraint", array('city' => $city, 'city' => $city, 'states' => $states_info, 'countries' => $countries, "categories" => $arr_service_category, "services" => $arr_service, "country_services" => $countryServices));
            } else {
                // validate and proceed
                $data = $request->all();
                $validate_response = Validator::make($data, array(
//                            'name' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {

                    CountryServices::where('city_id', $city_id)->where('user_id', $user_id_login)->delete();

                    if (isset($data['services']) && !empty($data['services'])) {

                        for ($i = 0; $i < count($data['services']); $i++) {

                            $arr_countryServices = array();


                            $arr_countryServices["service_id"] = $data['services'][$i];
                            if (isset($data['price_type_' . $data['services'][$i]])) {
                                $arr_countryServices["price_type"] = $data['price_type_' . $data['services'][$i]];
                            }
                            if (isset($data['base_price_' . $data['services'][$i]]) && !empty($data['base_price_' . $data['services'][$i]])) {
                                $arr_countryServices["base_price"] = $data['base_price_' . $data['services'][$i]];
                            }
                            if (isset($data['price_per_km_' . $data['services'][$i]]) && !empty($data['price_per_km_' . $data['services'][$i]])) {
                                $arr_countryServices["price_per_km"] = $data['price_per_km_' . $data['services'][$i]];
                            }
                            if (isset($data['price_per_min_' . $data['services'][$i]]) && !empty($data['price_per_min_' . $data['services'][$i]])) {
                                $arr_countryServices["price_per_min"] = $data['price_per_min_' . $data['services'][$i]];
                            }
                            if (isset($data['price_per_weight_' . $data['services'][$i]]) && !empty($data['price_per_weight_' . $data['services'][$i]])) {
                                $arr_countryServices["price_per_weight"] = $data['price_per_weight_' . $data['services'][$i]];
                            }
                            if (isset($data['unloading_time_' . $data['services'][$i]]) && !empty($data['unloading_time_' . $data['services'][$i]])) {
                                $arr_countryServices["unloading_time"] = $data['unloading_time_' . $data['services'][$i]];
                            }
                            if (isset($data['loading_time_type_' . $data['services'][$i]]) && !empty($data['loading_time_type_' . $data['services'][$i]])) {
                                $arr_countryServices["loading_time_type"] = $data['loading_time_type_' . $data['services'][$i]];
                            }
                            if (isset($data['unloading_time_type_' . $data['services'][$i]]) && !empty($data['unloading_time_type_' . $data['services'][$i]])) {
                                $arr_countryServices["unloading_time_type"] = $data['unloading_time_type_' . $data['services'][$i]];
                            }
                            if (isset($data['loading_time_' . $data['services'][$i]]) && !empty($data['loading_time_' . $data['services'][$i]])) {
                                $arr_countryServices["loading_time"] = $data['loading_time_' . $data['services'][$i]];
                            }
                            if (isset($data['check_point_distance_' . $data['services'][$i]]) && !empty($data['check_point_distance_' . $data['services'][$i]])) {
                                $arr_countryServices["check_point_distance"] = $data['check_point_distance_' . $data['services'][$i]];
                            }
                            if (isset($data['flat_price_' . $data['services'][$i]]) && !empty($data['flat_price_' . $data['services'][$i]])) {
                                $arr_countryServices["flat_price"] = $data['flat_price_' . $data['services'][$i]];
                            }
                            if (isset($data['base_km_' . $data['services'][$i]]) && !empty($data['base_km_' . $data['services'][$i]])) {
                                $arr_countryServices["base_km"] = $data['base_km_' . $data['services'][$i]];
                            }
                            $arr_countryServices["country_id"] = $country_id;
                            $arr_countryServices["city_id"] = $city_id;
                            $arr_countryServices["user_id"] = $user_id_login;
                            CountryServices::create($arr_countryServices);
                        }
                        return redirect('admin/city-constraint/list')->with('update-city-status', 'City constraint has been updated successfully!');
                    } else {
                        return redirect('admin/city-constraint/list')->with('city-status-error', 'Please select atleast one service type');
                    }
                }
            }
        } else {
            return redirect("admin/city-constraint/list");
        }
    }

    /* end manage star users */

    /* manage star user services */

    protected function updateStarUserDocumentInfo(Request $data, $user_id) {

        $data_values = $data->all();
        if (Auth::user()) {

            $arr_user_data = User::find($user_id);
            if (empty($arr_user_data->driverUserInformation->driver_license_flle)) {
                $validate_response = Validator::make($data_values, array(
                            'driver_license' => 'required|mimes:pdf,png,jpeg',
                            'licence_no' => 'required',
                ));
            } else {

                $validate_response = Validator::make($data_values, array(
                            'licence_no' => 'required',
                ));
            }
            if ($validate_response->fails()) {
                return redirect("admin/update-star-user/" . $user_id)
                                ->withErrors($validate_response)
                                ->withInput();
            } else {

                $driverUserInformation = array();

                if ($data->file('driver_license')) {
                    $extension = $data->file('driver_license')->getClientOriginalExtension();
                    $new_file_name = time() . "." . $extension;
                    Storage::put('public/star-document/' . $new_file_name, file_get_contents($data->file('driver_license')->getRealPath()));
                    if (isset($arr_user_data->driverUserInformation) && !empty($arr_user_data->driverUserInformation)) {
                        $arr_user_data->driverUserInformation->driver_license_flle = $new_file_name;
                    } else {
                        $driverUserInformation['driver_license_flle'] = $new_file_name;
                    }
                }
                if ($data->file('file')) {
                    $extension = $data->file('file')->getClientOriginalExtension();
                    $new_file_name1 = time() . "." . $extension;
                    Storage::put('public/star-document/' . $new_file_name1, file_get_contents($data->file('file')->getRealPath()));
                    $driverDocument = array();
                    $driverDocument['document_name'] = $data_values['document_name'];
                    $driverDocument['file'] = $new_file_name1;
                    $driverDocument['user_id'] = $user_id;
                    DriverDocument::create($driverDocument);
                }

                if (isset($arr_user_data->driverUserInformation) && !empty($arr_user_data->driverUserInformation)) {
                    $arr_user_data->driverUserInformation->driver_license = $data_values['licence_no'];
                    $arr_user_data->driverUserInformation->id_number = $data_values['id_number'];
                    // $arr_user_data->driverUserInformation->geo_fence = $data_values['geo_fence'];
                    $arr_user_data->driverUserInformation->save();
                } else {

                    $driverUserInformation['driver_license'] = $data_values['licence_no'];
                    $driverUserInformation['id_number'] = $data_values['id_number'];
                    //  $driverUserInformation['geo_fence'] = $data_values['geo_fence'];
                    $driverUserInformation['user_id'] = $user_id;
                    DriverUserInformation::create($driverUserInformation);
                }
                $succes_msg = "Driver user user documents has been updated successfully!";
                return redirect("admin/update-star-user/" . $user_id)->with("profile-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-profile", $errorMsg);
        }
    }

    public function getServicesByCategory(Request $request) {

        $data = $request->all();
        $html = '';
        if ($data['cat_id'] > 0) {
            $arr_service = Service::translatedIn(\App::getLocale())->get()->where('category_id', $data['cat_id']);

            foreach ($arr_service as $service) {
                $temp_html = '<div class="form-group"><label class="control-label">' . $service->name . '</label>';
                $temp_html = $temp_html . '<input type="checkbox" id="service_' . $service->id . '" name="services_' . $data['cat_id'] . '[]" value="' . $service->id . '" ></div>';
                $html = $html . '' . $temp_html;
            }
            echo $html;
        }
    }

    /* manage star user services */

    protected function updateStarUserServicesInfo(Request $data, $user_id) {

        $data_values = $data->all();

        if (Auth::user()) {



            if (array_key_exists('category', $data_values)) {

                UserServiceInformation::where('user_id', $user_id)->delete();

                if (array_key_exists('services', $data_values)) {
                    for ($k = 0; $k < count($data_values['services']); $k++) {
                        $arr_services = array();
                        $arr_services["service_id"] = $data_values['services'][$k];
                        $arr_services["user_id"] = $user_id;
                        $arr_services["goe_fence_area"] = $data_values['geo_area_' . $data_values['services'][$k]];
                        $updated_service_info = UserServiceInformation::create($arr_services);
                    }
                }
//                     }

                $succes_msg = "Driver user user Services has been updated successfully!";
                return redirect("admin/update-star-user/" . $user_id)->with("service-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-service", $errorMsg);
        }
    }

    protected function updateStarUserSpokenlanguage(Request $data, $user_id) {

        $data_values = $data->all();

        if (Auth::user()) {


            if (array_key_exists('language', $data_values)) {

                UserSpokenlanguageinformation::where('user_id', $user_id)->delete();

                for ($k = 0; $k < count($data_values['language']); $k++) {
                    $arr_spoken_languages = array();
                    $arr_spoken_languages["spoken_language_id"] = $data_values['language'][$k];
                    $arr_spoken_languages["user_id"] = $user_id;
                    $updated_language_info = UserSpokenlanguageinformation::create($arr_spoken_languages);
                }

                $succes_msg = "Driver user user preferred language has been updated successfully!";
                return redirect("admin/update-star-user/" . $user_id)->with("language-updated", $succes_msg);
            } else {
                UserSpokenlanguageinformation::where('user_id', $user_id)->delete();
                $succes_msg = "Driver user user preferred language has been updated successfully!";
                return redirect("admin/update-star-user/" . $user_id)->with("language-updated", $succes_msg);
            }
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-language", $errorMsg);
        }
    }

    protected function updateStarPaymentMethods(Request $data, $user_id) {

        $data_values = $data->all();
        if (Auth::user()) {
            UserPaymentMethod::where('user_id', $user_id)->delete();
            $payment_methods = $data['payment_method'];
            if (count($payment_methods) > 0) {
                foreach ($payment_methods as $method) {
                    $userPaymentMethods['user_id'] = $user_id;
                    $userPaymentMethods['payment_method_id'] = $method;
                    $userPaymentMethods['status'] = 1;
                    UserPaymentMethod::create($userPaymentMethods);
                }
            }
            $userOtherInfo = DriverUserInformation::where('user_id', $user_id)->first();
            $userOtherInfo->bank_name = $data['bank_name'];
            $userOtherInfo->ifsc_code = $data['ifsc_code'];
            $userOtherInfo->branch_name = $data['branch_name'];
            $userOtherInfo->account_number = $data['account_number'];
            $userOtherInfo->save();
            $succes_msg = "Driver user  payment details has been updated successfully!";
            return redirect("admin/update-star-user/" . $user_id)->with("payment-method-updated", $succes_msg);
        } else {
            $errorMsg = "Error! Something wrong is going on.";
            Auth::logout();
            return redirect("login")->with("issue-payment_method", $errorMsg);
        }
    }

    /* manage spoken language (preferred language) */

    public function listSpokenlanguage() {

        return view('admin::list-spokenlanguage');
    }

    public function listSpokenlanguageData() {
        \App::setLocale('en');
        $all_Spokenlangusge = SpokenLanguage::translatedIn(\App::getLocale())->get();
        return Datatables::collection($all_Spokenlangusge)
                        ->addColumn('Language', function($Spokenlanguage) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language .= '<li class="dropdown-item"> <a href="update-language/' . $Spokenlanguage->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })->make(true);
    }

    public function createSpokenlanguage(Request $request) {
        if ($request->method() == "GET") {
            return view("admin::create-spokenlanguage");
        } else {
            // validate and proceed
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'name' => 'required|unique:country_translations,name',
            ));

            if ($validate_response->fails()) {
                return redirect()->back()->withErrors($validate_response)->withInput();
            } else {
                $Spokenlangusge = SpokenLanguage::create();
                $en_langusge = $Spokenlangusge->translateOrNew(\App::getLocale());

                $en_langusge->name = $request->name;
                $en_langusge->spoken_language_id = $Spokenlangusge->id;
                $en_langusge->save();

                return redirect('admin/preferred-language/list')->with('country-status', 'preferred language has been created Successfully!');
            }
        }
    }

    public function updateSpokenlanguage(Request $request, $spoken_id) {
        $Spokenlangusge = SpokenLanguage::find($spoken_id);

        if ($Spokenlangusge) {
            $is_new_entry = !($Spokenlangusge->hasTranslation());

            $translated_spoken_language = $Spokenlangusge->translate();

            if ($request->method() == "GET") {

                return view("admin::update-spokenlanguage", array('country_info' => $translated_spoken_language));
            } else {
                // validate and proceed
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required|unique:country_translations,name,' . $translated_spoken_language->id,
//                                                    'lang_icon'    => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
//								$Spokenlangusge->lang_icon = $request->lang_icon;
//                                                                $Spokenlangusge->save();

                    $translated_spoken_language->name = $request->name;

                    if ($is_new_entry) {
                        $translated_spoken_language->spoken_language_id = $spoken_id;
                    }

                    $translated_spoken_language->save();

                    return redirect('admin/preferred-language/list')->with('update-country-status', 'preferred language has been updated successfully!');
                }
            }
        } else {
            return redirect("admin/preferred-language/list");
        }
    }

    public function updateSpokenLang(Request $request, $spoken_id, $locale) {
        $Spokenlangusge = SpokenLanguage::find($spoken_id);

        if ($Spokenlangusge) {
            $is_new_entry = !($Spokenlangusge->hasTranslation($locale));

            $translated_spoken_language = $Spokenlangusge->translateOrNew($locale);

            if ($request->method() == "GET") {
                return view("admin::update-spoken-language", array('country_info' => $translated_spoken_language));
            } else {
                // validate and proceed
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect()->back()->withErrors($validate_response)->withInput();
                } else {
                    $translated_spoken_language->name = $request->name;

                    if ($is_new_entry) {
                        $translated_spoken_language->spoken_language_id = $spoken_id;
                    }

                    $translated_spoken_language->save();

                    return redirect('admin/preferred-language/list')->with('update-country-status', 'preferred language updated successfully!');
                }
            }
        } else {
            return redirect("admin/preferred-language/list");
        }
    }

    public function deleteSpokenlanguage($spoken_id) {
        $Spokenlangusge = SpokenLanguage::find($spoken_id);

        if ($Spokenlangusge) {
            $Spokenlangusge->delete();

            return redirect('admin/preferred-language/list')->with('country-status', 'preferred language has been deleted successfully!');
        } else {
            return redirect("admin/preferred-language/list");
        }
    }

    public function deleteSpokenlanguageSelected($spoken_id) {
        $Spokenlangusge = SpokenLanguage::find($spoken_id);

        if ($Spokenlangusge) {
            $Spokenlangusge->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function IOSPushNotificaton($arrayToSend) {
        //$fcmApiKey = 'AIzaSyCsarbpB9079XBMzmbCN4vH2BCUQXvnbX4';//App API Key(This is google cloud messaging api key not web api key)
        $fcmApiKey = 'AIzaSyCsarbpB9079XBMzmbCN4vH2BCUQXvnbX4'; //App API Key(This is   cloud messaging api key not web api key)
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

    public function IOSPushNotificatonStar($arrayToSend) {
        //  $fcmApiKey = 'AIzaSyABge94AS3mxDcP8wACZd1FSOPzzIjxvwQ';//App API Key(This is google cloud messaging api key not web api key)
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

    /* manage spoken language */
}
