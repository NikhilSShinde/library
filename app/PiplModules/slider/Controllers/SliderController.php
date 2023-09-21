<?php

namespace App\PiplModules\slider\Controllers;

use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\slider\Models\SliderImage;
use Datatables;

class SliderController extends Controller {

    public function listSliders() {
        return view('slider::list-slider');
    }

    public function listSlidersData() {

        $all_slider_images = SliderImage::all();

        return Datatables::of($all_slider_images)
                        ->addColumn("image", function($image) {
                            $value = "";

                            if ($image->image_path) {
                                $value = '<img width="100" src="' . asset("/storageasset/slider-images/" . $image->image_path) . '">';
                            } else {
                                $value = "-";
                            }
                            return $value;
                        })
                        ->addColumn("type", function($type) {
                            
                            $value = "";
                            if ($type->type == '0') {
                                $value = "Driver";
                            } else {
                                $value = "Customer";
                            }
                            return $value;
                        })
                        ->make(true);
    }

    public function createSliders(Request $request) {
        if ($request->method() == "GET") {
            return view("slider::create-slider");
        } else {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'title' => 'required',
                        'value' => 'required',
                        'type' => 'required',
                        'locale' => 'required',
                            )
            );
            if ($validate_response->fails()) {
                return redirect($request->url())->withErrors($validate_response)->withInput();
            } else {
                $arrSliderImages = array();
                $arrSliderImages['title'] = $request->title;
                $arrSliderImages['type'] = $request->type;
                $arrSliderImages['locale'] = $request->locale;
                if ($request->file('value')) {
                    $extension = $request->file('value')->getClientOriginalExtension();
                    $new_file_name = time() . "." . $extension;
                    Storage::put('public/slider-images/' . $new_file_name, file_get_contents($request->file('value')->getRealPath()));
                    $arrSliderImages['image_path'] = $new_file_name;
                    SliderImage::create($arrSliderImages);
                    return redirect('admin/tutorials-list')->with('status', 'Tutorials Image has been created successfully!');
                }
            }
        }
    }

    public function updateSlider(Request $request, $slider_id, $locale = "") {
        $sliderData = SliderImage::find($slider_id);
        if ($request->method() == "GET") {

            return view("slider::update-slider", array("sliderData" => $sliderData));
        } else {
            $data = $request->all();
            $validate_response = Validator::make($data, array(
                        'title' => 'required',
                        'type' => 'required',
                        'locale' => 'required',
                            )
            );
            if ($validate_response->fails()) {
                return redirect($request->url())->withErrors($validate_response)->withInput();
            } else {
                $arrSliderImages = array();
                $sliderData->title = $request->title;
                $sliderData->type = $request->type;
                $sliderData->locale = $request->locale;
                if ($request->file('value')) {
                    $extension = $request->file('value')->getClientOriginalExtension();
                    $new_file_name = time() . "." . $extension;
                    Storage::put('public/slider-images/' . $new_file_name, file_get_contents($request->file('value')->getRealPath()));
                    $sliderData->image_path = $new_file_name;
                }
            }
            $sliderData->save();
            return redirect('admin/tutorials-list')->with('status', 'Tutorials Image has been updated successfully!');
        }
    }

    public function deleteSlider($slider_id) {
        $slider = SliderImage::find($slider_id);

        if ($slider) {
            $slider->delete();
            return redirect("admin/tutorials-list")->with('status', 'Tutorials Image has been deleted successfully!');
        } else {
            return redirect('admin/tutorials-list');
        }
    }

}
