<?php
namespace App\PiplModules\wallethistory\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\wallethistory\Models\UserWalletDetail;
use Mail;
use Datatables;

class WalletController extends Controller {

    public function index($user_id = '') {
        if (Auth::user()) {
            return view("wallethistory::list", array('user_id' => $user_id));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getWalletHistoryData($user_id = '') {
        if ($user_id == '') {
            $all_wallet_data = UserWalletDetail::all();
            $all_wallet_data=$all_wallet_data->sortByDesc('id');
        } else {
            $all_wallet_data = UserWalletDetail::where('user_id', $user_id)->latest()->get();
        }
        return Datatables::of($all_wallet_data)
                        ->addcolumn('user_name', function($all_wallet_data) {
                           if(isset($all_wallet_data->UserInformation->first_name))
                           {
                                return $all_wallet_data->UserInformation->first_name . ' ' . $all_wallet_data->UserInformation->last_name;
                           }
                        })
                        ->addcolumn('transaction_type', function($all_wallet_data) {
                            return ($all_wallet_data->transaction_type == '0') ? 'Credit' : 'Debit';
                        })
                        ->addcolumn('trans_description', function($all_wallet_data) {
                            return ($all_wallet_data->trans_desc);
                        })
                        ->addcolumn('user_type', function($all_wallet_data) {
                            $user_type="";
                            if(isset($all_wallet_data->UserInformation->user_type) && $all_wallet_data->UserInformation->user_type == '2')
                            {
                                $user_type="Delivery User";
                            }else if(isset($all_wallet_data->UserInformation->user_type) && $all_wallet_data->UserInformation->user_type == '3')
                            {
                                $user_type="Customer";
                            }
                            else if(isset($all_wallet_data->UserInformation->user_type) && $all_wallet_data->UserInformation->user_type == '4')
                            {
                                $user_type="Agent";
                            }
                            else if(isset($all_wallet_data->UserInformation->user_type) && $all_wallet_data->UserInformation->user_type == '5')
                            {
                                $user_type="Company";
                            }
                            else if(isset($all_wallet_data->UserInformation->user_type) && $all_wallet_data->UserInformation->user_type == '6')
                            {
                                $user_type="Manager";
                            }
                            return $user_type;
                        })
                        ->make(true);
    }

}
