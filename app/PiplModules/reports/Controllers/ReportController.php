<?php

namespace App\PiplModules\reports\Controllers;
use App\User;
use Auth;
use App\Nationality;
use App\Http\Requests;
use App\PiplModules\orderdetails\Models\Order;
use App\PiplModules\orderdetails\Models\OrdersInforation;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\ratingreview\Models\UserRatingInformation;
use DateTime;
use Illuminate\Http\Response;
use App\UserInformation;
use App\UserAddress;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Excel;
use Storage;
use Mail;
use Datatables;



class ReportController extends Controller {

     public function __construct()
    {
          $this->middleware('auth');
          \App::setLocale('en');
     }
      
    public function exportToExcel(Request $request) {
        
      $download = $request->download;


        $order_filter_by = $request->filter_type;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;

//        $orders = Order::select(array('order_unique_id', 'order_place_date_time', 'order_complete_date_time', 'cancelled_date', 'cancel_reson', 'fare_amount', 'waiting_charge', 'other_charges', 'country_id'))->get();
        $orders = Order::all()->sortByDesc("id");

        if ($order_country_id != "") {
            if ($order_country_id != '17') {
                $orders = $orders->filter(function($order)use($order_country_id) {
                    return $order->country_id == $order_country_id;
                });
            }
        }


        //filter order by date

        if ($order_start_date != "" && $order_end_date != "") {
            $orders = $orders->filter(function($order)use($order_start_date, $order_end_date) {
                return date("Y-m-d", strtotime($order->created_at)) >= $order_start_date && date("Y-m-d", strtotime($order->created_at)) <= $order_end_date;
            });
        }

        //filter order by days


        if ($order_filter_by != "") {

            $orders = $orders->filter(function($all_data)use($order_filter_by, $order_country_id) {
                if ($order_filter_by == "today") {
                    $filter_data = date("Y-m-d");
                    return date("Y-m-d", strtotime($all_data->created_at)) == $filter_data;
                } else if ($order_filter_by == "week") {
                    $today_date = date("Y-m-d");
                    $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                    return date("Y-m-d", strtotime($all_data->created_at)) <= $today_date && date("Y-m-d", strtotime($all_data->created_at)) >= $seven_days_back;
                } else if ($order_filter_by == "month") {
                    $d = new DateTime('first day of this month');
                    $month_start_date = $d->format('Y-m-d');
                    $current_date = date("Y-m-d");
                    return date("Y-m-d", strtotime($all_data->created_at)) <= $current_date && date("Y-m-d", strtotime($all_data->created_at)) >= $month_start_date;
                } else if ($order_filter_by == "year") {
                    $current_year = date("o");
                    return date("o", strtotime($all_data->created_at)) == $current_year;
                }
            });
        }

        if ($download == "excel") {
            Excel::create('Trip Reports_' . date('d-M-Y'), function($excel)use($orders) {

                $arr_orders = array();
                $i = 0;
                foreach ($orders as $order) {

                    if (isset($order->status)) {
                        if ($order->status == "0") {
                            $status = "Pending";
                        } else if ($order->status == "1") {
                            $status = "Active";
                        } else if ($order->status == "2") {
                            $status = "Completed";
                        } else if ($order->status == "3") {
                            $status = "Cancelled";
                        } else if ($order->status == "4") {
                            $status = "Expired";
                        } else {
                            $status = " ";
                        }
                    }


                    $arr_orders[$i]["order_unique_id"] = $order->order_unique_id;
                    $arr_orders[$i]["created_at"] = $order->created_at;
                    $arr_orders[$i]["order_place_date_time"] = (isset($order->order_place_date_time)) ? $order->order_place_date_time : "s";
                    $arr_orders[$i]["mate_id"] = (isset($order->mate_id)) ? $order->getUserMateInformation->first_name . " " . $order->getUserMateInformation->last_name : "";
                    $arr_orders[$i]["driver_id"] = (isset($order->driver_id)) ? $order->getUserStarInformation->first_name . " " . $order->getUserStarInformation->last_name : "";
                    $arr_orders[$i]["service_id"] = (isset($order->service_id)) ? $order->getServicesDetails->name : "";
//                    $arr_orders[$i]["category_id"] = (isset($order->service_id)) ? $order->getServicesDetails->categoryInfo->name : "";
                    $arr_orders[$i]["fare_amount"] = "INR ". $order->fare_amount;
                    if($order->total_amount=='')
                    {
                        $arr_orders[$i]["total_amount"] = "INR 0";
                    }else{
                        $arr_orders[$i]["total_amount"] = "INR ". $order->total_amount;
                    }
                     if(isset($order->payment_type) && ($order->payment_type=='1'))
                    {
                        $arr_orders[$i]["payment_type"] = 'Paytm';
                    }else if(isset($order->payment_type) && ($order->payment_type=='2'))
                    {
                         $arr_orders[$i]["payment_type"] = 'Wallet';
                    }else if(isset($order->payment_type) && ($order->payment_type=='3'))
                    {
                         $arr_orders[$i]["payment_type"] = 'Cash';
                    }
                    $arr_orders[$i]["status"] = $status;
                    $arr_orders[$i]["country_id"] = (isset($order->country_id) && $order->country_id != "0") ? $order->country->translate()->name : "";
                    $arr_orders[$i]["order_type"] = (isset($order->order_type) && $order->order_type == 1) ? "Instant order" : ((isset($order->order_type) && $order->order_type == 2) ? "Scheduled order" : ((isset($order->order_type) && $order->order_type == 3) ? "Picknow Deliver Later" : ''));
                    $arr_orders[$i]["selected_pickup_lat"] = (isset($order->getOrderTransInformation->selected_pickup_lat)) ? $order->getOrderTransInformation->selected_pickup_lat : "";
                    $arr_orders[$i]["selected_pickup_long"] = (isset($order->getOrderTransInformation->selected_pickup_long)) ? $order->getOrderTransInformation->selected_pickup_long : "";
                    $arr_orders[$i]["pickup_lat"] = (isset($order->getOrderTransInformation->pickup_lat)) ? $order->getOrderTransInformation->pickup_lat : "";
                    $arr_orders[$i]["pickup_long"] = (isset($order->getOrderTransInformation->pickup_long)) ? $order->getOrderTransInformation->pickup_long : "";
                    $arr_orders[$i]["selected_drop_lat"] = (isset($order->getOrderTransInformation->selected_drop_lat)) ? $order->getOrderTransInformation->selected_drop_lat : "";
                    $arr_orders[$i]["selected_drop_long"] = (isset($order->getOrderTransInformation->selected_drop_long)) ? $order->getOrderTransInformation->selected_drop_long : "";
                    $arr_orders[$i]["drop_lat"] = (isset($order->getOrderTransInformation->drop_lat)) ? $order->getOrderTransInformation->drop_lat : "";
                    $arr_orders[$i]["drop_long"] = (isset($order->getOrderTransInformation->drop_long)) ? $order->getOrderTransInformation->drop_long : "";
                    $arr_orders[$i]["pickup_area"] = (isset($order->getOrderTransInformation->pickup_area)) ? $order->getOrderTransInformation->pickup_area : "";
                    $arr_orders[$i]["drop_area"] = (isset($order->getOrderTransInformation->drop_area)) ? $order->getOrderTransInformation->drop_area : "";
//                    $arr_orders[$i]["contact_person_for_pickup"] = (isset($order->getOrderTransInformation->contact_person_for_pickup)) ? $order->getOrderTransInformation->contact_person_for_pickup : "";
//                    $arr_orders[$i]["contact_person_for_destination"] = (isset($order->getOrderTransInformation->contact_person_for_destination)) ? $order->getOrderTransInformation->contact_person_for_destination : "";
//                    $arr_orders[$i]["pickup_person_contact_no"] = (isset($order->getOrderTransInformation->pickup_person_contact_no)) ? $order->getOrderTransInformation->pickup_person_contact_no : "";
//                    $arr_orders[$i]["destination_person_contact_no"] = (isset($order->getOrderTransInformation->destination_person_contact_no)) ? $order->getOrderTransInformation->destination_person_contact_no : "";
                    $arr_orders[$i]["distance"] = (isset($order->getOrderTransInformation->distance)) ? $order->getOrderTransInformation->distance : "";
//                    $arr_orders[$i]["item_description"] = (isset($order->getOrderTransInformation->item_description)) ? $order->getOrderTransInformation->item_description : "";
//                    $arr_orders[$i]["duration"] = (isset($order->getOrderTransInformation->duration)) ? $order->getOrderTransInformation->duration : "";
                    $i++;
                }
//                dd($arr_orders);


                $excel->sheet('Excel sheet', function($sheet)use($arr_orders) {

                    $sheet->fromArray($arr_orders, null, 'A1', true);

                    for ($intRowNumber = 1; $intRowNumber <= count($arr_orders) + 1; $intRowNumber++) {
                        $sheet->setSize('A' . $intRowNumber, 25, 18);
                        $sheet->setSize('B' . $intRowNumber, 25, 18);
                        $sheet->setSize('C' . $intRowNumber, 25, 18);
                        $sheet->setSize('D' . $intRowNumber, 25, 18);
                        $sheet->setSize('E' . $intRowNumber, 25, 18);
                        $sheet->setSize('F' . $intRowNumber, 25, 18);
                        $sheet->setSize('G' . $intRowNumber, 25, 18);
                        $sheet->setSize('H' . $intRowNumber, 25, 18);
                        $sheet->setSize('I' . $intRowNumber, 25, 18);
                        $sheet->setSize('J' . $intRowNumber, 25, 18);
                        $sheet->setSize('K' . $intRowNumber, 25, 18);
                        $sheet->setSize('L' . $intRowNumber, 25, 18);
                        $sheet->setSize('M' . $intRowNumber, 25, 18);
                        $sheet->setSize('N' . $intRowNumber, 25, 18);
                        $sheet->setSize('O' . $intRowNumber, 40, 18);
                        $sheet->setSize('P' . $intRowNumber, 25, 18);
                        $sheet->setSize('Q' . $intRowNumber, 25, 18);
                        $sheet->setSize('R' . $intRowNumber, 25, 18);
                        $sheet->setSize('S' . $intRowNumber, 25, 18);
                        $sheet->setSize('T' . $intRowNumber, 25, 18);
                        $sheet->setSize('U' . $intRowNumber, 25, 18);
                        $sheet->setSize('V' . $intRowNumber, 50, 18);
                        $sheet->setSize('W' . $intRowNumber, 70, 18);
//                        $sheet->setSize('X' . $intRowNumber, 40, 18);
//                        $sheet->setSize('Y' . $intRowNumber, 35, 18);
////                        $sheet->setSize('Z' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AA' . $intRowNumber, 40, 18);
//                        $sheet->setSize('AB' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AC' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AD' . $intRowNumber, 25, 18);
                    }

                    $sheet->row(1, array(
                        'Trip Number', 'Trip Date', 'Posted Date', 'Customer Name', 'Driver Name', 'Service Name', 'Estimated Amount', 'Total Amount', 'Payment Type', 'Status', 'Country', 'Trip Type', 'Selected Pickup Latitude', 'Selected Pickup Longitude', 'Pickup Latitude', 'Pickup Longitude', 'Selected Drop Latitude', 'Selected Drop Longitude', 'Drop Latitude', 'Drop Longitude', 'Pickup Area', 'Drop Area', 'Distance'
                    ));

                    $sheet->cell('A1:AD1', function($cell) {

                        // Set font
                        $cell->setFont(array(
                            'family' => 'Calibri',
                            'size' => '12',
                            'bold' => true
                        ));
                    });

                    $sheet->freezeFirstRow();
                });
            })->export('xls');
        } else {
            Excel::create('Trip Reports_' . date('d-M-Y'), function($excel)use($orders) {

                $arr_orders = array();
                $i = 0;
                foreach ($orders as $order) {

                    if (isset($order->status)) {
                        if ($order->status == "0") {
                            $status = "Pending";
                        } else if ($order->status == "1") {
                            $status = "Active";
                        } else if ($order->status == "2") {
                            $status = "Completed";
                        } else if ($order->status == "3") {
                            $status = "Cancelled";
                        } else if ($order->status == "4") {
                            $status = "Expired";
                        } else {
                            $status = " ";
                        }
                    }


                    $arr_orders[$i]["order_unique_id"] = $order->order_unique_id;
                    $arr_orders[$i]["created_at"] = $order->created_at;
                    $arr_orders[$i]["order_place_date_time"] = (isset($order->order_place_date_time)) ? $order->order_place_date_time : "s";
                    $arr_orders[$i]["mate_id"] = (isset($order->mate_id)) ? $order->getUserMateInformation->first_name . " " . $order->getUserMateInformation->last_name : "";
                    $arr_orders[$i]["driver_id"] = (isset($order->driver_id)) ? $order->getUserStarInformation->first_name . " " . $order->getUserStarInformation->last_name : "";
                    $arr_orders[$i]["service_id"] = (isset($order->service_id)) ? $order->getServicesDetails->name : "";
//                    $arr_orders[$i]["category_id"] = (isset($order->service_id)) ? $order->getServicesDetails->categoryInfo->name : "";
                    $arr_orders[$i]["fare_amount"] = "INR ".$order->fare_amount;
                    if($order->total_amount=='')
                    {
                        $arr_orders[$i]["total_amount"] = "INR 0";
                    }else{
                        $arr_orders[$i]["total_amount"] = "INR ". $order->total_amount;
                    }
                    if(isset($order->payment_type) && ($order->payment_type=='1'))
                    {
                        $arr_orders[$i]["payment_type"] = 'Paytm';
                    }else if(isset($order->payment_type) && ($order->payment_type=='2'))
                    {
                         $arr_orders[$i]["payment_type"] = 'Wallet';
                    }else if(isset($order->payment_type) && ($order->payment_type=='3'))
                    {
                         $arr_orders[$i]["payment_type"] = 'Cash';
                    }
                    $arr_orders[$i]["status"] = $status;
                    $arr_orders[$i]["country_id"] = (isset($order->country_id) && $order->country_id != "0") ? $order->country->translate()->name : "";
                    $arr_orders[$i]["order_type"] = (isset($order->order_type) && $order->order_type == 1) ? "Instant order" : ((isset($order->order_type) && $order->order_type == 2) ? "Scheduled order" : ((isset($order->order_type) && $order->order_type == 3) ? "Picknow Deliver Later" : ''));
                    $arr_orders[$i]["selected_pickup_lat"] = (isset($order->getOrderTransInformation->selected_pickup_lat)) ? $order->getOrderTransInformation->selected_pickup_lat : "";
                    $arr_orders[$i]["selected_pickup_long"] = (isset($order->getOrderTransInformation->selected_pickup_long)) ? $order->getOrderTransInformation->selected_pickup_long : "";
                    $arr_orders[$i]["pickup_lat"] = (isset($order->getOrderTransInformation->pickup_lat)) ? $order->getOrderTransInformation->pickup_lat : "";
                    $arr_orders[$i]["pickup_long"] = (isset($order->getOrderTransInformation->pickup_long)) ? $order->getOrderTransInformation->pickup_long : "";
                    $arr_orders[$i]["selected_drop_lat"] = (isset($order->getOrderTransInformation->selected_drop_lat)) ? $order->getOrderTransInformation->selected_drop_lat : "";
                    $arr_orders[$i]["selected_drop_long"] = (isset($order->getOrderTransInformation->selected_drop_long)) ? $order->getOrderTransInformation->selected_drop_long : "";
                    $arr_orders[$i]["drop_lat"] = (isset($order->getOrderTransInformation->drop_lat)) ? $order->getOrderTransInformation->drop_lat : "";
                    $arr_orders[$i]["drop_long"] = (isset($order->getOrderTransInformation->drop_long)) ? $order->getOrderTransInformation->drop_long : "";
                    $arr_orders[$i]["pickup_area"] = (isset($order->getOrderTransInformation->pickup_area)) ? $order->getOrderTransInformation->pickup_area : "";
                    $arr_orders[$i]["drop_area"] = (isset($order->getOrderTransInformation->drop_area)) ? $order->getOrderTransInformation->drop_area : "";
//                    $arr_orders[$i]["contact_person_for_pickup"] = (isset($order->getOrderTransInformation->contact_person_for_pickup)) ? $order->getOrderTransInformation->contact_person_for_pickup : "";
//                    $arr_orders[$i]["contact_person_for_destination"] = (isset($order->getOrderTransInformation->contact_person_for_destination)) ? $order->getOrderTransInformation->contact_person_for_destination : "";
//                    $arr_orders[$i]["pickup_person_contact_no"] = (isset($order->getOrderTransInformation->pickup_person_contact_no)) ? $order->getOrderTransInformation->pickup_person_contact_no : "";
//                    $arr_orders[$i]["destination_person_contact_no"] = (isset($order->getOrderTransInformation->destination_person_contact_no)) ? $order->getOrderTransInformation->destination_person_contact_no : "";
                    $arr_orders[$i]["distance"] = (isset($order->getOrderTransInformation->distance)) ? $order->getOrderTransInformation->distance : "";
//                    $arr_orders[$i]["item_description"] = (isset($order->getOrderTransInformation->item_description)) ? $order->getOrderTransInformation->item_description : "";
//                    $arr_orders[$i]["duration"] = (isset($order->getOrderTransInformation->duration)) ? $order->getOrderTransInformation->duration : "";
                    $i++;
                }
//                dd($arr_orders);


                $excel->sheet('Excel sheet', function($sheet)use($arr_orders) {

                    $sheet->fromArray($arr_orders, null, 'A1', true);

                    for ($intRowNumber = 1; $intRowNumber <= count($arr_orders) + 1; $intRowNumber++) {
                        $sheet->setSize('A' . $intRowNumber, 25, 18);
                        $sheet->setSize('B' . $intRowNumber, 25, 18);
                        $sheet->setSize('C' . $intRowNumber, 25, 18);
                        $sheet->setSize('D' . $intRowNumber, 25, 18);
                        $sheet->setSize('E' . $intRowNumber, 25, 18);
                        $sheet->setSize('F' . $intRowNumber, 25, 18);
                        $sheet->setSize('G' . $intRowNumber, 25, 18);
                        $sheet->setSize('H' . $intRowNumber, 25, 18);
                        $sheet->setSize('I' . $intRowNumber, 25, 18);
                        $sheet->setSize('J' . $intRowNumber, 25, 18);
                        $sheet->setSize('K' . $intRowNumber, 25, 18);
                        $sheet->setSize('L' . $intRowNumber, 25, 18);
                        $sheet->setSize('M' . $intRowNumber, 25, 18);
                        $sheet->setSize('N' . $intRowNumber, 25, 18);
                        $sheet->setSize('O' . $intRowNumber, 40, 18);
                        $sheet->setSize('P' . $intRowNumber, 25, 18);
                        $sheet->setSize('Q' . $intRowNumber, 25, 18);
                        $sheet->setSize('R' . $intRowNumber, 25, 18);
                        $sheet->setSize('S' . $intRowNumber, 25, 18);
                        $sheet->setSize('T' . $intRowNumber, 25, 18);
                        $sheet->setSize('U' . $intRowNumber, 25, 18);
                        $sheet->setSize('V' . $intRowNumber, 50, 18);
                        $sheet->setSize('W' . $intRowNumber, 70, 18);
//                        $sheet->setSize('X' . $intRowNumber, 40, 18);
//                        $sheet->setSize('Y' . $intRowNumber, 35, 18);
////                        $sheet->setSize('Z' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AA' . $intRowNumber, 40, 18);
//                        $sheet->setSize('AB' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AC' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AD' . $intRowNumber, 25, 18);
                    }

                    $sheet->row(1, array(
                        'Trip Number', 'Trip Date', 'Posted Date', 'Customer Name', 'Driver Name', 'Service Name',  'Estimated Amount', 'Total Amount', 'Payment Type', 'Status', 'Country', 'Trip Type', 'Selected Pickup Latitude', 'Selected Pickup Longitude', 'Pickup Latitude', 'Pickup Longitude', 'Selected Drop Latitude', 'Selected Drop Longitude', 'Drop Latitude', 'Drop Longitude', 'Pickup Area', 'Drop Area','Distance'                    ));

                    $sheet->cell('A1:AD1', function($cell) {

                        // Set font
                        $cell->setFont(array(
                            'family' => 'Calibri',
                            'size' => '12',
                            'bold' => true
                        ));
                    });

                    $sheet->freezeFirstRow();
                });
            })->export('csv');
        }
    }

    public function index() {

        $all_countries = Country::translatedIn(\App::getLocale())->get();

        return view("reports::list", array("all_countries" => $all_countries));
    }

    public function revenueReportIndex() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();

        return view("reports::list-revenue-report", array("all_countries" => $all_countries));
    }

    public function getOrderData(Request $request) {

        $order_filter_by = $request->order_filter_by;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;
        $country_name = $request->country_name;

        if (Auth::user()) {

            //get all orders

            $all_data = Order::all();

            //filter  order by country
           if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin')))
           {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
            if($country!=17)
            {

                $all_data = $all_data->reject(function ($orderData) use ($country) {
                 return ($orderData->country_id!=$country);
                    
                });
            }
           }
           
            if (Auth::user()->userInformation->user_type == '5')
           {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
            if($country!=17)
            {

                $all_data = $all_data->reject(function ($orderData) use ($country) {
                 return ($orderData->country_id!=$country);
                    
                });
            }
           }
           
             if (Auth::user()->userInformation->user_type == '4')
           {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
            if($country!=17)
            {

                $all_data = $all_data->reject(function ($orderData) use ($country) {
                 return ($orderData->country_id!=$country);
                    
                });
            }
           }
           
            if (Auth::user()->userInformation->user_type == '6')
           {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
            if($country!=17)
            {

                $all_data = $all_data->reject(function ($orderData) use ($country) {
                 return ($orderData->country_id!=$country);
                    
                });
            }
           }
            if ($country_name != "") {
                $all_data = $all_data->filter(function($order)use($country_name) {
                    $country_id = \App\PiplModules\admin\Models\CountryTranslation::where('name', $country_name)->first();
                    return $order->country_id == $country_id->country_id;
                });
            }

            if ($order_country_id != "") {
                if ($order_country_id != '17') {
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

                $all_data = $all_data->filter(function($all_data)use($order_filter_by, $order_country_id) {
                    if ($order_filter_by == "today") {
                        $filter_data = date("Y-m-d");
                        return date("Y-m-d", strtotime($all_data->created_at)) == $filter_data;
                    } else if ($order_filter_by == "week") {
                        $today_date = date("Y-m-d");
                        $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                        return date("Y-m-d", strtotime($all_data->created_at)) <= $today_date && date("Y-m-d", strtotime($all_data->created_at)) >= $seven_days_back;
                    } else if ($order_filter_by == "month") {
                        $d = new DateTime('first day of this month');
                        $month_start_date = $d->format('Y-m-d');
                        $current_date = date("Y-m-d");
                        return date("Y-m-d", strtotime($all_data->created_at)) <= $current_date && date("Y-m-d", strtotime($all_data->created_at)) >= $month_start_date;
                    } else if ($order_filter_by == "year") {
                        $current_year = date("o");
                        return date("o", strtotime($all_data->created_at)) == $current_year;
                    }
                });
            }
            return Datatables::of($all_data)
                            ->addcolumn('star_user', function($all_data) {
                                return (isset($all_data->getUserStarInformation->first_name)) ? $all_data->getUserStarInformation->first_name . ' ' . $all_data->getUserStarInformation->last_name : "";
                            })
                            ->addcolumn('mate_user', function($all_data) {
                                return (isset($all_data->getUserMateInformation->first_name)) ? $all_data->getUserMateInformation->first_name . ' ' . $all_data->getUserMateInformation->last_name : "";
                            })
                            ->addcolumn('service_name', function($all_data) {
                                return $all_data->getServicesDetails->getServiceTransDetails->name;
                            })
                             ->addcolumn('order_type', function($all_data) {
                                $order_type = "Instant";
                                if ($all_data->order_type == '2') {
                                    $order_type = "Scheduled Order";
                                } else if ($all_data->order_type == '3') {
                                    $order_type = "Pick Now Deliver Later";
                                }
                                return ($order_type);
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
                            ->make(true);
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getRevenueData(Request $request) {
        $order_filter_by = $request->order_filter_by;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;

        $country_name = $request->country_name;


        if (Auth::user()) {
            //get all orders
            $all_data = Order::where('status', 2)->get();
             if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
               if($country!=17)
               {
                 $all_data= $all_data->reject(function ($orderData) use ($country){
                     
                     return $orderData->country_id !=$country;
                 });
               }
            }
             if (Auth::user()->userInformation->user_type == '4') {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
               if($country!=17)
               {
                 $all_data= $all_data->reject(function ($orderData) use ($country){
                     
                     return $orderData->country_id !=$country;
                 });
               }
            }
            if (Auth::user()->userInformation->user_type == '5') {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
               if($country!=17)
               {
                 $all_data= $all_data->reject(function ($orderData) use ($country){
                     
                     return $orderData->country_id !=$country;
                 });
               }
            }
            if (Auth::user()->userInformation->user_type == '6') {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
               if($country!=17)
               {
                 $all_data= $all_data->reject(function ($orderData) use ($country){
                     
                     return $orderData->country_id !=$country;
                 });
               }
            }
            //filter  order by country

            if ($country_name != "") {
                $all_data = $all_data->filter(function($order)use($country_name) {
                    $country_id = \App\PiplModules\admin\Models\CountryTranslation::where('name', $country_name)->first();
                    return $order->country_id == $country_id->country_id;
                });
            }

            if ($order_country_id != "") {
                if ($order_country_id != '17') {
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

                $all_data = $all_data->filter(function($all_data)use($order_filter_by, $order_country_id) {
                    if ($order_filter_by == "today") {
                        $filter_data = date("Y-m-d");
                        return date("Y-m-d", strtotime($all_data->created_at)) == $filter_data;
                    } else if ($order_filter_by == "week") {
                        $today_date = date("Y-m-d");
                        $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                        return date("Y-m-d", strtotime($all_data->created_at)) <= $today_date && date("Y-m-d", strtotime($all_data->created_at)) >= $seven_days_back;
                    } else if ($order_filter_by == "month") {
                        $d = new DateTime('first day of this month');
                        $month_start_date = $d->format('Y-m-d');
                        $current_date = date("Y-m-d");
                        return date("Y-m-d", strtotime($all_data->created_at)) <= $current_date && date("Y-m-d", strtotime($all_data->created_at)) >= $month_start_date;
                    } else if ($order_filter_by == "year") {
                        $current_year = date("o");
                        return date("o", strtotime($all_data->created_at)) == $current_year;
                    }
                });
            }
            return Datatables::of($all_data)
                            ->addcolumn('star_user', function($all_data) {
                                return (isset($all_data->getUserStarInformation->first_name)) ? $all_data->getUserStarInformation->first_name . ' ' . $all_data->getUserStarInformation->last_name : "";
                            })
                            ->addcolumn('total_amount', function($all_data) {
                                return (isset($all_data->total_amount)) ? $all_data->total_amount:"0";
                            })
                            ->addcolumn('mate_user', function($all_data) {
                                return (isset($all_data->getUserMateInformation->first_name)) ? $all_data->getUserMateInformation->first_name . ' ' . $all_data->getUserMateInformation->last_name : "";
                            })
                            ->addcolumn('service_name', function($all_data) {
                                return $all_data->getServicesDetails->getServiceTransDetails->name;
                            })
                            ->addcolumn('order_type', function($all_data) {
                                $order_type = "Instant";
                                if ($all_data->order_type == '2') {
                                    $order_type = "Scheduled Order";
                                } else if ($all_data->order_type == '3') {
                                    $order_type = "Pick Now Deliver Later";
                                }
                                return ($order_type);
                            })
                            ->addcolumn('status', function($all_data) {

                                $order_status = "Pending";
                                if ($all_data->status == 1) {
                                    $order_status = "Active";
                                } else if ($all_data->status == 2) {
                                    $order_status= "Completed";
                                } else if ($all_data->status == 3) {
                                    $order_status = "Cancelled";
                                } else if ($all_data->status == 4) {
                                    $order_status = "Expired";
                                }
                                return $order_status;
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
            return view("reports::order-view", array('oder_details' => $order_details));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getStarUsers() {
        $all_countries = Country::translatedIn(\App::getLocale())->get();
        return view("reports::list-star-users-reports", array("all_countries" => $all_countries));
    }

    public function listStarUsersData(Request $request) {

        $user_filter_by = $request->user_filter_by;
        $user_country = $request->user_country;
        $user_reg_from_date = $request->user_reg_from_date;
        $user_reg_to_date = $request->user_reg_to_date;

        $all_users = UserInformation::where("user_type", 2)->get();
        $all_users = $all_users->sortByDesc('id');
        if (Auth::user()->userInformation->user_type == '1') {
            $star_users = $all_users->reject(function ($user) {
                return (($user->user->hasRole('superadmin') || ($user->user_type != 2)));
            });
               if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin')))
           {

                if (Auth::user()->userAddress) {

                    foreach (Auth::user()->userAddress as $address) {
                        $country = $address->user_country;
                    }
                }
            if($country!=17)
            {

                $star_users = $all_users->reject(function ($user) use ($country) {
                $star_country = 0;
                    if ($user->user->userAddress) {

                        foreach ($user->user->userAddress as $address) {
                            $star_country = $address->user_country;
                        }
                    }
                    return ($star_country!=$country);
                    
                });
            }
           }
        }
        if (Auth::user()->userInformation->user_type == '4')  {
          
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
                if($country!='3')
               {
                if ($country != '17') {
                    $contry_passed = ($star_country != $country);
                }
                if ($state != '32') {
                    $state_passed = ($star_state != $state);
                }
                if ($city != '22') {
                    $city_passed = ($star_city != $city);
                }
                 return ($condition || ($contry_passed || $state_passed ||$city_passed));
               }else{
                   $contry_passed = ($star_country != $country);
                   if($state!='5')
                   {
                       return ($condition || ($contry_passed));
                   }else{
                     return ($condition || ($contry_passed || $state_passed));
                   }
               }
               
            });
        }
        if (Auth::user()->userInformation->user_type == '5')  {
          
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
                if($country!='3')
               {
                if ($country != '17') {
                    $contry_passed = ($star_country != $country);
                }
                if ($state != '32') {
                    $state_passed = ($star_state != $state);
                }
                if ($city != '22') {
                    $city_passed = ($star_city != $city);
                }
                 return ($condition || ($contry_passed || $state_passed ||$city_passed));
               }else{
                   $contry_passed = ($star_country != $country);
                   if($state!='5')
                   {
                       return ($condition || ($contry_passed));
                   }else{
                     return ($condition || ($contry_passed || $state_passed));
                   }
               }
               
            });
        }

        if (Auth::user()->userInformation->user_type == '6')  {
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
                if($country!='3')
               {
                if ($country != '17') {
                    $contry_passed = ($star_country != $country);
                }
                if ($state != '32') {
                    $state_passed = ($star_state != $state);
                }
                if ($city != '22') {
                    $city_passed = ($star_city != $city);
                }
                 return ($condition || ($contry_passed || $state_passed ||$city_passed));
               }else{
                   $contry_passed = ($star_country != $country);
                   if($state!='5')
                   {
                       return ($condition || ($contry_passed));
                   }else{
                     return ($condition || ($contry_passed || $state_passed));
                   }
               }
               
            });
        }

//filter by user country


        if ($user_country != "") {
            if ($user_country != "17") {
                $star_users = $star_users->filter(function($user)use($user_country) {
                    return $user->user->userAddress->first()->user_country == $user_country;
                });
            }
        }


        //filter order by date

        if ($user_reg_from_date != "" && $user_reg_to_date != "") {
            $star_users = $star_users->filter(function($user)use($user_reg_from_date, $user_reg_to_date) {
                return date("Y-m-d", strtotime($user->user->created_at)) >= $user_reg_from_date && date("Y-m-d", strtotime($user->user->created_at)) <= $user_reg_to_date;
            });
        }

        //filter order by days


        if ($user_filter_by != "") {
            $star_users = $star_users->filter(function($user)use($user_filter_by) {
                if ($user_filter_by == "today") {
                    $filter_data = date("Y-m-d");
                    return date("Y-m-d", strtotime($user->user->created_at)) == $filter_data;
                } else if ($user_filter_by == "week") {
                    $today_date = date("Y-m-d");
                    $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                    return date("Y-m-d", strtotime($user->user->created_at)) <= $today_date && date("Y-m-d", strtotime($user->user->created_at)) >= $seven_days_back;
                } else if ($user_filter_by == "month") {
                    $d = new DateTime('first day of this month');
                    $month_start_date = $d->format('Y-m-d');
                    $current_date = date("Y-m-d");
                    return date("Y-m-d", strtotime($user->user->created_at)) <= $current_date && date("Y-m-d", strtotime($user->user->created_at)) >= $month_start_date;
                } else if ($user_filter_by == "year") {
                    $current_year = date("o");
                    return date("o", strtotime($user->user->created_at)) == $current_year;
                }
            });
        }

        return Datatables::of($star_users)
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
                        ->addColumn('device', function($admin_users) {
                             return ((isset($admin_users->device_type) && ($admin_users->device_type=='0')?'Android':'IOS'));
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
                                        
                                       if(isset($admin_users->nationality) && $admin_users->nationality!='0')
                                        {
                                           $nationality=Nationality::where('id',$admin_users->nationality)->first();
                                           if(isset($nationality->country_name))
                                           {
                                            $location.=$nationality->country_name;
                                             $location.=" / ";
                                           }
                                          
                                        }
                                         
                                        if(isset($address->countryinfo->translate()->name))
                                        {
                                            $location.=$address->countryinfo->translate()->name;
                                        }
                                       if(isset($address->stateInfo))
                                       {
                                         $location.=" /" . $address->stateInfo->translate()->name;
                                       }
                                       if(isset($address->cityInfo))
                                       {
                                        $location.=" /" . $address->cityInfo->translate()->name;
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
                        ->addColumn('created_at', function($admin_users) {
                            return $admin_users->user->created_at;
                        })
                         ->addColumn('rating', function($regsiter_user) {
                            //finding avg rating
                            $userRatingInfo= UserRatingInformation::where('to_id',$regsiter_user->user_id)->where('status','1')->get();
                            $avg_rating=($userRatingInfo->avg('rating'))?$userRatingInfo->avg('rating'):'0'; 
                            return round($avg_rating);
                        })
                        ->make(true);
    }

    public function exportStarUsersToExcel(Request $request) {

        $download = $request->download;
        $user_filter_by = $request->filter_type;
        $user_country = $request->order_country;
        $user_reg_from_date = $request->start_date;
        $user_reg_to_date = $request->end_date;

        $all_users = UserInformation::where("user_type", 2)->get();
        $all_users = $all_users->sortByDesc('id');

        if (Auth::user()->userInformation->user_type != '4') {
            $star_users = $all_users->reject(function ($user) {

                return (($user->user->hasRole('superadmin') || ($user->user_type != 2)));
            });
        } else {

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
                 if($country!='3')
               {
                if ($country != '17') {
                    $contry_passed = ($star_country != $country);
                }
                if ($state != '32') {
                    $state_passed = ($star_state != $state);
                }
                 return ($condition || ($contry_passed || $state_passed));
               }else{
                   $contry_passed = ($star_country != $country);
                   if($state!='5')
                   {
                       return ($condition || ($contry_passed));
                   }else{
                     return ($condition || ($contry_passed || $state_passed));
                   }
               }
            });
        }




//filter by user country


        if ($user_country != "") {
            if ($user_country != "17") {
                $star_users = $star_users->filter(function($user)use($user_country) {
                    return $user->user->userAddress->first()->user_country == $user_country;
                });
            }
        }


        //filter order by date

        if ($user_reg_from_date != "" && $user_reg_to_date != "") {
            $star_users = $star_users->filter(function($user)use($user_reg_from_date, $user_reg_to_date) {
                return date("Y-m-d", strtotime($user->user->created_at)) >= $user_reg_from_date && date("Y-m-d", strtotime($user->user->created_at)) <= $user_reg_to_date;
            });
        }

        //filter order by days


        if ($user_filter_by != "") {

            $star_users = $star_users->filter(function($user)use($user_filter_by) {
                if ($user_filter_by == "today") {
                    $filter_data = date("Y-m-d");
                    return date("Y-m-d", strtotime($user->user->created_at)) == $filter_data;
                } else if ($user_filter_by == "week") {
                    $today_date = date("Y-m-d");
                    $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                    return date("Y-m-d", strtotime($user->user->created_at)) <= $today_date && date("Y-m-d", strtotime($user->user->created_at)) >= $seven_days_back;
                } else if ($user_filter_by == "month") {
                    $d = new DateTime('first day of this month');
                    $month_start_date = $d->format('Y-m-d');
                    $current_date = date("Y-m-d");
                    return date("Y-m-d", strtotime($user->user->created_at)) <= $current_date && date("Y-m-d", strtotime($user->user->created_at)) >= $month_start_date;
                } else if ($user_filter_by == "year") {
                    $current_year = date("o");
                    return date("o", strtotime($user->user->created_at)) == $current_year;
                }
            });
        }

        if ($download == "excel") {

            Excel::create('Driver User Reports_' . date('d-M-Y'), function($excel)use($star_users) {
                $arr_star_user = array();
                $i = 0;
                foreach ($star_users as $user) {
                    if (isset($user->user->driverUserInformation->availability)) {
                        if ($user->user->driverUserInformation->availability == "0") {
                            $available_status = "Offline";
                        } else if ($user->user->driverUserInformation->availability == "1") {
                            $available_status = "Online";
                        } else {
                            $available_status = " ";
                        }
                    }
                    $userAddressInfo=UserAddress::where('user_id',$user->user_id)->first();
                    $arr_star_user[$i]["user_id"] = $user->user_id;
                    $arr_star_user[$i]["first_name"] = $user->first_name;
                    $arr_star_user[$i]["last_name"] = $user->last_name;
                    $userRatingInfo= UserRatingInformation::where('to_id',$user->user_id)->where('status','1')->get();
                    $avg_rating=($userRatingInfo->avg('rating'))?$userRatingInfo->avg('rating'):'0'; 

                    $arr_star_user[$i]["rating"] = $avg_rating;
                   if($user->user_status=='0') 
                   {
                       $arr_star_user[$i]["status"] ='Inactive';
                   }else  if($user->user_status=='1') 
                   {
                        $arr_star_user[$i]["status"] ='Active';
                   }
                   else  if($user->user_status=='2') 
                   {
                        $arr_star_user[$i]["status"] ='Blocked';
                   }
                   else  if($user->user_status=='3') 
                   {
                        $arr_star_user[$i]["status"] ='Suspended';
                   }
                    
                    $arr_star_user[$i]["user_mobile"] ="+".$user->mobile_code."".$user->user_mobile;
                    $arr_star_user[$i]["created_at"] = (isset($user->user->created_at)) ? $user->user->created_at : "";
                    $countryInfo="";
                    if((isset($userAddressInfo->countryinfo)))
                    {
                        if(isset($user->nationality))
                        {
                            $nationality=Nationality::where('id',$user->nationality)->first();
                           if(isset($nationality->country_name))
                           {
                            $countryInfo.=$nationality->country_name;
                            $countryInfo.='/';
                           }
                        }
                         $countryInfo.=(isset($userAddressInfo->countryinfo)) ? $userAddressInfo->countryinfo->translate()->name : "";
                         $arr_star_user[$i]["country"] = $countryInfo;
                    }
                   
                    $arr_star_user[$i]["state"] = (isset($userAddressInfo->stateInfo)) ? $userAddressInfo->stateInfo->translate()->name : "";
                    $arr_star_user[$i]["city"] =(isset($userAddressInfo->cityInfo)) ? $userAddressInfo->cityInfo->translate()->name : "";
                   if((isset($user->user->driverUserInformation->driver_license)) &&($user->user->driverUserInformation->driver_license!=''))
                   {
                    $arr_star_user[$i]["driver_license_file"] = (isset($user->user->driverUserInformation->driver_license_flle)) ? asset("/storageasset/driver-license/" . $user->user->driverUserInformation->driver_license_flle) : "";
                   }else{
                       $arr_star_user[$i]["driver_license_file"] = 'Not updated';
                   }
                    $arr_star_user[$i]["driver_license"] = (isset($user->user->driverUserInformation->driver_license)) ? $user->user->driverUserInformation->driver_license : "";
                    $arr_star_user[$i]["geo_fence"] = (isset($user->user->driverUserInformation->geo_fence)) ? $user->user->driverUserInformation->geo_fence : "";
                    $arr_star_user[$i]["id_number"] = (isset($user->user->driverUserInformation->id_number)) ? $user->user->driverUserInformation->id_number : "";
                    $arr_star_user[$i]["availability"] = $available_status;
                    if((isset($user->profile_picture)) &&($user->profile_picture!=''))
                   {
                        $arr_star_user[$i]["profile_picture"] = (isset($user->profile_picture)) ? asset("/storageasset/user-images/" . $user->profile_picture) : "";
                    }else{
                        $arr_star_user[$i]["profile_picture"] = 'Not updated';
                    }
                    $arr_star_user[$i]["device_id"] = $user->user->device_id;
                    $arr_star_user[$i]["device_type"] = (isset($user->device_type) && ($user->device_type == "0")) ? "Android" : "IOS";

                    $i++;
                }

                $excel->sheet('Excel sheet', function($sheet)use($arr_star_user) {

                    $sheet->fromArray($arr_star_user, null, 'A1', true);

                    for ($intRowNumber = 1; $intRowNumber <= count($arr_star_user) + 1; $intRowNumber++) {
                        $sheet->setSize('A' . $intRowNumber, 25, 18);
                        $sheet->setSize('B' . $intRowNumber, 25, 18);
                        $sheet->setSize('C' . $intRowNumber, 25, 18);
                        $sheet->setSize('D' . $intRowNumber, 25, 18);
                        $sheet->setSize('E' . $intRowNumber, 25, 18);
                        $sheet->setSize('F' . $intRowNumber, 25, 18);
                        $sheet->setSize('G' . $intRowNumber, 25, 18);
                        $sheet->setSize('H' . $intRowNumber, 25, 18);
                        $sheet->setSize('I' . $intRowNumber, 50, 18);
                        $sheet->setSize('J' . $intRowNumber, 25, 18);
                        $sheet->setSize('K' . $intRowNumber, 25, 18);
                        $sheet->setSize('L' . $intRowNumber, 25, 18);
                        $sheet->setSize('M' . $intRowNumber, 25, 18);
                        $sheet->setSize('N' . $intRowNumber, 50, 18);
                        $sheet->setSize('O' . $intRowNumber, 25, 18);
                        $sheet->setSize('P' . $intRowNumber, 25, 18);
                        $sheet->setSize('Q' . $intRowNumber, 25, 18);
                        $sheet->setSize('R' . $intRowNumber, 25, 18);
                    }



                    $sheet->row(1, array(
                        'User Id', 'First Name', 'Last Name','Rating','Status', 'Mobile Number', 'Registration Date', 'Nationality/Country', 'State', 'City', 'Driver License File', 'Driver License', 'Geo Fence', 'Batch Number', 'Availability Status', 'Profile Picture', 'Device Id', 'Device Type',
                    ));

                    $sheet->cell('A1:R1', function($cell) {

                        // Set font
                        $cell->setFont(array(
                            'family' => 'Calibri',
                            'size' => '12',
                            'bold' => true
                        ));
                    });




                    $sheet->freezeFirstRow();
                });
            })->export('xls');
        } else {
            Excel::create('Driver User Reports_' . date('d-M-Y'), function($excel)use($star_users) {
                $arr_star_user = array();
                $i = 0;
                foreach ($star_users as $user) {
                    if (isset($user->user->driverUserInformation->availability)) {
                        if ($user->user->driverUserInformation->availability == "0") {
                            $available_status = "Offline";
                        } else if ($user->user->driverUserInformation->availability == "1") {
                            $available_status = "Online";
                        } else {
                            $available_status = " ";
                        }
                    }
                    if (isset($user->user->userAddress{0}->countryinfo->country_code) && isset($user->user_mobile)) {
                        $user_mobile = $user->user->userAddress{0}->countryinfo->country_code . " " . $user->user_mobile;
                    } else {
                        $user_mobile = $user->user_mobile;
                    }

                    $arr_star_user[$i]["user_id"] = $user->user_id;
                    $arr_star_user[$i]["first_name"] = $user->first_name;
                    $arr_star_user[$i]["last_name"] = $user->last_name;
                    $userRatingInfo= UserRatingInformation::where('to_id',$user->user_id)->where('status','1')->get();
                    $avg_rating=($userRatingInfo->avg('rating'))?$userRatingInfo->avg('rating'):'0'; 

                    $arr_star_user[$i]["rating"] = $avg_rating;
                   
                    if($user->user_status=='0') 
                   {
                       $arr_star_user[$i]["status"] ='Inactive';
                   }else  if($user->user_status=='1') 
                   {
                        $arr_star_user[$i]["status"] ='Active';
                   }
                   else  if($user->user_status=='2') 
                   {
                        $arr_star_user[$i]["status"] ='Blocked';
                   }
                   else  if($user->user_status=='3') 
                   {
                        $arr_star_user[$i]["status"] ='Suspended';
                   }
                    $arr_star_user[$i]["user_mobile"] = $user_mobile;
                    $arr_star_user[$i]["created_at"] = (isset($user->user->userAddress->created_at)) ? $user->user->userAddress->created_at : "";
                    $arr_star_user[$i]["country"] = (isset($user->user->userAddress->countryinfo)) ? $user->user->userAddress->countryinfo->translate()->name : "";
                    $arr_star_user[$i]["state"] = (isset($user->user->userAddress->countryinfo)) ? $user->user->userAddress->stateInfo->translate()->name : "";
                    $arr_star_user[$i]["city"] = (isset($user->user->userAddress->countryinfo)) ? $user->user->userAddress->cityInfo->translate()->name : "";
                     if((isset($user->user->driverUserInformation->driver_license_flle)) &&($user->user->driverUserInformation->driver_license_flle!=''))
                    {
                        $arr_star_user[$i]["driver_license_file"] = (isset($user->user->driverUserInformation->driver_license_flle)) ? asset("/storageasset/driver-license/" . $user->user->driverUserInformation->driver_license_flle) : "";
                    }else{
                        $arr_star_user[$i]["driver_license_file"] = 'Not updated';
                    }
                    
                    $arr_star_user[$i]["driver_license"] = (isset($user->user->driverUserInformation->driver_license)) ? $user->user->driverUserInformation->driver_license : "";
                    $arr_star_user[$i]["geo_fence"] = (isset($user->user->driverUserInformation->geo_fence)) ? $user->user->driverUserInformation->geo_fence : "";
                    $arr_star_user[$i]["id_number"] = (isset($user->user->driverUserInformation->id_number)) ? $user->user->driverUserInformation->id_number : "";
                    $arr_star_user[$i]["availability"] = $available_status;
                     if((isset($user->profile_picture)) &&($user->profile_picture!=''))
                   {
                        $arr_star_user[$i]["profile_picture"] = (isset($user->profile_picture)) ? asset("/storageasset/user-images/" . $user->profile_picture) : "";
                    }else{
                        $arr_star_user[$i]["profile_picture"] = 'Not updated';
                    }
                    $arr_star_user[$i]["device_id"] = $user->user->device_id;
                    $arr_star_user[$i]["device_type"] = (isset($user->device_type) && $user->device_type == "0") ? "Android" : "IOS";

                    $i++;
                }

                $excel->sheet('Excel sheet', function($sheet)use($arr_star_user) {

                    $sheet->fromArray($arr_star_user, null, 'A1', true);

                    for ($intRowNumber = 1; $intRowNumber <= count($arr_star_user) + 1; $intRowNumber++) {
                        $sheet->setSize('A' . $intRowNumber, 25, 18);
                        $sheet->setSize('B' . $intRowNumber, 25, 18);
                        $sheet->setSize('C' . $intRowNumber, 25, 18);
                        $sheet->setSize('D' . $intRowNumber, 25, 18);
                        $sheet->setSize('E' . $intRowNumber, 25, 18);
                        $sheet->setSize('F' . $intRowNumber, 25, 18);
                        $sheet->setSize('G' . $intRowNumber, 25, 18);
                        $sheet->setSize('H' . $intRowNumber, 25, 18);
                        $sheet->setSize('I' . $intRowNumber, 50, 18);
                        $sheet->setSize('J' . $intRowNumber, 25, 18);
                        $sheet->setSize('K' . $intRowNumber, 25, 18);
                        $sheet->setSize('L' . $intRowNumber, 25, 18);
                        $sheet->setSize('M' . $intRowNumber, 25, 18);
                        $sheet->setSize('N' . $intRowNumber, 50, 18);
                        $sheet->setSize('O' . $intRowNumber, 25, 18);
                        $sheet->setSize('P' . $intRowNumber, 25, 18);
                        $sheet->setSize('Q' . $intRowNumber, 25, 18);
                        $sheet->setSize('R' . $intRowNumber, 25, 18);
                    }



                    $sheet->row(1, array(
                        'User Id', 'First Name', 'Last Name','Rating','Status', 'Mobile Number','Registration Date', 'Country', 'State', 'City', 'Driver License File', 'Driver License', 'Geo Fence', 'Batch Number', 'Availability Status', 'Profile Picture', 'Device Id', 'Device Type'
                    ));

                    $sheet->cell('A1:R1', function($cell) {

                        // Set font
                        $cell->setFont(array(
                            'family' => 'Calibri',
                            'size' => '12',
                            'bold' => true
                        ));
                    });




                    $sheet->freezeFirstRow();
                });
            })->export('csv');
        }
    }

    public function exportToExcelRevenueReport(Request $request) {
        $download = $request->download;


        $order_filter_by = $request->filter_type;
        $order_country_id = $request->order_country;
        $order_start_date = $request->start_date;
        $order_end_date = $request->end_date;

//        $orders = Order::select(array('order_unique_id', 'order_place_date_time', 'order_complete_date_time', 'cancelled_date', 'cancel_reson', 'fare_amount', 'waiting_charge', 'other_charges', 'country_id'))->get();
        $orders = Order::where('status', 2)->get();

        if ($order_country_id != "") {
            if ($order_country_id != '17') {
                $orders = $orders->filter(function($order)use($order_country_id) {
                    return $order->country_id == $order_country_id;
                });
            }
        }


        //filter order by date

        if ($order_start_date != "" && $order_end_date != "") {
            $orders = $orders->filter(function($order)use($order_start_date, $order_end_date) {
                return date("Y-m-d", strtotime($order->created_at)) >= $order_start_date && date("Y-m-d", strtotime($order->created_at)) <= $order_end_date;
            });
        }

        //filter order by days


        if ($order_filter_by != "") {

            $orders = $orders->filter(function($all_data)use($order_filter_by, $order_country_id) {
                if ($order_filter_by == "today") {
                    $filter_data = date("Y-m-d");
                    return date("Y-m-d", strtotime($all_data->created_at)) == $filter_data;
                } else if ($order_filter_by == "week") {
                    $today_date = date("Y-m-d");
                    $seven_days_back = date('Y-m-d', strtotime('-7 days'));
                    return date("Y-m-d", strtotime($all_data->created_at)) <= $today_date && date("Y-m-d", strtotime($all_data->created_at)) >= $seven_days_back;
                } else if ($order_filter_by == "month") {
                    $d = new DateTime('first day of this month');
                    $month_start_date = $d->format('Y-m-d');
                    $current_date = date("Y-m-d");
                    return date("Y-m-d", strtotime($all_data->created_at)) <= $current_date && date("Y-m-d", strtotime($all_data->created_at)) >= $month_start_date;
                } else if ($order_filter_by == "year") {
                    $current_year = date("o");
                    return date("o", strtotime($all_data->created_at)) == $current_year;
                }
            });
        }

        if ($download == "excel") {
            Excel::create('Revenue Report' . date('d-M-Y'), function($excel)use($orders) {

                $arr_orders = array();
                $i = 0;
                foreach ($orders as $order) {

                    if (isset($order->status)) {
                        if ($order->status == "0") {
                            $status = "Pending";
                        } else if ($order->status == "1") {
                            $status = "Active";
                        } else if ($order->status == "2") {
                            $status = "Completed";
                        } else if ($order->status == "3") {
                            $status = "Cancelled";
                        } else if ($order->status == "4") {
                            $status = "Expired";
                        } else {
                            $status = " ";
                        }
                    }


                    $arr_orders[$i]["order_unique_id"] = $order->order_unique_id;
                    $arr_orders[$i]["created_at"] = $order->created_at;
                    $arr_orders[$i]["order_place_date_time"] = (isset($order->order_place_date_time)) ? $order->order_place_date_time : "s";
                    $arr_orders[$i]["mate_id"] = (isset($order->mate_id)) ? $order->getUserMateInformation->first_name . " " . $order->getUserMateInformation->last_name : "";
                    $arr_orders[$i]["driver_id"] = (isset($order->driver_id)) ? $order->getUserStarInformation->first_name . " " . $order->getUserStarInformation->last_name : "";
                    $arr_orders[$i]["service_id"] = (isset($order->service_id)) ? $order->getServicesDetails->name : "";
//                    $arr_orders[$i]["category_id"] = (isset($order->service_id)) ? $order->getServicesDetails->categoryInfo->name : "";
                    $arr_orders[$i]["fare_amount"] ="INR ". $order->fare_amount;
                    $arr_orders[$i]["total_amount"] ="INR ". $order->total_amount;
                    $arr_orders[$i]["payment_type"] = (isset($order->payment_type) && $order->payment_type == 1) ? "Online" : ((isset($order->payment_type) && $order->payment_type == 2) ? "COD" : ((isset($order->payment_type) && $order->payment_type == 3) ? "Wallet" : ''));
                    $arr_orders[$i]["status"] = $status;
                    $arr_orders[$i]["country_id"] = (isset($order->country_id) && $order->country_id != "0") ? $order->country->translate()->name : "";
                    $arr_orders[$i]["order_type"] = (isset($order->order_type) && $order->order_type == 0) ? "Instant order" : ((isset($order->order_type) && $order->order_type == 1) ? "Scheduled order" : ((isset($order->order_type) && $order->order_type == 2) ? "Picknow Deliver Later" : ''));
                    $arr_orders[$i]["selected_pickup_lat"] = (isset($order->getOrderTransInformation->selected_pickup_lat)) ? $order->getOrderTransInformation->selected_pickup_lat : "";
                    $arr_orders[$i]["selected_pickup_long"] = (isset($order->getOrderTransInformation->selected_pickup_long)) ? $order->getOrderTransInformation->selected_pickup_long : "";
                    $arr_orders[$i]["pickup_lat"] = (isset($order->getOrderTransInformation->pickup_lat)) ? $order->getOrderTransInformation->pickup_lat : "";
                    $arr_orders[$i]["pickup_long"] = (isset($order->getOrderTransInformation->pickup_long)) ? $order->getOrderTransInformation->pickup_long : "";
                    $arr_orders[$i]["selected_drop_lat"] = (isset($order->getOrderTransInformation->selected_drop_lat)) ? $order->getOrderTransInformation->selected_drop_lat : "";
                    $arr_orders[$i]["selected_drop_long"] = (isset($order->getOrderTransInformation->selected_drop_long)) ? $order->getOrderTransInformation->selected_drop_long : "";
                    $arr_orders[$i]["drop_lat"] = (isset($order->getOrderTransInformation->drop_lat)) ? $order->getOrderTransInformation->drop_lat : "";
                    $arr_orders[$i]["drop_long"] = (isset($order->getOrderTransInformation->drop_long)) ? $order->getOrderTransInformation->drop_long : "";
                    $arr_orders[$i]["pickup_area"] = (isset($order->getOrderTransInformation->pickup_area)) ? $order->getOrderTransInformation->pickup_area : "";
                    $arr_orders[$i]["drop_area"] = (isset($order->getOrderTransInformation->drop_area)) ? $order->getOrderTransInformation->drop_area : "";
//                    $arr_orders[$i]["contact_person_for_pickup"] = (isset($order->getOrderTransInformation->contact_person_for_pickup)) ? $order->getOrderTransInformation->contact_person_for_pickup : "";
//                    $arr_orders[$i]["contact_person_for_destination"] = (isset($order->getOrderTransInformation->contact_person_for_destination)) ? $order->getOrderTransInformation->contact_person_for_destination : "";
//                    $arr_orders[$i]["pickup_person_contact_no"] = (isset($order->getOrderTransInformation->pickup_person_contact_no)) ? $order->getOrderTransInformation->pickup_person_contact_no : "";
//                    $arr_orders[$i]["destination_person_contact_no"] = (isset($order->getOrderTransInformation->destination_person_contact_no)) ? $order->getOrderTransInformation->destination_person_contact_no : "";
                    $arr_orders[$i]["distance"] = (isset($order->getOrderTransInformation->distance)) ? $order->getOrderTransInformation->distance : "";
//                    $arr_orders[$i]["item_description"] = (isset($order->getOrderTransInformation->item_description)) ? $order->getOrderTransInformation->item_description : "";
//                    $arr_orders[$i]["duration"] = (isset($order->getOrderTransInformation->duration)) ? $order->getOrderTransInformation->duration : "";
                    $i++;
                }
//                dd($arr_orders);


                $excel->sheet('Excel sheet', function($sheet)use($arr_orders) {

                    $sheet->fromArray($arr_orders, null, 'A1', true);

                    for ($intRowNumber = 1; $intRowNumber <= count($arr_orders) + 1; $intRowNumber++) {
                        $sheet->setSize('A' . $intRowNumber, 25, 18);
                        $sheet->setSize('B' . $intRowNumber, 25, 18);
                        $sheet->setSize('C' . $intRowNumber, 25, 18);
                        $sheet->setSize('D' . $intRowNumber, 25, 18);
                        $sheet->setSize('E' . $intRowNumber, 25, 18);
                        $sheet->setSize('F' . $intRowNumber, 25, 18);
                        $sheet->setSize('G' . $intRowNumber, 25, 18);
                        $sheet->setSize('H' . $intRowNumber, 25, 18);
                        $sheet->setSize('I' . $intRowNumber, 25, 18);
                        $sheet->setSize('J' . $intRowNumber, 25, 18);
                        $sheet->setSize('K' . $intRowNumber, 25, 18);
                        $sheet->setSize('L' . $intRowNumber, 25, 18);
                        $sheet->setSize('M' . $intRowNumber, 25, 18);
                        $sheet->setSize('N' . $intRowNumber, 25, 18);
                        $sheet->setSize('O' . $intRowNumber, 40, 18);
                        $sheet->setSize('P' . $intRowNumber, 25, 18);
                        $sheet->setSize('Q' . $intRowNumber, 25, 18);
                        $sheet->setSize('R' . $intRowNumber, 25, 18);
                        $sheet->setSize('S' . $intRowNumber, 25, 18);
                        $sheet->setSize('T' . $intRowNumber, 25, 18);
                        $sheet->setSize('U' . $intRowNumber, 25, 18);
                        $sheet->setSize('V' . $intRowNumber, 50, 18);
                        $sheet->setSize('W' . $intRowNumber, 70, 18);
//                        $sheet->setSize('X' . $intRowNumber, 40, 18);
//                        $sheet->setSize('Y' . $intRowNumber, 35, 18);
//                        $sheet->setSize('Z' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AA' . $intRowNumber, 40, 18);
//                        $sheet->setSize('AB' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AC' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AD' . $intRowNumber, 25, 18);
                    }

                    $sheet->row(1, array(
                        'Trip Number', 'Trip Date', 'Posted Date', 'Customer Name', 'Driver Name', 'Service Name', 'Estimated Amount', 'Total Amount', 'Payment Type', 'Status', 'Country', 'Trip Type', 'Selected Pickup Latitude', 'Selected Pickup Longitude', 'Pickup Latitude', 'Pickup Longitude', 'Selected Drop Latitude', 'Selected Drop Longitude', 'Drop Latitude', 'Drop Longitude', 'Pickup Area', 'Drop Area','Distance'
                    ));

                    $sheet->cell('A1:AD1', function($cell) {

                        // Set font
                        $cell->setFont(array(
                            'family' => 'Calibri',
                            'size' => '12',
                            'bold' => true
                        ));
                    });

                    $sheet->freezeFirstRow();
                });
            })->export('xls');
        } else {
            Excel::create('Revenue Report'. date('d-M-Y'), function($excel)use($orders) {

                $arr_orders = array();
                $i = 0;
                foreach ($orders as $order) {

                    if (isset($order->status)) {
                        if ($order->status == "0") {
                            $status = "Pending";
                        } else if ($order->status == "1") {
                            $status = "Active";
                        } else if ($order->status == "2") {
                            $status = "Completed";
                        } else if ($order->status == "3") {
                            $status = "Cancelled";
                        } else if ($order->status == "4") {
                            $status = "Expired";
                        } else {
                            $status = " ";
                        }
                    }


                    $arr_orders[$i]["order_unique_id"] = $order->order_unique_id;
                    $arr_orders[$i]["created_at"] = $order->created_at;
                    $arr_orders[$i]["order_place_date_time"] = (isset($order->order_place_date_time)) ? $order->order_place_date_time : "s";
                    $arr_orders[$i]["mate_id"] = (isset($order->mate_id)) ? $order->getUserMateInformation->first_name . " " . $order->getUserMateInformation->last_name : "";
                    $arr_orders[$i]["driver_id"] = (isset($order->driver_id)) ? $order->getUserStarInformation->first_name . " " . $order->getUserStarInformation->last_name : "";
                    $arr_orders[$i]["service_id"] = (isset($order->service_id)) ? $order->getServicesDetails->name : "";
//                    $arr_orders[$i]["category_id"] = (isset($order->service_id)) ? $order->getServicesDetails->categoryInfo->name : "";
                    $arr_orders[$i]["fare_amount"] = "INR ".$order->fare_amount;
                    $arr_orders[$i]["total_amount"] ="INR ".$order->total_amount;
                    $arr_orders[$i]["payment_type"] = (isset($order->payment_type) && $order->payment_type == 1) ? "Online" : ((isset($order->payment_type) && $order->payment_type == 2) ? "COD" : ((isset($order->payment_type) && $order->payment_type == 3) ? "Wallet" : ''));
                    $arr_orders[$i]["status"] = $status;
                    $arr_orders[$i]["country_id"] = (isset($order->country_id) && $order->country_id != "0") ? $order->country->translate()->name : "";
                    $arr_orders[$i]["order_type"] = (isset($order->order_type) && $order->order_type == 1) ? "Instant order" : ((isset($order->order_type) && $order->order_type == 2) ? "Scheduled order" : ((isset($order->order_type) && $order->order_type == 3) ? "Picknow Deliver Later" : ''));
                    $arr_orders[$i]["selected_pickup_lat"] = (isset($order->getOrderTransInformation->selected_pickup_lat)) ? $order->getOrderTransInformation->selected_pickup_lat : "";
                    $arr_orders[$i]["selected_pickup_long"] = (isset($order->getOrderTransInformation->selected_pickup_long)) ? $order->getOrderTransInformation->selected_pickup_long : "";
                    $arr_orders[$i]["pickup_lat"] = (isset($order->getOrderTransInformation->pickup_lat)) ? $order->getOrderTransInformation->pickup_lat : "";
                    $arr_orders[$i]["pickup_long"] = (isset($order->getOrderTransInformation->pickup_long)) ? $order->getOrderTransInformation->pickup_long : "";
                    $arr_orders[$i]["selected_drop_lat"] = (isset($order->getOrderTransInformation->selected_drop_lat)) ? $order->getOrderTransInformation->selected_drop_lat : "";
                    $arr_orders[$i]["selected_drop_long"] = (isset($order->getOrderTransInformation->selected_drop_long)) ? $order->getOrderTransInformation->selected_drop_long : "";
                    $arr_orders[$i]["drop_lat"] = (isset($order->getOrderTransInformation->drop_lat)) ? $order->getOrderTransInformation->drop_lat : "";
                    $arr_orders[$i]["drop_long"] = (isset($order->getOrderTransInformation->drop_long)) ? $order->getOrderTransInformation->drop_long : "";
                    $arr_orders[$i]["pickup_area"] = (isset($order->getOrderTransInformation->pickup_area)) ? $order->getOrderTransInformation->pickup_area : "";
                    $arr_orders[$i]["drop_area"] = (isset($order->getOrderTransInformation->drop_area)) ? $order->getOrderTransInformation->drop_area : "";
//                    $arr_orders[$i]["contact_person_for_pickup"] = (isset($order->getOrderTransInformation->contact_person_for_pickup)) ? $order->getOrderTransInformation->contact_person_for_pickup : "";
//                    $arr_orders[$i]["contact_person_for_destination"] = (isset($order->getOrderTransInformation->contact_person_for_destination)) ? $order->getOrderTransInformation->contact_person_for_destination : "";
//                    $arr_orders[$i]["pickup_person_contact_no"] = (isset($order->getOrderTransInformation->pickup_person_contact_no)) ? $order->getOrderTransInformation->pickup_person_contact_no : "";
//                    $arr_orders[$i]["destination_person_contact_no"] = (isset($order->getOrderTransInformation->destination_person_contact_no)) ? $order->getOrderTransInformation->destination_person_contact_no : "";
                    $arr_orders[$i]["distance"] = (isset($order->getOrderTransInformation->distance)) ? $order->getOrderTransInformation->distance : "";
//                    $arr_orders[$i]["item_description"] = (isset($order->getOrderTransInformation->item_description)) ? $order->getOrderTransInformation->item_description : "";
//                    $arr_orders[$i]["duration"] = (isset($order->getOrderTransInformation->duration)) ? $order->getOrderTransInformation->duration : "";
                    $i++;
                }
//                dd($arr_orders);


                $excel->sheet('Excel sheet', function($sheet)use($arr_orders) {

                    $sheet->fromArray($arr_orders, null, 'A1', true);

                    for ($intRowNumber = 1; $intRowNumber <= count($arr_orders) + 1; $intRowNumber++) {
                        $sheet->setSize('A' . $intRowNumber, 25, 18);
                        $sheet->setSize('B' . $intRowNumber, 25, 18);
                        $sheet->setSize('C' . $intRowNumber, 25, 18);
                        $sheet->setSize('D' . $intRowNumber, 25, 18);
                        $sheet->setSize('E' . $intRowNumber, 25, 18);
                        $sheet->setSize('F' . $intRowNumber, 25, 18);
                        $sheet->setSize('G' . $intRowNumber, 25, 18);
                        $sheet->setSize('H' . $intRowNumber, 25, 18);
                        $sheet->setSize('I' . $intRowNumber, 25, 18);
                        $sheet->setSize('J' . $intRowNumber, 25, 18);
                        $sheet->setSize('K' . $intRowNumber, 25, 18);
                        $sheet->setSize('L' . $intRowNumber, 25, 18);
                        $sheet->setSize('M' . $intRowNumber, 25, 18);
                        $sheet->setSize('N' . $intRowNumber, 25, 18);
                        $sheet->setSize('O' . $intRowNumber, 40, 18);
                        $sheet->setSize('P' . $intRowNumber, 25, 18);
                        $sheet->setSize('Q' . $intRowNumber, 25, 18);
                        $sheet->setSize('R' . $intRowNumber, 25, 18);
                        $sheet->setSize('S' . $intRowNumber, 25, 18);
                        $sheet->setSize('T' . $intRowNumber, 25, 18);
                        $sheet->setSize('U' . $intRowNumber, 25, 18);
                        $sheet->setSize('V' . $intRowNumber, 50, 18);
                        $sheet->setSize('W' . $intRowNumber, 70, 18);
//                        $sheet->setSize('X' . $intRowNumber, 40, 18);
//                        $sheet->setSize('Y' . $intRowNumber, 35, 18);
//                        $sheet->setSize('Z' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AA' . $intRowNumber, 40, 18);
//                        $sheet->setSize('AB' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AC' . $intRowNumber, 25, 18);
//                        $sheet->setSize('AD' . $intRowNumber, 25, 18);
                    }

                    $sheet->row(1, array(
                        'Trip Number', 'Trip Date', 'Posted Date', 'Customer Name', 'Driver Name', 'Service Name', 'Category Name', 'Estimated Amount', 'Total Amount', 'Payment Type', 'Status', 'Country', 'Trip Type', 'Selected Pickup Latitude', 'Selected Pickup Longitude', 'Pickup Latitude', 'Pickup Longitude', 'Selected Drop Latitude', 'Selected Drop Longitude', 'Drop Latitude', 'Drop Longitude', 'Pickup Area', 'Drop Area','Distance'                    ));

                    $sheet->cell('A1:AD1', function($cell) {

                        // Set font
                        $cell->setFont(array(
                            'family' => 'Calibri',
                            'size' => '12',
                            'bold' => true
                        ));
                    });

                    $sheet->freezeFirstRow();
                });
            })->export('csv');
        }
    }

}
