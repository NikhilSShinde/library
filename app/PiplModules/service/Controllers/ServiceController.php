<?php

namespace App\PiplModules\service\Controllers;

use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\service\Models\Service;
use App\PiplModules\category\Models\Category;
use Mail;
use Datatables;

class ServiceController extends Controller {

     public function __construct()
    {
          $this->middleware('auth');
          \App::setLocale('en');
     }
    public function listServices() {

        $all_services = Service::translatedIn(\App::getLocale())->get();
        return view('service::list-services', array('services' => $all_services));
    }

    public function listServicesData() {

        $all_services = Service::translatedIn(\App::getLocale())->get();
        $all_services=$all_services->reject(function ($service)
        {
          
            return ($service->categoryInfo->status=='0');
        });
        return Datatables::of($all_services)
                        ->addColumn('Language', function($service) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language.='<li class="dropdown-item"> <a href="service/' . $service->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })
                        ->addColumn('category', function($service) {

                            if (isset($service->categoryInfo)) {
                                return ($service->categoryInfo->translate()->name);
                            } else {
                                return "";
                            }
                        })
                        ->addColumn('main_service', function($service) {

                            if (isset($service->serviceInfo)) {
                                return ($service->serviceInfo->name);
                            } else {
                                return "--";
                            }
                        })
                        ->make(true);
    }

    public function createService(Request $request) {
        if ($request->method() == "GET") {
            $arr_categories = Category::translatedIn(\App::getLocale())->where('status','1')->get();
            $services = Service::translatedIn(\App::getLocale())->where('parent_id',0)->get();
           
            return view("service::create-service", array("categories" => $arr_categories,"services"=>$services));
        } else {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'name' => 'required',
                        'category' => 'required',
                        'service_image' => 'required|image',
                        'service_selected_image' => 'required|image'
            ));

            if ($validate_response->fails()) {
                return redirect($request->url())->withErrors($validate_response)->withInput();
            } else {
                $new_service_img="";
                $new_service_selected_img="";
                if ($request->hasFile('service_image')) {
                        $service_extension = $request->file('service_image')->getClientOriginalExtension();
                        $new_service_img = rand() . "." . $service_extension;
                        Storage::put('public/service-image/' . $new_service_img, file_get_contents($request->file('service_image')->getRealPath()));
                        $service->service_image = $new_service_img;
                        $service->save();
                    }
                if ($request->hasFile('service_selected_image')) {
                    $service_sel_extension = $request->file('service_selected_image')->getClientOriginalExtension();
                    $new_service_selected_img = rand() . "." . $service_sel_extension;
                    Storage::put('public/service-image/' . $new_service_selected_img, file_get_contents($request->file('service_selected_image')->getRealPath()));
                    $service->service_selected_image = $new_service_selected_img;
                    $service->save();
                }
                $arr_service_data=Service::where('category_id',$request->category)->count();
                $created_service = Service::create(array('category_id' => $request->category,'parent_id'=>$request->parent_id,'service_image' => $new_service_img, 'service_selected_image' => $new_service_selected_img));

                $translated_service = $created_service->translateOrNew(\App::getLocale());
                $translated_service->name = $request->name;
                $translated_service->description = $request->description;
                $translated_service->locale = \App:: getLocale();
                $translated_service->service_id = $created_service->id;
                $translated_service->save();
               

                return redirect("admin/services-list")->with('status', 'Service created successfully!');
            }
        }
    }

    public function updateCategory(Request $request, $service_id, $locale = "") {
        $service = Service::find($service_id);
        if ($service) {
             $translated_service = $service->translateOrNew($locale);
            
            $arr_categories = Category::translatedIn(\App::getLocale())->get();
            if ($request->method() == "GET") {
                $services = Service::translatedIn(\App::getLocale())->where('category_id',$service->category_id)->get();
                if (isset($locale) && $locale != 'en' && $locale != '') {
                    return view("service::update-language-service", array('service' => $translated_service, 'main_info' => $service));
                } else {
                    return view("service::update-service", array('service' => $translated_service, 'main_info' => $service, "categories" => $arr_categories,"services"=>$services));
                }
            } else {
                $data = $request->all();
                if ($locale != 'en' && $locale != '') {

                    $validate_response = Validator::make($data, array(
                                'name' => 'required',
                    ));
                } else {

                    $validate_response = Validator::make($data, array(
                                'name' => 'required',
                                'category' => 'required',
                                'service_image' => 'image',
                                'service_selected_image' => 'image',
                                'service_type' => 'required',
                    ));
                }

                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    if ($request->hasFile('service_image')) {
                        $service_extension = $request->file('service_image')->getClientOriginalExtension();
                        $new_service_img = rand() . "." . $service_extension;
                        Storage::put('public/service-image/' . $new_service_img, file_get_contents($request->file('service_image')->getRealPath()));
                        $service->service_image = $new_service_img;
                        $service->save();
                    }
                    if ($request->hasFile('service_selected_image')) {
                        $service_sel_extension = $request->file('service_selected_image')->getClientOriginalExtension();
                        $new_service_selected_img = rand() . "." . $service_sel_extension;
                        Storage::put('public/service-image/' . $new_service_selected_img, file_get_contents($request->file('service_selected_image')->getRealPath()));
                        $service->service_selected_image = $new_service_selected_img;
                        $service->save();
                    }
                    
//                    $service->is_default = '1';
//                    $service->save();
                   
                    if ($locale == 'en' || $locale == '') {
                        $service->category_id = $request->category;
                        $service->service_type = $request->service_type;
                        $service->required_pick_up_address = $request->required_pick_up_address;
                        $service->required_drop_up_address = $request->required_drop_up_address;
                        $service->required_pick_up_person = $request->required_pick_up_person;
                        $service->required_drop_up_person = $request->required_drop_up_person;
                        $service->required_goods_description = $request->required_goods_description;
                        $service->required_goods_image = $request->required_goods_image;
                        $service->number_of_person = $request->number_of_person;
                        $service->pickup_detail_address = $request->pickup_detail_address;
                        $service->dropoff_detail_address = $request->dropoff_detail_address;
                        $service->fuel_price_field = $request->fuel_price_field;
                        $service->show_number_of_person_limit = $request->show_number_of_person_limit;
                        $service->number_of_person_limit = $request->number_of_person_limit;
                        $service->is_sharable = $request->is_sharable;
                        $service->no_of_hours = $request->no_of_hours;
                        $service->min_range = $request->min_range;
                        $service->max_range = $request->max_range;
                        $service->parent_id = $request->parent_id;
                       
                        $service->save();
                    }
                    $translated_service->name = $request->name;
                    $translated_service->description = $request->description;

                    if ($locale != '' && $locale != 'en') {
                        $translated_service->service_id = $service->id;
                        $translated_service->locale = $locale;
                    }

                    $translated_service->save();

                    return redirect("admin/services-list")->with('status', 'Service updated successfully!');
                }
            }
        } else {
            return redirect('admin/services-list');
        }
    }

    public function deleteCategory($category_id) {
        $category = Category::find($category_id);

        if ($category) {
            $category->delete();
            return redirect("admin/categories-list")->with('status', 'Category deleted successfully!');
        } else {
            return redirect('admin/categories-list');
        }
    }

    public function deleteSelectedCategory($category_id) {
        $category = Category::find($category_id);

        if ($category) {
            $category->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }
    
    public function getServicesByCategory($category_id) {
      
        $services = Service::where('category_id', $category_id)->translatedIn(\App::getLocale())->get();
        $select_value = '<option value="">--No Parent--</option>';
       
        if ($services) {
            foreach ($services as $key => $value) {
                if($value->id!=17 &&$value->id!=15 &&$value->id!=32)
                {
                  $select_value.='<option value="' . $value->id . '">' . $value->name . '</option>';
                }
            }
        }
        echo $select_value;
        exit;

        //return view('admin::list-states');
    }
}
