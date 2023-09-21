<?php
namespace App\PiplModules\faq\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\faq\Models\Faq;
use App\PiplModules\faq\Models\FaqCategory;
use Mail;
use Datatables;

class FaqController extends Controller
{


	public function index()
	{
		
		return view("faq::list");
		
	}
	public function getFaqData()
	{

		$all_faqs = Faq::translatedIn(\App::getLocale())->get();
                 return Datatables::of($all_faqs)
                   ->addColumn('Language', function($faq){
                     $language='<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                    if(count(config("translatable.locales_to_display")))
                    {
                     foreach(config("translatable.locales_to_display") as $locale=>$locale_full_name)
                     {
                          if($locale != 'en')
                          {
                            $language.='<li class="dropdown-item"> <a href="faq/'.$faq->id.'/'.$locale.'">'.$locale_full_name.'</a></li>';
                          }
                     }
                    }
                    return $language;
                   })       
                   ->addcolumn('category',function($faq)
                     {
                           if($faq->category) 
                           {
                               return $faq->category->name;
                               
                           }else{
                               return "-";
                           }
                     })
                    ->make(true);
		
	}
       
	
	public function deleteFaq($faq_id)
	{
		$faq = Faq::find($faq_id);
		
		if($faq)
		{
			
			$faq->delete();
			
			return redirect("admin/faqs")->with('status','FAQ deleted successfully!');
		}
		else
		{
			return redirect('admin/faqs');
		}
	}
	public function deleteSelectedFaq($faq_id)
	{
		$faq = Faq::find($faq_id);
		
		if($faq)
		{
			
			$faq->delete();
			 echo json_encode(array("success"=>'1','msg'=>'Selected records has been deleted successfully.'));
		}
		else
		{
                    echo json_encode(array("success"=>'0','msg'=>'There is an issue in deleting records.'));
		}
	}
	
	public function updateFaq(Request $request, $faq_id,$locale="")
	{
		$faq = Faq::find($faq_id);
		
		if($faq )
		{
			$translated_faq = $faq->translateOrNew($locale);

			if($request->method() == "GET")
			{
				$existing_categories = FaqCategory::withTranslation()->get();
						
				$tree = $this->getCategoryTree($existing_categories->toTree(),'&nbsp;&nbsp;');

				return view('faq::update',array("faq_main"=>$faq,'faq'=>$translated_faq,'tree'=>$tree,'locale'=>$locale,'category'=>$faq->faq_category_id));
			}
			else
			{

                                    // do validation and update the faq
				    $data = $request->all();
                                    $validate_response = Validator::make($data, 

                                        array(
                                                        'question' => 'required',
                                                        'answer' => 'required',
                                                 )
                                        );
						
                                    if($validate_response->fails())
                                    {
                                            return redirect($request->url())->withErrors($validate_response)->withInput();
                                    }
                                    else
                                    {
							

                                            if($locale != "")
                                            {
                                                    $translated_faq->faq_id = $faq_id;
                                                    $translated_faq->locale = $locale;
                                            }
                                            else
                                            {
                                                if($request->category=='0')
                                                {
                                                    $faq->faq_category_id = NULL;
                                                }else{
                                                    $faq->faq_category_id = $request->category;
                                                }
                                                    $faq->faq_type=$request->faq_type;
                                                    $faq->save();
                                            }


                                            $translated_faq->question = $request->question;
                                            $translated_faq->answer = $request->answer;
                                            $translated_faq->save();

                                            return redirect('admin/faqs')->with('status','FAQ updated successfully!');
                                }
			}
		}
		else
		{
			return redirect('admin/faqs');
		}
	}

	public function createFaq(Request $request)
	{

		if($request->method()=="GET")
			{
				$existing_categories = FaqCategory::withTranslation()->get();
						
				$tree = $this->getCategoryTree($existing_categories->toTree(),'&nbsp;&nbsp;');
				return view("faq::create",array('tree'=>$tree));	
			}
			else
			{
							$data = $request->all();
							$validate_response = Validator::make($data, 

                                                        array(
                                                                        'question' => 'required',
                                                                        'answer' => 'required',
                                                                 )
                                                        );

						if($validate_response->fails())
						{
							return redirect($request->url())->withErrors($validate_response)->withInput();
						}
						else
						{
							$created_faq = Faq::create(array('created_by'=>Auth::user()->id, 'faq_type'=>$request->faq_type,'faq_category_id'=>$request->category));

							$translated_faq = $created_faq->translateOrNew(\App::getLocale());
							$translated_faq->question = $request->question;
							$translated_faq->answer = $request->answer;
							$translated_faq->locale =\App:: getLocale();
							$translated_faq->faq_id =$created_faq->id;
							$translated_faq->save();

							return redirect("admin/faqs")->with('status','FAQ created successfully!');

						}
			}
	}
	
	
	
	public function listFaqCategories()
	{
		
		
		return view('faq::list-categories');
		
	}
	public function listFaqCategoriesData()
	{

		$all_categories = FaqCategory::translatedIn(\App::getLocale())->get();
               return Datatables::of($all_categories)
                     ->addcolumn('parent',function($category)
                     {
                           if($category->parentCat) 
                           {
                               return $category->parentCat->translate()->name;
                               
                           }else{
                               return "-";
                           }
                     })
                   
                    ->make(true);
		
	}
	public function createFaqCategories(Request $request)
	{
			if($request->method()=="GET")
			{
				$existing_categories = FaqCategory::withTranslation()->get();
						
				$tree = $this->getCategoryTree($existing_categories->toTree(),'&nbsp;&nbsp;');
				
				return view("faq::create-category",array('tree'=>$tree));	
			}
			else
			{
							$data = $request->all();
							$validate_response = Validator::make($data,
                                                      array(
                                                        'name' => 'required',
                                                        'cat_url' => 'required|unique:faq_categories',

							),
                                                        array(
                                                        'name.required' => 'Please enter name (eg:- mycategory)',
                                                        'cat_url.required' => 'Please enter a url (eg:- my-category)',
                                                        'cat_url.unique' => 'This url has been taken already'
							)
                                                      );
						
						if($validate_response->fails())
						{
									return redirect($request->url())->withErrors($validate_response)->withInput();
						}
						else
						{
							$parent_cat = FaqCategory::find($request->parent_id);
							$created_category = FaqCategory::create(array('created_by'=>Auth::user()->id,'parent_id'=>$request->parent_id,'cat_url'=>str_slug($request->cat_url)));
							$translated_category = $created_category->translateOrNew(\App::getLocale());
							$translated_category->name = $request->name;
							$translated_category->locale =\App:: getLocale();
							$translated_category->faq_category_id =$created_category->id;
							$translated_category->save();
							
							return redirect("admin/faq-categories")->with('status','Category created successfully!');
						}
			}
	}
	
	public function updateFaqCategory(Request $request,$category_id,$locale="")
	{

			$category = FaqCategory::find($category_id);
			
			if($category)
			{
					$translated_category = $category->translateOrNew($locale);
				
					if($request->method()=="GET")
					{
						$existing_categories = FaqCategory::withTranslation()->where('id','<>',$category_id)->get();
						
						$tree = $this->getCategoryTree($existing_categories->toTree(),'&nbsp;&nbsp;');
						
						return view("faq::update-category",array('main_cat_table'=>$category,'category'=>$translated_category,'categories'=>$existing_categories,'parent_id'=>$category->parent_id,'locale'=>$locale,'tree'=>$tree));	
					}
					else
					{
                                                        $data = $request->all();
                                                       $validate_response = Validator::make($data,
                                                        array(
                                                          'name' => 'required',
                                                          'cat_url' => 'required',

                                                          ),
                                                          array(
                                                          'name.required' => 'Please enter name (eg:- mycategory)',
                                                          'cat_url.required' => 'Please enter a url (eg:- my-category)'
                                                          )
                                                      );
								
								if($validate_response->fails())
								{
											return redirect($request->url())->withErrors($validate_response)->withInput();
								}
								else
								{
									
                                                                        if($request->parent_id=="0")
                                                                        {
                                                                            $category->rawNode(100,100,null);
                                                                            $category->cat_url=str_slug($request->cat_url);
                                                                            $category->save();
                                                                        }
                                                                        else
                                                                        {
									$category->parent_id = $request->parent_id;
                                                                        $category->cat_url=str_slug($request->cat_url);
									$category->save();
                                                                        }
									$translated_category->name = $request->name;
									
									if($locale!='')
									{
										$translated_category->faq_category_id =$category->id;
										$translated_category->locale =$locale;
									}
									
									$translated_category->save();
									
									return redirect("admin/faq-categories")->with('status','Category updated successfully!');
								}
					}
			}
			else
			{
				return redirect('admin/faq-categories');
			}
	}
	
	public function deleteFaqCategory($category_id)
	{
		
		$category = FaqCategory::find($category_id);
		if($category)
		{
			$category->delete();
			return redirect("admin/faq-categories")->with('status','Category deleted successfully!');
		}
		else
		{
			return redirect('admin/faq-categories');
		}
	}
	public function deleteSelectedFaqCategory($category_id)
	{
		
		$category = FaqCategory::find($category_id);
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

	public function viewFaqs()
	{
		$faqs = Faq::translatedIn(\App::getLocale())->get();
                $existing_categories = FaqCategory::withTranslation()->get();
                $tree = $this->getCategoryTree($existing_categories->toTree(),'&nbsp;&nbsp;');    
		return view('faq::faqs',array('faqs'=>$faqs,'tree'=>$tree));
	}
	public function viewFaqsBycategory($cat_url)
	{
          
            if(isset($cat_url))
            {
                $cat=FaqCategory::where('cat_url',$cat_url)->first();
                if(isset($cat->id))
                {
                   $faqs = Faq::where('faq_category_id',$cat->id)->translatedIn(\App::getLocale())->get();
                }
                
            }else{
                $faqs = Faq::translatedIn(\App::getLocale())->get();
            }
            
            //to get all categories
            $existing_categories = FaqCategory::withTranslation()->get();
            $tree = $this->getCategoryTree($existing_categories->toTree(),'&nbsp;&nbsp;');  
            // 
            
            return view('faq::faqs',array('faqs'=>$faqs,'tree'=>$tree));
	}
	
	private function getCategoryTree($nodes,$prefix="-") 
	{
		$arr_cats = array();
		$traverse = function ($categories, $prefix) use (&$traverse, &$arr_cats ) {
			
				foreach ($categories as $category) {
					
				
					$arr_cats[] = new categoryTreeHolder($prefix.' '.$category->name,$category->id,$category->cat_url);
					
					$traverse($category->children, $prefix.$prefix);
					
				}
			};
			
			$traverse($nodes,$prefix);
			
			return $arr_cats;
	}
	
}

class categoryTreeHolder{
	
	public $display='';
	public $id='';
        public $cat_url='';
	
	public function __construct($display,$id,$url)
	{
		$this->id = $id;
		$this->display = $display;
		$this->cat_url = $url;
	}
}