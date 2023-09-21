<?php

namespace App\PiplModules\ratingreview\Controllers;

use Auth;
use Auth\User;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Storage;
use App\PiplModules\ratingreview\Models\RatingQuestion;
use App\PiplModules\ratingreview\Models\UserRatingInformation;
use App\PiplModules\ratingreview\Models\RatingQuestionTranslation;
use App\UserInformation;
use Mail;
use Datatables;

class RatingReviewController extends Controller {

    public function index($user_id='0') {
        if (Auth::user()) {
            return view("ratingreview::list",array('user_id'=>$user_id));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getRatingData($user_id=0) {
       $all_data = UserRatingInformation::all();
       if($user_id!=0)
       {
          $all_data = UserRatingInformation::where('to_id',$user_id)->get();
       }
       return Datatables::of($all_data)                
                        ->addcolumn('from_name', function($all_data) {
                           if(isset($all_data->getFromUserDetails))
                           {
                            return $all_data->getFromUserDetails->first_name . ' ' . $all_data->getFromUserDetails->last_name;
                           }
                        })
                        ->addcolumn('to_name', function($all_data) {
                             if(isset($all_data->getToUserDetails))
                           {
                            return $all_data->getToUserDetails->first_name . ' ' . $all_data->getToUserDetails->last_name;
                            }
                        })
                        ->addcolumn('order_unique_id', function($all_data) {
                             if(isset($all_data->getOrderDetails))
                           {
                        
                            return $all_data->getOrderDetails->order_unique_id;
                            }
                        })
                        ->addcolumn('status', function($all_data) {
                             if(isset($all_data->status))
                           {
                        
                            return ($all_data->status == '0') ? 'Inactive' : 'Active';
                            }
                        })
                        ->make(true);
    }

    public function getRatingAndReviewDetails($rating_id,$user_id=0) {
        if (Auth::user()) {
            $all_data = UserRatingInformation::find($rating_id);
            $implode_ids = explode(',', $all_data->rating_ques_id);
            $arr_get_question_data = RatingQuestionTranslation::whereIn('id', $implode_ids)->get();
            return view("ratingreview::view", array('rating_details' => $all_data, 'question_data' => $arr_get_question_data,'user_id'=>$user_id));
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function ratingTagsList() {
        if (Auth::user()) {
            return view("ratingreview::tags-list");
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function ratingTagsData() {
        $all_data = RatingQuestion::all();
        return Datatables::of($all_data)
                        ->addcolumn('rating_star_no', function($all_data) {
                            return $all_data->rating_star_no;
                        })
                        ->addcolumn('title', function($all_data) {
                            return $all_data->ques_title;
                        })
                        ->addcolumn('title', function($all_data) {
                            return $all_data->ques_desc;
                        })
                        ->addColumn('Language', function($all_data) {
                            $language = '<button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="langDropDown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Another Language <span class="caret"></span> </button>
                         <ul class="dropdown-menu multilanguage" aria-labelledby="langDropDown">';
                            if (count(config("translatable.locales_to_display"))) {
                                foreach (config("translatable.locales_to_display") as $locale => $locale_full_name) {
                                    if ($locale != 'en') {
                                        $language.='<li class="dropdown-item"> <a href="edit-tags/' . $all_data->id . '/' . $locale . '">' . $locale_full_name . '</a></li>';
                                    }
                                }
                            }
                            return $language;
                        })
                        ->addcolumn('status', function($all_data) {
                            return ($all_data->status == '1') ? 'Active' : 'Inactive';
                        })
                        ->make(true);
    }

    public function editRatingTags(Request $request, $rating_que_tag_id, $locale = "") {
        $all_data = RatingQuestion::find($rating_que_tag_id);
        if ($all_data) {
//            $is_new_entry = !($all_data->hasTranslation());
            $all_data_translate = $all_data->translateOrNew($locale);

            if ($request->method() == "GET") {
                if ($locale != 'en' && $locale != '' && isset($locale)) {
                    return view("ratingreview::update-language-tags", array('rating_data_lang' => $all_data_translate));
                } else {
                    return view("ratingreview::edit-tags", array('rating_data_lang' => $all_data_translate, 'rating_data' => $all_data));
                }
            } else {
                $data = $request->all();
                if ($locale != 'en' && $locale != '') {
                    $validate_response = Validator::make($data, array(
                                'title' => 'required',
                                'description' => 'required',
                    ));
                } else {
                    $validate_response = Validator::make($data, array(
                                'title' => 'required',
                                'description' => 'required',
//                                'status' => 'required',
                    ));
                }
                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    if ($locale == '' || $locale == 'en') {
//                        $all_data->rating_star_no = $request->star_counter;
//                        $all_data->status = $request->status;
                        $all_data->save();
                    }

                    $all_data_translate->ques_title = $request->title;
                    $all_data_translate->ques_desc = $request->description;

                    if ($locale != '' && isset($locale) && $locale != 'en') {
                        $all_data_translate->rating_question_id = $all_data->id;
                        $all_data_translate->ques_title = $request->title;
                        $all_data_translate->ques_desc = $request->description;
                        $all_data_translate->locale = $locale;
                        $all_data_translate->save();
                    }
                    $all_data_translate->save();
                    return redirect("/admin/rating-review/tags-list")->with('status', 'Rating tags has been updated successfully!');
                }
            }
        } else {
            return redirect('/admin/rating-review/tags-list');
        }
    }

    public function createRatingTags(Request $request) {
        if (Auth::user()) {
            if ($request->method() == "GET") {
                return view("ratingreview::creat-tags");
            } else {
                $data = $request->all();
                $validate_response = Validator::make($data, array(
                            'title' => 'required',
                            'description' => 'required',
                ));
                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                    $rating_question = new RatingQuestion();
                    $rating_question->rating_star_no = $request->star_counter;
                    $rating_question->save();

                    $rating_tags = new RatingQuestionTranslation();
                    $rating_tags->ques_title = $request->title;
                    $rating_tags->ques_desc = $request->description;
                    $rating_tags->rating_question_id = $rating_question->id;
                    $rating_tags->save();
                    return redirect("/admin/rating-review/tags-list")->with('status', 'Rating tags has been created successfully!');
                }
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function editReviewRating(Request $request, $rating_id,$user_id=0) {
        
        if (Auth::user()) {
            $all_data = UserRatingInformation::find($rating_id);
            $arr_rating_by = UserInformation::where('user_id',$all_data->from_id)->first();
            if ($request->method() == "GET") {
//               
//              $explode_ids = explode(',', $all_data->rating_ques_id);
                $arr_get_star_no = RatingQuestion::translatedIn(\App::getLocale())->get();
               //rating
                return view("ratingreview::edit", array('rating_details' => $all_data, 'question_data' => $arr_get_star_no,"rating_by_type"=>$arr_rating_by->user_type,'user_id'=>$user_id));
            } else {
                $data = $request->all();
                if($arr_rating_by->user_type==3){
                    $validate_response = Validator::make($data, array(
                                'status' => 'required',
                                'star_counter' => 'required',
                                'description' => 'required',
                    ));
                }else{    
                    $validate_response = Validator::make($data, array(
                                'status' => 'required',
                                'star_counter' => 'required',
                    ));
                }
                
                if ($validate_response->fails()) {
                    return redirect($request->url())->withErrors($validate_response)->withInput();
                } else {
                   
                    // $implode_ids = implode(',', $request->rating_tags);
                   // $implode_ids = implode(',', $request->rating_tags);
                    $all_data->rating = $request->star_counter;
                    $all_data->review = isset($request->description)?$request->description:'';
                   
                    $all_data->rating_ques_id = (isset($request->rating_tags) && $request->rating_tags!='0')?$request->rating_tags:'';
                    $all_data->status = $request->status;
                    $all_data->save();
                  if($user_id!=0)
                  {
                      return redirect("/admin/rating-review/list/".$user_id)->with('status', 'Record has been updated successfully!');
                  }else{
                    return redirect("/admin/rating-review/list")->with('status', 'Record has been updated successfully!');
                  }
                }
            }
        } else {
            $errorMsg = "Error! Something is wrong going on.";
            Auth::logout();
            return redirect("admin/login")->with("issue-profile", $errorMsg);
        }
    }

    public function getRatingTags(Request $request) {
//        echo $request->question_id;
        $arr_get_star_no = RatingQuestion::where('rating_star_no', $request->question_id)->select('id')->get();
        $arr_ids = array();
        foreach ($arr_get_star_no as $key){
            $arr_ids[] = $key->id;
        }
        
        $arr_get_question_data = RatingQuestionTranslation::whereIn('rating_question_id', $arr_ids)->where('locale','en')->get();
//        dd($arr_get_question_data);
        return $arr_get_question_data;
    }

    public function deleteSelectedTags($tag_id) {
        $tag_id = RatingQuestion::find($tag_id);
        if ($tag_id) {
            $tag_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

    public function deleteSelectedReview($rating_id) {
        $rating_id = UserRatingInformation::find($rating_id);
        if ($rating_id) {
            $rating_id->delete();
            echo json_encode(array("success" => '1', 'msg' => 'Selected records has been deleted successfully.'));
        } else {
            echo json_encode(array("success" => '0', 'msg' => 'There is an issue in deleting records.'));
        }
    }

}
