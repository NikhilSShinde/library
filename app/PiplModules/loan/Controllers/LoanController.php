<?php

namespace App\PiplModules\loan\Controllers;

use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use App\PiplModules\loan\Models\Loan;
use App\PiplModules\loan\Models\LoanEmi;
use Datatables;
use App\UserInformation;

class LoanController extends Controller {

    public function __construct() {
        if (!Auth::user()) {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function index() {

        if (Auth::user()) {
            return view("loan::list");
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getLoanInfomation() {
        $all_loans = Loan::all();
        return Datatables::of($all_loans)
                        ->addcolumn('driver_name', function($loan) {
                            if (isset($loan->driverDetail)) {
                                return $loan->driverDetail->first_name . " " . $loan->driverDetail->last_name;
                            } else {
                                return "-";
                            }
                        })
                        ->addcolumn('loan_amount', function($loan) {
                            return "₹" . $loan->loan_amount;
                        })
                        ->addcolumn('intrest', function($loan) {
                            return $loan->intrest . "%";
                        })
                        ->addcolumn('terms', function($loan) {
                            return $loan->terms . " months";
                        })
                        ->addcolumn('emi_detail', function($loan) {
                            return $loan->terms . " months";
                        })
                        ->make(true);
    }
    
    public function deleteSelectedLoan($loan_id) {
        $loan = Loan::find($loan_id);
        if ($loan) {
            $loan->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function addLoanInfomation(Request $request) {
        if ($request->method() == "GET") {
            $drivers = UserInformation::where(['user_type' => 2])->get();
            return view("loan::create", ['drivers' => $drivers]);
        } else {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'driver_id' => 'required',
                        'receipt_bp_type' => 'required',
                        'loan_amount' => 'required',
                        'intrest' => 'required',
                        'terms' => 'required',
                            )
            );

            if ($validate_response->fails()) {
                return redirect($request->url())->withErrors($validate_response)->withInput();
            } else {
                $loan_data = [
                    'driver_id' => $request->driver_id,
                    'receipt_bp_type' => $request->receipt_bp_type,
                    'loan_account' => time(),
                    'loan_amount' => $request->loan_amount,
                    'intrest' => $request->intrest,
                    'terms' => $request->terms
                ];
                $created_loan = Loan::create($loan_data);
                $p = $request->loan_amount;
                $r = $request->intrest;
                $t = $request->terms;
                $emi = $this->emi_calculator($p, $r, $t);

                $monthly_date = date("Y-m-05");
                for ($i = 1; $i <= $request->terms; $i++) {
                    $monthly_date = $this->calculate_next_month($monthly_date);
                    $loanEmiData = [
                        'loan_id' => $created_loan->id,
                        'emi' => $emi,
                        'emi_date' => $monthly_date,
                        'paid' => '1'
                    ];
                    LoanEmi::create($loanEmiData);
                }
                return redirect("admin/loan-list")->with('status', 'Loan created successfully!');
            }
        }
    }

    public function getLoanEMIInfomation($loan_id) {
        return view("loan::emi_list", ['loan_id' => $loan_id]);
    }
    
   

    public function getLoanEMIDataInfomation(Request $request, $loan_id) {
        $all_loans = LoanEmi::where(['loan_id' => $loan_id])->get();
        return Datatables::of($all_loans)
                        ->addcolumn('emi', function($loan) {
                            return "₹" . $loan->emi;
                        })
                        ->addcolumn('emi_date', function($loan) {
                            return $loan->emi_date;
                        })
                        ->addcolumn('paid', function($loan) {
                            $is_paid = "No";
                            if ($loan->paid == 2) {
                                $is_paid = "Yes";
                            }
                            return $is_paid;
                        })
                        ->addcolumn('paid_date', function($loan) {
                            $paid_date = "-";
                            if ($loan->paid == 2) {
                                $paid_date = $loan->paid_date;
                            }
                            return $paid_date;
                        })
                        ->make(true);
    }

    public function payLoanEMIInfomation(Request $request, $loan_id, $loan_emi_id) {
        if ($request->method() == "GET") {
            $emi_detail = LoanEmi::where(['id' => $loan_emi_id])->first();
            if (isset($emi_detail)) {
                return view("loan::pay_emi", ['emi_detail' => $emi_detail]);
            } else {
                return view("loan::list");
            }
        } else {
            $emi_detail = LoanEmi::where(['id' => $loan_emi_id])->first();
            if (isset($emi_detail)) {
                $emi_detail->paid = '2';
                $emi_detail->paid_date = date("Y-m-d H:i:s");
                $emi_detail->save();
                return redirect("admin/loan-emi-list/" . $loan_id)->with('status', 'EMI paid successfully.');
            } else {
                return redirect("admin/loan-list")->with('status', 'Invalid EMI.');
            }
        }
    }
    
     public function dopayment(Request $request) {
        //Input items of form
        $input = $request->all();

        // Please check browser console.
        
        $emi_detail = LoanEmi::where(['id' => $request->loan_emi_id])->first();
        if (isset($emi_detail)) {
            $emi_detail->paid = '2';
            $emi_detail->payment_id = $request->razorpay_payment_id;
            $emi_detail->paid_date = date("Y-m-d H:i:s");
            $emi_detail->save();
        }
        print_r($input);    
        exit;
    }

    public function emi_calculator($amount, $rate, $term) {
        $rate = .12 / 12; // Monthly interest rate        
        return $emi = $amount * $rate * (pow(1 + $rate, $term) / (pow(1 + $rate, $term) - 1));
    }

    function calculate_next_month($start_date = FALSE) {
        if ($start_date) {
            $now = $start_date; // Use supplied start date.
        } else {
            $now = time(); // Use current time.
        }
        $now = strtotime($now);
        // Get the current month (as integer).
        $current_month = date('n', $now);

        // If the we're in Dec (12), set current month to Jan (1), add 1 to year.
        if ($current_month == 12) {
            $next_month = 1;
            $plus_one_month = mktime(0, 0, 0, 1, date('d', $now), date('Y', $now) + 1);
        }
        // Otherwise, add a month to the next month and calculate the date.
        else {
            $next_month = $current_month + 1;
            $plus_one_month = mktime(0, 0, 0, date('m', $now) + 1, date('d', $now), date('Y', $now));
        }

        $i = 1;
        // Go back a day at a time until we get the last day next month.
        while (date('n', $plus_one_month) != $next_month) {
            $plus_one_month = mktime(0, 0, 0, date('m', $now) + 1, date('d', $now) - $i, date('Y', $now));
            $i++;
        }

        return date("Y-m-d", $plus_one_month);
    }

}
