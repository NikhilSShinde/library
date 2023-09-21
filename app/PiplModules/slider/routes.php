<?php
Route::group(array('module'=>'Slider','namespace' => 'App\PiplModules\slider\Controllers','middleware'=>'web'), function() {
        //Your routes belong to this module.
	Route::get("/admin/tutorials-list","SliderController@listSliders");//->middleware('permission:view.slider.images');
	Route::get("/admin/tutorials-list-data","SliderController@listSlidersData");//->middleware('permission:view.slider.images');
	Route::get("/admin/tutorials/create","SliderController@createSliders");//->middleware('permission:create.slider.images');
	Route::post("/admin/tutorials/create","SliderController@createSliders");//->middleware('permission:create.slider.images');
	Route::get("/admin/tutorials/{tutorial_id}/{locale?}","SliderController@updateSlider");//->middleware('permission:update.slider.images');
	Route::post("/admin/tutorials/{category_id}/{locale?}","SliderController@updateSlider");//->middleware('permission:update.slider.images');
	Route::delete("/admin/tutorials/{tutorial_id}","SliderController@deleteSlider");//->middleware('permission:delete.slider.images');
	Route::delete("/admin/tutorials-delete-selected/{tutorial_id}","SliderController@deleteSelectedSlider");//->middleware('permission:delete.slider.images');
});