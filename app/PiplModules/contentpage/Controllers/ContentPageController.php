<?php

namespace App\PiplModules\contentpage\Controllers;
use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Datatables;

use App\PiplModules\contentpage\Models\ContentPage;


class ContentPageController extends Controller
{


	public function index()
	{

		return view("contentpage::list");
		
	}
	public function cmsPageDataAdmin()
	{

		$all_pages = ContentPage::translatedIn(\App::getLocale())->get();
		
                 return Datatables::of($all_pages)
                ->addColumn('page_alias', function($cms_page){
                     return url('').'/'.$cms_page->page_alias;
                })
                 ->addColumn('Language', function($country){
                     $language='<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                    if(count(config("translatable.locales_to_display")))
                    {
                     foreach(config("translatable.locales_to_display") as $locale=>$locale_full_name)
                     {
                          if($locale != 'en')
                          {
                            $language.='<li class="dropdown-item"> <a href="update-language/'.$country->id.'/'.$locale.'">'.$locale_full_name.'</a></li>';
                          }
                     }
                    }
                    return $language;
                 })
		  ->make(true);
		
	}
	
	public function showUpdateContentPageForm(Request $request,$page_id)
	{
           
                $content_page = ContentPage::find($page_id);
		
		if($content_page)
		{
					
			$page_information = $content_page->translate();
			
			if($request->method() == "GET" )
			{
				return view("contentpage::edit",["page"=>$content_page,"page_information"=>$page_information]);
			}
			else
			{
				
				// validate request
					$data = $request->all();
					$validate_response = Validator::make($data, [
                                            'page_alias' => 'required|unique:content_pages,page_alias,'.$content_page->id,
                                            'page_title' => 'required',
                                            'page_content' => 'required',
                                            'page_seo_title' => 'required',
                                            'page_meta_keywords' => 'required',
                                            'page_meta_descriptions' => 'required'
                                            

				]);
				
				if($validate_response->fails())
				{
							return redirect($request->url())->withErrors($validate_response)->withInput();
				}
				else
				{
					
					$page_information->page_title = $request->page_title;
					$page_information->page_content = $request->page_content;
					$page_information->page_seo_title = $request->page_seo_title;
					$page_information->page_meta_keywords = $request->page_meta_keywords;
					$page_information->page_meta_descriptions = $request->page_meta_descriptions;
					$page_information->save();
					$content_page->page_alias = $request->page_alias;
					$content_page->created_by = Auth::user()->id;
					$content_page->page_status = $request->page_status;
					$content_page->save();
					
					return redirect('admin/content-pages/list')->with('status','Content pages has been updated Successfully!');
				}
				
			}
		}
		else
		{
			return redirect("admin/content-pages/list");
		}
		
	}
	
	public function showUpdateContentPageLanguageForm(Request $request,$page_id,$locale)
	{
		
		$content_page = ContentPage::find($page_id);
		
		if($content_page)
		{
			
					$is_new_entry = !($content_page->hasTranslation($locale));
					
					$translated_page = $content_page->translateOrNew($locale);
					
			
					if($request->method() == "GET" )
					{
					
						return view("contentpage::edit-language-content",["page"=>$content_page,"page_information"=>$translated_page]);
				
					}
					else
					{
						
						// validate request
							$data = $request->all();
							$validate_response = Validator::make($data, [
																			'page_title' => 'required',
																			'page_content' => 'required'
																			
						]);
						
						if($validate_response->fails())
						{
									return redirect($request->url())->withErrors($validate_response)->withInput();
						}
						else
						{
							
								$translated_page->page_title = $request->page_title;
								$translated_page->page_content = $request->page_content;
								$translated_page->page_seo_title = $request->page_seo_title;
								$translated_page->page_meta_keywords = $request->page_meta_keywords;
								$translated_page->page_meta_descriptions = $request->page_meta_descriptions;
								
								if($is_new_entry)
								{
									$translated_page->content_page_id = $page_id;
								}
								
								$translated_page->save();
								
							return redirect($request->url())->with('status','Updated Successfully!');
							
						}
						
					}
		}
		else
		{
			return redirect("admin/content-pages/list");
		}
		
	
	}
	
	public function createContentPage(Request $request)
	{
	
			if($request->method() == "GET" )
			{
				return view("contentpage::create");
			}
			else
			{
				
				// validate request
					$data = $request->all();
					$validate_response = Validator::make($data, [
                                            'page_alias' => 'required|unique:content_pages,page_alias',
                                            'page_title' => 'required',
                                            'page_content' => 'required'

				]);
				
				if($validate_response->fails())
				{
							return redirect($request->url())->withErrors($validate_response)->withInput();
				}
				else
				{
					
					$created_page = ContentPage::create(array("page_alias"=>$request->page_alias,'created_by'=> Auth::user()->id,'page_status'=>$request->page_status));
					
					$translated_page = $created_page->translateOrNew(\App::getLocale());
					
					$translated_page->page_title = $request->page_title;
					$translated_page->page_content = $request->page_content;
					$translated_page->page_seo_title = $request->page_seo_title;
					$translated_page->page_meta_keywords = $request->page_meta_keywords;
					$translated_page->page_meta_descriptions = $request->page_meta_descriptions;
					$translated_page->content_page_id = $created_page->id;
					$translated_page->locale =\App:: getLocale();
					
					$translated_page->save();
					
					return redirect("admin/content-pages/list")->with('status','Page created successfully!');
					
				}
				
			}
		
	}
	
	public function deleteContentPage(Request $request, $page_id)
	{
			$content_page = ContentPage::find($page_id);
			
			if($content_page)
			{
				$content_page->delete();
				return redirect("admin/content-pages/list")->with('status','Page deleted successfully!');
			}
			else
			{
				return redirect("admin/content-pages/list");
			}
			
	}
	
	
	public function findAndShowPageAccordingToSlug($slug)
	{
			
			$page = ContentPage::where('page_alias',$slug)->first();
			if($page && $page->page_status)
			{
				$page_information =  $page->translateOrDefault(\App::getLocale());
			
				return view('contentpage::view',array('page'=>$page,'page_information'=>$page_information));
			}
			else
			{
				abort(404);
			}
			
	}
	
}