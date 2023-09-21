<?php

namespace App\PiplModules\supporttickets\Controllers;

use Auth;
use App\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\supporttickets\Models\SupportTicket;
use App\PiplModules\supporttickets\Models\supportTicketAssignInformations;
use App\PiplModules\supporttickets\Models\supportTicketConversation;
use App\PiplModules\admin\Models\GlobalSetting;
use App\PiplModules\emailtemplate\Models\EmailTemplate;
use App\UserInformation;
use Mail;
use Datatables;
use GlobalValues;

class SupportController extends Controller {

    public function index() {
        if (Auth::user()) {
            return view("supporttickets::list");
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getSupportTicketData() {
        $user_detail = Auth::user();
        $all_SupportTicket = SupportTicket::all()->sortByDesc("id");
        if ($user_detail->userInformation->user_type == '4' || $user_detail->userInformation->user_type == '6') {
            $all_SupportTicket = SupportTicket::all()->sortByDesc("id");
            $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($user_detail) {
                if (isset($obj->assignTicketInformation->assign_to)) {
                    return $obj->assignTicketInformation->assign_to == $user_detail->id;
                }
            });
        }
        if ($user_detail->userInformation->user_type == '5') {
            $agentuser = User::where('supervisor_id', $user_detail->id)->get();
            $arrAgentUsers = array();
            if (count($agentuser) > 0) {
                foreach ($agentuser as $agent) {
                    $arrAgentUsers[] = $agent->id;
                }
            }

            $all_SupportTicket = SupportTicket::all()->sortByDesc("id");
            $all_SupportTicket = $all_SupportTicket->filter(function ($obj)use($arrAgentUsers) {
                //get all user agents


                if (isset($obj->assignTicketInformation->assign_to)) {
                    return (in_array($obj->assignTicketInformation->assign_to, $arrAgentUsers));
                }
            });
        }
        if (Auth::user()->userInformation->user_type == '1' && (!Auth::user()->hasRole('superadmin'))) {

            if (Auth::user()->userAddress) {

                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                }
            }
            if ($country != '17') {
                $all_SupportTicket = $all_SupportTicket->reject(function ($tickets) use ($country) {

                    return $tickets->orderInformation->country_id != $country;
                });
            }
        }

        return Datatables::of($all_SupportTicket)
                        ->addcolumn('added_by', function($all_SupportTicket) {
                            $name = "";
                            $name = isset($all_SupportTicket->UserInformation) ? $all_SupportTicket->UserInformation->first_name : "";
                            $name .= isset($all_SupportTicket->UserInformation) ? $all_SupportTicket->UserInformation->last_name : "";
                            if (isset($name)) {
                                return $name;
                            } else {
                                return "--";
                            }
                        })
                        ->addcolumn('order_unique_id', function($all_SupportTicket) {
                            if (isset($all_SupportTicket->orderInformation)) {
                                return $all_SupportTicket->orderInformation->order_unique_id;
                            } else {
                                return "-";
                            }
                        })
                        ->addcolumn('is_reply', function($all_SupportTicket) {
                            return (count($all_SupportTicket->TicketConversation) > 1) ? '<label class="label-sm label-success">Replied</label>' : '<label class="label-sm label-danger">Not yet reply</label>';
                        })
                        ->addcolumn('assign_btn', function($all_SupportTicket) {
                            if (supportTicketAssignInformations::where('ticket_id', $all_SupportTicket->id)->first()) {
                                return '<a href="javascript:void(0);" onclick="assignTicket(' . $all_SupportTicket->id . ')" class="btn btn-sm btn-success">Reassign</a> <a href="' . url('/') . '/admin/suppor-ticket-details/' . $all_SupportTicket->id . '" class="btn btn-sm btn-info">View & Reply</a>';
                            } else {
                                return '<a href="javascript:void(0);" onclick="assignTicket(' . $all_SupportTicket->id . ')" class="btn btn-sm btn-danger">Assign</a> <a href="' . url('/') . '/admin/suppor-ticket-details/' . $all_SupportTicket->id . '" class="btn btn-sm btn-info">View & Reply</a>';
                            }
                        })
                        ->make(true);
    }

    public function deleteSelectedTicket($ticket_id) {
        $ticket_id = SupportTicket::find($ticket_id);
        if ($ticket_id) {
            $ticket_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function getAgentUser(Request $request) {
        $ticket_details = SupportTicket::find($request->ticket_id);
        $arr_agent_user = UserInformation::where('user_type', '4')->whereNotIn('user_id', array($ticket_details->added_by))->get();
        $ticket_id = $request->ticket_id;
        if (count($arr_agent_user) > 0) {
            foreach ($arr_agent_user as $key => $agent_user) {
                $ticket_assigned_data = supportTicketAssignInformations::where('ticket_id', $ticket_id)->where('assign_to', $agent_user['user_id'])->first();
                $arr_agent_user[$key]->asigned_user_information = $ticket_assigned_data;
            }
        }
        return $arr_agent_user;
    }

    public function assignTicket(Request $request) {
        $login_user_details = Auth::user();
        $ticket_assigned_data = supportTicketAssignInformations::where('ticket_id', $request->ticket_id)->first();
        $ticket_details = SupportTicket::find($request->ticket_id);
        if (count($ticket_assigned_data) > 0) {
            $ticket_assigned_data->assign_to = $request->assign_to;
            $ticket_assigned_data->save();

            $email_template = EmailTemplate::where("template_key", 'assign-ticket')->first();
            $contact_email = $ticket_assigned_data->UserInformation->user->email;

            $arr_keyword_values = array();

            $ticket_assigned_data = supportTicketAssignInformations::where('ticket_id', $request->ticket_id)->where('assign_to', $request->assign_to)->first();

            $site_email = GlobalValues::get('site-email');
            $site_title = GlobalValues::get('site-title');
            $arr_keyword_values['USER_NAME'] = $ticket_assigned_data->UserInformation->first_name;
            $arr_keyword_values['TICKET_ID'] = $ticket_details->ticket_unique_id;
            $arr_keyword_values['SITE_TITLE'] = $site_title;

            Mail::send("emailtemplate::assign-ticket", $arr_keyword_values, function ($message) use ($email_template, $contact_email, $site_email, $site_title) {
                $message->to($contact_email)->subject($email_template->subject)->from($site_email, $site_title);
            });

            return redirect("admin/support-tickets")->with('status', 'Ticket re-assigned successfully!');
        } else {
            $assign_ticket = new supportTicketAssignInformations();
            if ($request->assign_to != '' && $request->ticket_id != '') {
                $assign_ticket->ticket_id = $request->ticket_id;
                $assign_ticket->assign_by = $login_user_details->id;
                $assign_ticket->assign_to = $request->assign_to;
                $assign_ticket->save();

                $email_template = EmailTemplate::where("template_key", 'assign-ticket')->first();

                $arr_keyword_values = array();
                $ticket_assigned_data = supportTicketAssignInformations::where('ticket_id', $request->ticket_id)->where('assign_to', $request->assign_to)->first();
                $contact_email = $ticket_assigned_data->UserInformation->user->email;

                $site_email = GlobalValues::get('site-email');
                $site_title = GlobalValues::get('site-title');
                $arr_keyword_values['USER_NAME'] = $ticket_assigned_data->UserInformation->first_name;
                $arr_keyword_values['TICKET_ID'] = $ticket_details->ticket_unique_id;
                $arr_keyword_values['SITE_TITLE'] = $site_title;

                Mail::send("emailtemplate::assign-ticket", $arr_keyword_values, function ($message) use ($email_template, $contact_email, $site_email, $site_title) {
                    $message->to($contact_email)->subject($email_template->subject)->from($site_email, $site_title);
                });

                return redirect("admin/support-tickets")->with('status', 'Ticket assigned successfully!');
            }
        }
    }

    public function getSupportTicketDetails($ticket_id) {
        if (Auth::user()) {
            $ticket_details = SupportTicket::find($ticket_id);
            $ticket_assigned_data = supportTicketAssignInformations::where('ticket_id', $ticket_id)->first();
            $reply_on_ticket = supportTicketConversation::where('ticket_id', $ticket_id)->get();
            return view("supporttickets::view", array('support_ticket' => $ticket_details, 'ticket_assigned_data' => $ticket_assigned_data, 'ticket_id' => $ticket_id, 'reply_on_ticket' => $reply_on_ticket));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function supportTicketReply(Request $request) {
        $data = $request->all();
        if (Auth::user()->userInformation->user_type != '1') {
            $validate_response = Validator::make($data, array(
                        'message' => 'required',
                        'assigned_to' => 'required'
            ));
        } else {
            $validate_response = Validator::make($data, array(
                        'message' => 'required'
            ));
        }


        if ($validate_response->fails()) {
            return redirect('admin/suppor-ticket-details/' . $request->ticket_id)->withErrors($validate_response)->withInput();
        } else {

            //check if suport ticket status is 0 then it will be 1
            $ticket_details = SupportTicket::find($request->ticket_id);

            if ($request->is_completed == 1) {
                $ticket_details->status = 2;
                $ticket_details->save();
            } else if ($ticket_details->status == 0) {
                $ticket_details->status = 1;
                $ticket_details->save();
            }

            $reply_ticket = new supportTicketConversation();
            $reply_ticket->ticket_id = $request->ticket_id;
            $reply_ticket->posted_by = Auth::user()->id;
            $reply_ticket->description = $request->message;
            $reply_ticket->save();

            $email_template = EmailTemplate::where("template_key", 'reply-on-assign-ticket')->first();
            $ticket_assigned_data = array();
            $contact_email = '';
            if (Auth::user()->userInformation->user_type != '1') {
                $ticket_assigned_data = supportTicketAssignInformations::where('ticket_id', $request->ticket_id)->where('assign_to', $request->assigned_to)->first();
                $ticket_assigned_data->UserInformation->user->email;
            } else {
                $contact_email = Auth::user()->email;
                $ticket_assigned_data = Auth::user();
            }

            $arr_keyword_values = array();
            $site_email = GlobalValues::get('site-email');
            $site_title = GlobalValues::get('site-title');
            $arr_keyword_values['USER_NAME'] = $ticket_assigned_data->UserInformation->first_name . ' ' . $ticket_assigned_data->UserInformation->last_name;
            $arr_keyword_values['MESSAGE'] = strip_tags($request->message);
            $arr_keyword_values['TICKET_ID'] = $ticket_details->ticket_unique_id;
            $arr_keyword_values['SITE_TITLE'] = $site_title;

            Mail::send("emailtemplate::reply-on-assign-ticket", $arr_keyword_values, function ($message) use ($email_template, $contact_email, $site_email, $site_title) {
                $message->to($contact_email)->subject($email_template->subject)->from($site_email, $site_title);
            });

            return redirect("admin/suppor-ticket-details/" . $request->ticket_id)->with('status', 'Replied has been posted successfully.');
        }
    }

}
