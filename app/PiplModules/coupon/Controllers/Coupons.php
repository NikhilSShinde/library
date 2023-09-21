<?php

namespace App\PiplModules\coupon\Controllers;

use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\coupon\Models\Coupon;
use App\PiplModules\admin\Models\Country;
use Mail;
use Datatables;

class Coupons extends Controller {

    public function index() {
        if (Auth::user()) {
            return view("coupon::list");
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getCouponData() {
        $all_coupons = Coupon::all();
        return Datatables::of($all_coupons)
                        ->addcolumn('status', function($all_coupons) {
                            return ($all_coupons->status == '1') ? 'Active' : 'Inactive';
                        })
                        ->addcolumn('start_date', function($all_coupons) {
                            return date('Y-m-d',strtotime($all_coupons->start_date));
                        })
                        ->addcolumn('end_date', function($all_coupons) {
                            return date('Y-m-d',strtotime($all_coupons->end_date));
                        })
                        ->addcolumn('type', function($all_coupons) {
                            if ($all_coupons->type == 0) {
                                $type = 'Percentage';
                            } elseif ($all_coupons->type == 1) {
                                $type = 'Fixed';
                            } else {
                                $type = 'Conditional';
                            }
                            return $type;
                        })
                        ->addcolumn('country_code', function($all_coupons) {
                            if (isset($all_coupons->getCountry)) {
                                return $all_coupons->getCountry->name;
                            } else {
                                return "-";
                            }
                        })
                        ->make(true);
    }

    public function deleteSelectedCoupon($coupon_id) {
        $coupon_id = Coupon::find($coupon_id);
        if ($coupon_id) {
            $coupon_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function createCoupon(Request $request) {
        if (Auth::user()) {
            if ($request->method() == "GET") {
                $countries = Country::all();
                $rand_string = $this->generateRandomString();
                return view("coupon::create", array('rand_string' => $rand_string, 'countires' => $countries));
            } else {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'coupon_code' => 'required',
                            'title' => 'required',
//                        'discount' => array('required', 'regex:/^\d*(\.\d{2}\)?$/'),
                            'amount' => 'required|numeric|between:1,99',
                            'country_code' => 'required',
                            'start_date' => 'required',
                            'end_date' => 'required',
                            'type' => 'required',
                            'allow_user_for_multi_use' => 'required',
                            'usage_time' => 'required|numeric|min:1',
                ));

                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    $created_coupon = Coupon::create();
                    $created_coupon->title = $request->title;
                    $created_coupon->code = $request->coupon_code;
                    $created_coupon->discount = $request->discount;
                    $created_coupon->country_id = $request->country_code;
                    $created_coupon->start_date = $request->start_date;
                    $created_coupon->end_date = $request->end_date;
                    $created_coupon->type = $request->type;
                    $created_coupon->allow_user_for_multi_use = $request->allow_user_for_multi_use;
                    $created_coupon->usage_time = $request->usage_time;
                    $created_coupon->save();
                    return redirect("admin/coupons")->with('status', 'Coupon created successfully!');
                }
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function updateCoupon(Request $request, $coupun_id) {
        if (Auth::user()) {

            $arr_coupun_code_details = Coupon::find($coupun_id);
            if ($request->method() == "GET") {
                $countries = Country::all();
                return view("coupon::edit", array('arr_coupun_code_details' => $arr_coupun_code_details, 'countires' => $countries));
            } else {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'coupon_code' => 'required',
                            'title' => 'required',
                            'amount' => 'required|numeric|between:1,99',
                            'country_code' => 'required',
                            'start_date' => 'required',
                            'end_date' => 'required',
                            'type' => 'required',
                            'allow_user_for_multi_use' => 'required',
                            'usage_time' => 'required|numeric|min:1',
                ));

                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    $arr_coupun_code_details->title = $request->title;
                    $arr_coupun_code_details->code = $request->coupon_code;
                    $arr_coupun_code_details->discount = $request->amount;
                    $arr_coupun_code_details->country_id = $request->country_code;
                    $arr_coupun_code_details->start_date = $request->start_date;
                    $arr_coupun_code_details->end_date = $request->end_date;
                    $arr_coupun_code_details->type = $request->type;
                    $arr_coupun_code_details->allow_user_for_multi_use = $request->allow_user_for_multi_use;
                    $arr_coupun_code_details->status = $request->status;
                    $arr_coupun_code_details->usage_time = $request->usage_time;
                    $arr_coupun_code_details->save();
                    return redirect("admin/coupons")->with('status', 'Coupon updated successfully!');
                }
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function viewCoupon($coupon_id) {
        if (Auth::user()) {
            $coupon_details = Coupon::find($coupon_id);
            return view("coupon::view", array('arr_coupun_code_details' => $coupon_details));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    function generateRandomString($length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
