<?php
Route::group(array('module'=>'Faq','namespace' => 'App\PiplModules\faq\Controllers','middleware'=>'web'), function() {
    //Your routes belong to this module.
	
	Route::get("/admin/faqs","FaqController@index")->middleware('permission:view.faqs');
	Route::get("/admin/faq-data","FaqController@getFaqData")->middleware('permission:view.faqs');
	
	Route::get("/admin/faqs/create","FaqController@createFaq")->middleware('permission:create.faqs');
	Route::post("/admin/faqs/create","FaqController@createFaq")->middleware('permission:create.faqs');
	
	Route::get("/admin/faq/{faq_id}/{locale?}","FaqController@updateFaq")->middleware('permission:update.faqs');
	Route::post("/admin/faq/{faq_id}/{locale?}","FaqController@updateFaq")->middleware('permission:update.faqs');

	Route::delete("/admin/faq/delete/{faq_id}","FaqController@deleteFaq")->middleware('permission:delete.faqs');
	Route::delete("/admin/faq/delete-selected/{faq_id}","FaqController@deleteSelectedFaq")->middleware('permission:delete.faqs');
	
	Route::get("/admin/faq-categories","FaqController@listFaqCategories")->middleware('permission:view.faq-categories');
	Route::get("/admin/faq-categories-data","FaqController@listFaqCategoriesData")->middleware('permission:view.faq-categories');
	Route::get("/admin/faq-categories/create","FaqController@createFaqCategories")->middleware('permission:create.faq-category');
	Route::post("/admin/faq-categories/create","FaqController@createFaqCategories")->middleware('permission:create.faq-category');

	Route::get("/admin/faq-category/{category_id}/{locale?}","FaqController@updateFaqCategory")->middleware('permission:update.faq-category');
	Route::post("/admin/faq-category/{category_id}/{locale?}","FaqController@updateFaqCategory")->middleware('permission:update.faq-category');
	
	Route::delete("/admin/delete-faq-category/{category_id}","FaqController@deleteFaqCategory")->middleware('permission:delete.faq-category');
	Route::delete("/admin/delete-selected-faq-category/{category_id}","FaqController@deleteSelectedFaqCategory")->middleware('permission:delete.faq-category');


	Route::get("/faqs","FaqController@viewFaqs");
	Route::get("/faqs/{cat_url}","FaqController@viewFaqsBycategory");
});