<?php
Route::group(array('module'=>'Testimonial','namespace' => 'App\PiplModules\testimonial\Controllers','middleware'=>'web'), function() {
    //Your routes belong to this module.
	
	Route::get("/admin/testimonials/list","TestimonialController@index")->middleware('permission:view.testimonials');
	Route::get("/admin/testimonials-data","TestimonialController@getTestimonialData")->middleware('permission:view.testimonials');
	
	Route::get("/admin/testimonials/create","TestimonialController@createTestimonial")->middleware('permission:create.testimonials');
	Route::post("/admin/testimonials/create","TestimonialController@createTestimonial")->middleware('permission:create.testimonials');
	
	Route::get("/admin/testimonials/update/{page_id}","TestimonialController@showUpdateTestimonialPageForm")->middleware('permission:update.testimonials');
	Route::post("/admin/testimonials/update/{page_id}","TestimonialController@showUpdateTestimonialPageForm")->middleware('permission:update.testimonials');
	
	
	Route::delete("/admin/testimonials/delete/{page_id}","TestimonialController@deleteTestimonial")->middleware('permission:delete.testimonials');
	Route::delete("/admin/testimonials/delete-selected/{page_id}","TestimonialController@deleteSelectedTestimonial")->middleware('permission:delete.testimonials');
	
});
