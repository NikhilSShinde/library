<?php
namespace App\PiplModules\testimonial\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\PiplModules\testimonial\Models\Testimonial;
use Storage;
use Datatables;
class TestimonialController extends Controller
{

	private $placeholder_img;

	public function __construct()
	{

		$this->placeholder_img = asset('media/front/img/avatar-placeholder.svg');
	}

	public function index()
	{

		return view("testimonial::list");
		
	}
        
        public function getTestimonialData()
	{

		$all_testimonials = Testimonial::all();
		
                return DataTables::of($all_testimonials)
                    ->addColumn('status', function($testimonial){
                     return ($testimonial->status>0)? 'Published': 'Unpublished';
                    })
                        ->make(true);
		return view("testimonial::list",["testimonials"=>$all_testimonials,'placeholder'=>$this->placeholder_img]);
		
	}
        
	
	public function showUpdateTestimonialPageForm(Request $request,$id)
	{
	

		$testimonial = Testimonial::find($id);
		
		if($testimonial)
		{
					
			
			if($request->method() == "GET" )
			{
				return view("testimonial::edit",["testimonial"=>$testimonial,'placeholder'=>$this->placeholder_img]);
			}
			else
			{
				
				// validate request
					$data = $request->all();
					$validate_response = Validator::make($data, [
																	'name' => 'required',
																	'user_description' => 'required',
																	'photo' => 'sometimes|image',
																	'description' => 'required'
																	
				]);
				
				if($validate_response->fails())
				{
							return redirect($request->url())->withErrors($validate_response)->withInput();
				}
				else
				{
					
					$testimonial->name = $request->name;
					$testimonial->user_description = $request->user_description;
					$testimonial->description = $request->description;
					$testimonial->status = $request->publish_status;


					if($request->hasFile('photo'))
					{
						
						$uploaded_file = $request->file('photo');
						
						
						$extension = $uploaded_file->getClientOriginalExtension();
							
						$new_file_name = time().".".$extension;
				
						Storage::put('public/testimonials/'.$new_file_name,file_get_contents($uploaded_file->getRealPath()));

						$testimonial->photo = $new_file_name;
						
					}

					$testimonial->save();
					
					return redirect("admin/testimonials/list")->with('status','Testimonial updated successfully!');
				}
				
			}
		}
		else
		{
			return redirect("admin/testimonials/list");
		}
		
	}
	
	
	
	public function createTestimonial(Request $request)
	{
	
			if($request->method() == "GET" )
			{
				return view("testimonial::create");
			}
			else
			{
				
				// validate request
					$data = $request->all();
					$validate_response = Validator::make($data, [
                                        'name' => 'required',
                                        'user_description' => 'required',
                                        'photo' => 'sometimes|image|mimes:jpg,png,gif,jpeg',
                                        'description' => 'required'

					]);
				
				if($validate_response->fails())
				{
                                    return redirect($request->url())->withErrors($validate_response)->withInput();
				}
				else
				{
					
					$created_testimonial = Testimonial::create(array("name"=>$request->name,'user_description'=>$request->user_description,'created_by'=>Auth::user()->id,'description'=>$request->description,'status'=>$request->publish_status));

					if($request->hasFile('photo'))
					{
						
						$uploaded_file = $request->file('photo');
						
						
						$extension = $uploaded_file->getClientOriginalExtension();
							
						$new_file_name = time().".".$extension;
				
						Storage::put('public/testimonials/'.$new_file_name,file_get_contents($uploaded_file->getRealPath()));

						$created_testimonial->photo = $new_file_name;
						$created_testimonial->save();
						
					}

					
					
					return redirect("admin/testimonials/list")->with('status','Testimonial created successfully!');
					
				}
				
			}
		
	}
	
	public function deleteTestimonial(Request $request, $id)
	{
			$testimonial = Testimonial::find($id);
			
			if($testimonial)
			{
				$testimonial->delete();
				return redirect("admin/testimonials/list")->with('status','Testimonial deleted successfully!');
			}
			else
			{
				return redirect("admin/testimonials/list");
			}
			
	}
	public function deleteSelectedTestimonial(Request $request, $id)
	{
			$testimonial = Testimonial::find($id);
			if($testimonial)
			{
				$testimonial->delete();
				echo json_encode(array("success"=>'1','msg'=>'Selected records has been deleted successfully.'));
			}
			else
			{
				 echo json_encode(array("success"=>'0','msg'=>'There is an issue in deleting records.'));
			}
			
	}
	
	
}