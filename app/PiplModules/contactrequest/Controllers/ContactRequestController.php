<?php
namespace App\PiplModules\contactrequest\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\admin\Models\Country;
use App\PiplModules\contactrequest\Models\ContactRequest;
use App\PiplModules\contactrequest\Models\ContactRequestReply;
use App\PiplModules\emailtemplate\Models\EmailTemplate;
use App\PiplModules\admin\Models\GlobalSetting;
use App\PiplModules\contactrequest\Models\ContactRequestCategory;
use Mail;
use Datatables;
use Lang;
use GlobalValues;
class ContactRequestController extends Controller {

    public function index() {

        return view("contactrequest::list");
    }
     public function __construct() {
         
         \App::setLocale('en');
    }

    public function contactRequestData() {
        $all_requests = ContactRequest::all()->sortByDesc("id");
        
        if(Auth::user()->userInformation->user_type=='4')
        {
            
            $mobile_code = 0;
            
            if (Auth::user()->userInformation) {
               $mobile_code=str_replace("+","",Auth::user()->userInformation->mobile_code);
            }
            $all_requests=$all_requests->reject(function($request) use($mobile_code)
            {
                if($mobile_code!=0)
                {
                    return($request->country_code!=$mobile_code);
                }
                
            });
        }
         if(Auth::user()->userInformation->user_type=='1')
        {
            
            $country = 0;
            if (Auth::user()->userAddress) {
                foreach (Auth::user()->userAddress as $address) {
                    $country = $address->user_country;
                   
                }
            }
            $mobile_code = 0;
            if($country!='17' && $country!='0')
            {
                $countryInfo=Country::where('id',$country)->first();
                if(count($countryInfo)>0)
                {
                    $mobile_code=str_replace("+","",$countryInfo->country_code);
                }
            }
            $all_requests=$all_requests->reject(function($request) use($mobile_code)
            {
                if($mobile_code!=0)
                {
                    return($request->country_code!=$mobile_code);
                }
                
            });
        }
        if(Auth::user()->userInformation->user_type=='5')
        {
            
            $mobile_code = 0;
            
            if (Auth::user()->userInformation) {
               $mobile_code=str_replace("+","",Auth::user()->userInformation->mobile_code);
            }
            $all_requests=$all_requests->reject(function($request) use($mobile_code)
            {
                if($mobile_code!=0)
                {
                    return($request->country_code!=$mobile_code);
                }
                
            });
        }
        if(Auth::user()->userInformation->user_type=='6')
        {
            
            $mobile_code = 0;
            
            if (Auth::user()->userInformation) {
               $mobile_code=str_replace("+","",Auth::user()->userInformation->mobile_code);
            }
            $all_requests=$all_requests->reject(function($request) use($mobile_code)
            {
                if($mobile_code!=0)
                {
                    return($request->country_code!=$mobile_code);
                }
                
            });
        }
        return Datatables::of($all_requests)
                        ->addColumn('name', function($request) {
                            $nameemailphone = $request->contact_name;
                            if (isset($request->contact_email)) {
                                $nameemailphone.="/ " . $request->contact_email;
                            }
                            if (isset($request->contact_phone)) {
                                $nameemailphone.="/ " . $request->contact_phone;
                            }
                            return $nameemailphone;
                        })
                        ->addColumn('category', function($request) {
                            $category_name = "";
                            if (isset($request->contact_request_category)) {
                                $category_name = $request->category->translate()->name;
                            } else {
                                $category_name = "-";
                            }
                            return $category_name;
                        })
                        ->addColumn('is_reply', function($request) {
                            if ($request->is_reply == 0) {
                                return '<span class="alert-danger">Not Replied</span>';
                            } else {
                                return '<span class="alert-success">Replied</span>';
                            }
                        })
                        ->make(true);
    }

    public function showContactForm(Request $request) {

        if ($request->method() == "GET") {
            
            $contact_categories = ContactRequestCategory::translatedIn(\App::getLocale())->get();
            $arr_user_data = array("name" => '', 'email' => '');
            if (Auth::check()) {
                $arr_user_data['name'] = Auth::user()->userInformation->first_name;
                $arr_user_data['email'] = Auth::user()->email;
            }
            
               
            return view("contactrequest::contact-us-form", array('contact_categories' => $contact_categories, 'user_data' => $arr_user_data));
        } elseif ($request->method() == "POST") {
           
            $data = $request->all();
            $validate_response = Validator::make($data,
                     [    
                            'name' => 'required',   
//                            'category' => 'required',   
                            'email' => 'required|email',   
                            'subject' => 'required',   
                            'message' => 'required', 
                            'phone' => 'required'  
                    ],
                    [
                        'name.required'=>Lang::choice('website_keywords.name_is_required',\App::getLocale()), 
                        'category.required'=>Lang::choice('website_keywords.category_is_required',\App::getLocale()), 
                        'email.required'=>Lang::choice('website_keywords.email_is_required',\App::getLocale()),
                        'subject.required'=>Lang::choice('website_keywords.subject_is_required',\App::getLocale()),
                        'message.required'=>Lang::choice('website_keywords.message_is_required',\App::getLocale()),
                        'phone.numeric'=>Lang::choice('website_keywords.phone_is_numberic',\App::getLocale())

                    ]
                        
            );

            if ($validate_response->fails()) {
               
                return redirect('/')->withErrors($validate_response)->withInput();
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
                if($ip=='192.168.2.1' || $ip=='192.168.2.22')
                {
                    $ip="182.72.79.154";
                }
                $country_iso="";
                $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
               if(count($details)>0)
               {
                   if(isset($details->country))
                   {
                       $country_iso=$details->country;
                   }
               }
              $countryInfo= Country::where('iso',$country_iso)->first();
             
                $arr_request_data = array();
                $reference_no = $this->generateReferenceNumber();
                $arr_request_data["contact_name"] = $request->name;
                $arr_request_data["contact_email"] = $request->email;
                $arr_request_data["contact_phone"] = $request->phone;
                if(count($countryInfo)>0)
                {
                    $arr_request_data["country_code"] =str_replace("+","",$countryInfo->country_code);
                }

                if (Auth::check()) {
                    $arr_request_data["contacted_by"] = Auth::user()->id;
                }

                $arr_request_data["contact_subject"] = $request->subject;
                $arr_request_data["contact_message"] = $request->message;
                $arr_request_data["contact_request_category"] = $request->category;
                $arr_request_data["reference_no"] = $reference_no;

                $attachments = array();

                if ($request->hasFile('attachment')) {

                    $uploaded_files = $request->file('attachment');

                    foreach ($uploaded_files as $uploaded_file) {

                        $new_file_name = $uploaded_file->getClientOriginalName();

                        Storage::put('public/contact-requests/' . $reference_no . "/" . $new_file_name, file_get_contents($uploaded_file->getRealPath()));
                        $attachments[] = $new_file_name;
                    }

                    $arr_request_data["contact_attachment"] = $attachments;
                }

                $created_request = ContactRequest::create($arr_request_data);

                $email_template = EmailTemplate::where("template_key", 'contact-request')->first();
                $contact_email = GlobalSetting::where('slug', 'contact-email')->first();

                $arr_keyword_values = array();

                $selected_category_name = "0";

                if (!empty($request->category)) {
                    $category_selected = $created_request->category;

                    if ($category_selected) {
                        $selected_category_name = $category_selected->translate()->name;
                    }
                }
                $site_email=GlobalValues::get('site-email');
                $site_title=GlobalValues::get('site-title');
                $arr_keyword_values['USER_NAME'] = $request->name;
                $arr_keyword_values['USER_EMAIL'] = $request->email;
                $arr_keyword_values['USER_PHONE'] = $request->phone;
                $arr_keyword_values['CATEGORY'] = $selected_category_name;
                $arr_keyword_values['REQUEST_DATE'] = date("d M, Y H:i A");
                $arr_keyword_values['SUBJECT'] = $request->subject;
                $arr_keyword_values['MESSAGE'] = $request->message;
                $arr_keyword_values['REFERENCE'] = $reference_no;

                $arr_keyword_values['SITE_TITLE'] =$site_title;
                Mail::send("emailtemplate::contact-request", $arr_keyword_values, function ($message) use ($email_template, $attachments, $contact_email, $reference_no,$site_email,$site_title) {
                $message->to($contact_email->value)->subject($email_template->subject)->from($site_email,$site_title);
                    if (count($attachments) > 0) {
                        $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix() . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "contact-requests" . DIRECTORY_SEPARATOR . $reference_no . DIRECTORY_SEPARATOR;
                        foreach ($attachments as $attachment) {
                            $pathToFile = $storagePath . $attachment;
                            $message->attach($pathToFile);
                        }
                    }
                });
                return redirect('/')->with('status',  Lang::choice('website_keywords.contact_success',\App::getLocale()));
            }
        }
    }

    public function deleteContactRequest($req_id) {
        $contact_request = ContactRequest::find($req_id);

        if ($contact_request) {
            $directory = 'public/contact-requests/' . $contact_request->reference_no;

            $contact_request->delete();
            // delete associated files from storage
            Storage::deleteDirectory($directory);

            return redirect("admin/contact-requests")->with('status', 'Request deleted successfully!');
        } else {
            return redirect('admin/contact-requests');
        }
    }

    public function deleteSelectedContactRequest($req_id) {
        $contact_request = ContactRequest::find($req_id);

        if ($contact_request) {
            $directory = 'public/contact-requests/' . $contact_request->reference_no;

            $contact_request->delete();
            // delete associated files from storage
            Storage::deleteDirectory($directory);
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function viewContactRequest($reference_no) {
        $contact_request = ContactRequest::where('reference_no', $reference_no)->get()->first();

        if ($contact_request) {
            $contact_email = GlobalSetting::where('slug', 'contact-email')->first();
            return view('contactrequest::view', array('request' => $contact_request, 'contact_email' => $contact_email));
        } else {
            return redirect('admin/contact-requests');
        }
    }

    public function postReply(Request $request, $reference_no) {

        $contact_request = ContactRequest::where('reference_no', $reference_no)->get()->first();

        if ($contact_request) {


            // validate request
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'email' => 'required|email',
                        'subject' => 'required',
                        'message' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect('admin/contact-request/' . $reference_no)->withErrors($validate_response)->withInput();
            } else {

                $arr_request_data = array();

                $arr_request_data["reply_subject"] = $request->subject;
                $arr_request_data["reply_email"] = $request->email;
                $arr_request_data["from_user_id"] = Auth::user()->id;
                $arr_request_data["reply_message"] = $request->message;
                $arr_request_data["contact_request_id"] = $contact_request->id;
                //updating contact request is reply flag
                $contact_request->is_reply = 1;
                $contact_request->save();

                $attachments = array();

                if ($request->hasFile('attachment')) {
                    $uploaded_files = $request->file('attachment');

                    foreach ($uploaded_files as $uploaded_file) {

                        $new_file_name = $uploaded_file->getClientOriginalName();

                        Storage::put('public/contact-requests/' . $reference_no . "/" . $new_file_name, file_get_contents($uploaded_file->getRealPath()));
                        $attachments[] = $new_file_name;
                    }

                    $arr_request_data["reply_attachment"] = $attachments;
                }

                ContactRequestReply::create($arr_request_data);
                $site_email=GlobalValues::get('site-email');
                $site_title=GlobalValues::get('site-title');
                $arr_keyword_values = array();
                $arr_keyword_values['MESSAGE'] = $request->message;
                $arr_keyword_values['SITE_TITLE'] =$site_title;

                Mail::send("emailtemplate::contact-request-reply", $arr_keyword_values, function ($message) use ($request, $attachments, $reference_no, $contact_request) {

                    $message->to($contact_request->contact_email)->subject($request->subject)->from($request->email);

                    if (count($attachments) > 0) {

                        $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix() . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "contact-requests" . DIRECTORY_SEPARATOR . $reference_no . DIRECTORY_SEPARATOR;

                        foreach ($attachments as $attachment) {
                            $pathToFile = $storagePath . $attachment;
                            $message->attach($pathToFile);
                        }
                    }
                });


                return redirect('admin/contact-requests/')->with('status', 'Reply posted successfully!');
            }
        } else {
            return redirect('admin/contact-requests');
        }
    }

    private function generateReferenceNumber() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    public function listContactCategories() {

        return view('contactrequest::list-categories');
    }

    public function listContactCategoriesData() {

        $all_categories = ContactRequestCategory::translatedIn(\App::getLocale())->get();
        return Datatables::of($all_categories)
                        //return Datatables::collection($all_states)->make(true);
                        ->addColumn('Language', function($category) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language.='<li class="dropdown-item"> <a href="contact-request-categories/update-language/' . $category->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })
                        ->make(true);
    }

    public function createContactCategories(Request $request) {
        if ($request->method() == "GET") {
            return view("contactrequest::create-category");
        } else {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'name' => 'required',
            ));

            if ($validate_response->fails()) {
                return redirect($request->url())->withErrors($validate_response)->withInput();
            } else {
                $created_category = ContactRequestCategory::create(array('created_by' => Auth::user()->id));

                $translated_category = $created_category->translateOrNew(\App::getLocale());
                $translated_category->name = $request->name;
                $translated_category->locale = \App:: getLocale();
                $translated_category->contact_request_category_id = $created_category->id;
                $translated_category->save();

                return redirect("admin/contact-request-categories")->with('status', 'Category created successfully!');
            }
        }
    }

    public function updateContactCategory(Request $request, $category_id, $locale = "") {
        $category = ContactRequestCategory::find($category_id);

        if ($category) {
            $translated_category = $category->translateOrNew($locale);

            if ($request->method() == "GET") {
                return view("contactrequest::update-category", array('category' => $translated_category));
            } else {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {

                    $translated_category->name = $request->name;

                    if ($locale != '') {
                        $translated_category->contact_request_category_id = $category->id;
                        $translated_category->locale = $locale;
                    }

                    $translated_category->save();

                    return redirect("admin/contact-request-categories")->with('status', 'Category updated successfully!');
                }
            }
        } else {
            return redirect('admin/contact-request-categories');
        }
    }

    public function updateContactCategoryLanguage(Request $request, $category_id, $locale = "") {
        $category = ContactRequestCategory::find($category_id);

        if ($category) {
            $translated_category = $category->translateOrNew($locale);

            if ($request->method() == "GET") {
                return view("contactrequest::update-category", array('category' => $translated_category));
            } else {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'name' => 'required',
                ));

                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {

                    $translated_category->name = $request->name;

                    if ($locale != '') {
                        $translated_category->contact_request_category_id = $category->id;
                        $translated_category->locale = $locale;
                    }

                    $translated_category->save();

                    return redirect("admin/contact-request-categories")->with('status', 'Category updated successfully!');
                }
            }
        } else {
            return redirect('admin/contact-request-categories');
        }
    }

    public function deleteContactCategory($category_id) {
        $category = ContactRequestCategory::find($category_id);

        if ($category) {
            $category->delete();
            return redirect("admin/contact-request-categories")->with('status', 'Category deleted successfully!');
        } else {
            return redirect('admin/contact-request-categories');
        }
    }

    public function deleteSelectedContactCategory($category_id) {
        $category = ContactRequestCategory::find($category_id);

        if ($category) {
            $category->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

}
