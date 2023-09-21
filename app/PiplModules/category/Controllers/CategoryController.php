<?php
namespace App\PiplModules\category\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\category\Models\Category;
use Mail;
use Datatables;
class CategoryController extends Controller
{

      public function __construct() {
         \App::setLocale('en');
        //  $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }
	public function listCategories()
	{
		
		$all_categories = Category::translatedIn(\App::getLocale())->get();
		
		return view('category::list-categories',array('categories'=>$all_categories));
		
	}
	public function listCategoriesData()
	{
		$all_categories = Category::where('status','1')->translatedIn(\App::getLocale())->get();
		return Datatables::of($all_categories)
                ->addColumn('Language', function($category){
                     $language='<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                    if(count(config("translatable.locales_to_display")))
                    {
                     foreach(config("translatable.locales_to_display") as $locale=>$locale_full_name)
                     {
                          if($locale != 'en')
                          {
                            $language.='<li class="dropdown-item"> <a href="category/'.$category->id.'/'.$locale.'">'.$locale_full_name.'</a></li>';
                          }
                     }
                    }
                    return $language;
                 })
                ->make(true);
		
	}
	
	public function createCategories(Request $request)
	{
			if($request->method()=="GET")
			{
				return view("category::create-category");	
			}
			else
			{
							$data = $request->all();
							$validate_response = Validator::make($data, array(
																				'name' => 'required',
																				
							));
						
						if($validate_response->fails())
						{
									return redirect($request->url())->withErrors($validate_response)->withInput();
						}
						else
						{
							$created_category = Category::create(array('created_by'=>Auth::user()->id));
							
							$translated_category = $created_category->translateOrNew(\App::getLocale());
							$translated_category->name = $request->name;
							$translated_category->locale =\App:: getLocale();
							$translated_category->category_id =$created_category->id;
							$translated_category->save();
							
							return redirect("admin/categories-list")->with('status','Category created successfully!');
						}
			}
	}
	
	public function updateCategory(Request $request,$category_id,$locale="")
	{
			$category = Category::find($category_id);
			
			if($category)
			{
					$translated_category = $category->translateOrNew($locale);
				
					if($request->method()=="GET")
					{
                                            
                                            if($locale!='en' && $locale!='')
                                            {
                                                return view("category::update-language-category",array('category'=>$translated_category,'main_info'=>$category));	
                                            }else{
                                              
						return view("category::update-category",array('category'=>$translated_category,'main_info'=>$category));	
                                            }
					}
					else
					{
                                                                $data = $request->all();
                                                                if($locale!='en')
                                                                {
                                                                    $validate_response = Validator::make($data, array(
                                                                        'name' => 'required'

                                                                    ));
                                                                }else{
                                                                    $validate_response = Validator::make($data, array(
                                                                     'name' => 'required',
                                                                     'category_type' => 'required',

                                                                    ));
                                                                }
								
								if($validate_response->fails())
								{
									return redirect($request->url())->withErrors($validate_response)->withInput();
								}
								else
								{
									if($request->is_drop_location!='')
                                                                        {
                                                                           
                                                                            $category->is_drop_location = $request->is_drop_location;
                                                                            $category->number_of_person = $request->number_of_person;
                                                                            $category->request_range = $request->request_range;
                                                                            $category->save();
                                                                        }
									$translated_category->name = $request->name;
									$translated_category->description = $request->description;
									if($locale!='')
									{
										$translated_category->category_id =$category->id;
										$translated_category->locale =$locale;
									}
									
									$translated_category->save();
									
									return redirect("admin/categories-list")->with('status','Category updated successfully!');
								}
					}
			}
			else
			{
				return redirect('admin/categories-list');
			}
	}
	
	public function deleteCategory($category_id)
	{
		$category = Category::find($category_id);
		
		if($category)
		{
			$category->delete();
			return redirect("admin/categories-list")->with('status','Category deleted successfully!');
		}
		else
		{
			return redirect('admin/categories-list');
		}
	}
	public function deleteSelectedCategory($category_id)
	{
		$category = Category::find($category_id);
		
		if($category)
		{
			$category->delete();
			 echo json_encode(array("success"=>'1','msg'=>'Selected records has been deleted successfully.'));
		}
		else
		{
			 echo json_encode(array("success"=>'0','msg'=>'There is an issue in deleting records.'));
		}
	}
	
}