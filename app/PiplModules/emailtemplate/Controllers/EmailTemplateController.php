<?php
namespace App\PiplModules\emailtemplate\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PiplModules\emailtemplate\Models\EmailTemplate;
use Validator;
use Datatables;

class EmailTemplateController extends Controller
{


	public function index()
	{
            
		return view("emailtemplate::list");
	}
        
        public function getEmailTemplateData()
	{

		$all_templates = EmailTemplate::all();
                
                return Datatables::of($all_templates)
                ->addColumn('template_key', function($email_page){
                     return ucwords(str_replace("-"," ",($email_page->template_key)));
                })     
               ->make(true);
	}
        
	
	public function showUpdateEmailTemplateForm(Request $request,$template_id)
	{
	
		$email_template = EmailTemplate::find($template_id);
		
		if($email_template)
		{
			if($request->method() == "GET" )
			{
				return view("emailtemplate::edit",["template_info"=>$email_template]);
			}
			else
			{
                            $data = $request->all();
                            $validate_response = Validator::make($data, array(
                                        'subject' => 'required',
                                        'html_content' => 'required',

                                ));
						
                                if($validate_response->fails())
                                {
                                    return redirect($request->url())->withErrors($validate_response)->withInput();
                                }else{
				// update it				
				$email_template->subject = ($request->input('subject'));
				$email_template->html_content =($request->input('html_content'));				
				$email_template->save();
				
				// create view
				$view_location = __DIR__. "/../Views/".$email_template->template_key.".blade.php";
				
				$view_resource = fopen($view_location ,"w+");
				fwrite($view_resource,$email_template->html_content);
				fclose($view_resource);
				
				return redirect("/admin/email-templates/list")->with('status','Email contents has been updated Successfully!');
                                }
			}
		}
		else
		{
			return redirect("/admin/email-templates/list");
		}
		
		
	}
	
}