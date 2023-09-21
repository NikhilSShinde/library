<?php
namespace App\PiplModules\newsletter\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PiplModules\newsletter\Models\Subscriber;
use App\PiplModules\newsletter\Models\Newsletter;
use App\PiplModules\newsletter\SendNewsletterEmail;
use Validator;
use Datatables;
class NewsletterController extends Controller
{
	public function index()
	{
		return view("newsletter::index");
	}
	
	public function subscribeToNewsLetter(Request $req)
	{
		 if($req->ajax())
		 {
                                $validation = Validator::make($req->all(), array(
                                                                                                                                                                                                                                 'email' => 'required|email|unique:subscribers,email'
                                                                                                                                                                                                                       )
                                        );
                                       if($validation->fails())
                                       {
                                               return $validation->errors()->first();
                                       }
                                       else
                                       {
                                        $create = Subscriber::create(array(
                                                         'email' => $req->email
                                          )
                                         );

                                         return $create?'1':'We could not save your address to our system, please try again later';

                                    }
                    }
		 else
		 {
			 return redirect(url('newsletter'));
		  }
	}
	
	public function createNewsletter(Request $request)
	{
			if($request->method()=="GET")
			{
				return view("newsletter::create");
			}
			elseif($request->method()=="POST")
			{
				 $validation = Validator::make($request->all(), array(
																													  'subject' => 'required',
																													  'content'=>'required',
																													  'status' => 'required'
																													)
																		 );
						if($validation->fails())
						{
							return redirect($request->url())->withErrors($validation)->withInput();
						}
						else
						{
								$arr_newsletter_data = array();
								$arr_newsletter_data['subject'] = $request->subject;
								$arr_newsletter_data['content'] = $request->content;
								$arr_newsletter_data['status'] = $request->status;
								
								$created_newsletter = Newsletter::create(	$arr_newsletter_data );
								
								if($created_newsletter)
								{
									
									// create view
									$view_location = __DIR__. "/../Views/".$created_newsletter->id.".blade.php";
									
									$view_resource = fopen($view_location ,"w+");
									fwrite($view_resource,$created_newsletter->content);
									fclose($view_resource);
									
									return redirect(url('admin/newsletters'))->with("status","Newsletter created successfully");
								}
								else
								{
									return redirect($request->url())->with("error","Something goes wrong!!! Please try again")->withInput();
								}
								
						}
			}
	}
	
	public function listNewsletters()
	{
		$all_news_letters = Newsletter::all();
		
		return view("newsletter::list",array("newsletters"=>$all_news_letters));
	}
	public function listNewslettersData()
	{
                 $all_news_letters = Newsletter::all();
                 return Datatables::of($all_news_letters)
               
                ->make(true);
		
		
		//return view("newsletter::list",array("newsletters"=>$all_news_letters));
	}
	
	public function updateNewsletter(Request $request,$newsletter_id)
	{
			$newsletter = Newsletter::find($newsletter_id);
			if($request->method()=="GET")
			{
				if($newsletter)
				{
					return view("newsletter::update",array("newsletter"=>$newsletter));
				}
				else
				{
					return redirect('admin/newsletters');
				}
			}
			elseif($request->method()=="POST")
			{
				if($newsletter)
				{
					 $validation = Validator::make($request->all(), array(
																													  'subject' => 'required',
																													  'content'=>'required',
																													  'status' => 'required'
																													)
																													);
						if($validation->fails())
						{
							return redirect($request->url())->withErrors($validation)->withInput();
						}
						else
						{
							$newsletter->subject = $request->subject;
							$newsletter->content = $request->content;
							$newsletter->status = $request->status;
							$newsletter->save();
							
							// create view
							$view_location = __DIR__. "/../Views/".$newsletter->id.".blade.php";
							
							$view_resource = fopen($view_location ,"w+");
							fwrite($view_resource,$newsletter->content);
							fclose($view_resource);
							return redirect(url('admin/newsletters'))->with("status","Newsletter Updated successfully");
							
						}
				}
				else
				{
					return redirect('admin/newsletters');
				}
			}
	}
	
	public function deleteNewsLetter(Request $request, $newsletter_id)
	{
		if( $request->method() == 'DELETE')
		{
			$newsletter = Newsletter::find($newsletter_id);
			
			if($newsletter)
			{
				$view_location = __DIR__. "/../Views/".$newsletter->id.".blade.php";
				@unlink($view_location);
				$newsletter->delete();
				return redirect('admin/newsletters')->with("status","Newsletter deleted successfully!");
			}
			
		}
	}
	
	
	public function distributeNewsLetters($newsletter_id)
	{
		$newsletter = Newsletter::find($newsletter_id);
		
		if($newsletter)
		{
                        $subscribers = Subscriber::all();
                        $queued_newsletter =(new SendNewsletterEmail($newsletter,$subscribers))->delay(60);
                        $this->dispatch($queued_newsletter);
                        return redirect('admin/newsletters')->with("status","Newsletter queued successfully!");
		}
		
	}
}