<?php
Route::group(array('module'=>'ContentPage','namespace' => 'App\PiplModules\contactrequest\Controllers','middleware'=>'web'), function() {
    //Your routes belong to this module.
	
	Route::get("/admin/contact-requests/{list?}","ContactRequestController@index")->middleware('permission:view.contact-requests');
	Route::get("/admin/contact-requests-data","ContactRequestController@contactRequestData")->middleware('permission:view.contact-requests');
	
	Route::get("/contact-us","ContactRequestController@showContactForm");
	Route::post("/contact-us","ContactRequestController@showContactForm");
	
	Route::get("/admin/contact-request/{reference_no}","ContactRequestController@viewContactRequest")->middleware('permission:view.contact-requests');;
	Route::post("/admin/contact-request-reply/{reference_no}","ContactRequestController@postReply")->middleware('permission:do.contact-reply');
	
	Route::delete("/admin/contact-request/delete/{req_id}","ContactRequestController@deleteContactRequest")->middleware('permission:do.contact-reply');
	Route::delete("/admin/contact-request/delete-selected/{req_id}","ContactRequestController@deleteSelectedContactRequest")->middleware('permission:delete.contact-requests');
	
	Route::get("/admin/contact-request-categories","ContactRequestController@listContactCategories")->middleware('permission:view.contact-request-categories');
	Route::get("/admin/contact-request-categories-data","ContactRequestController@listContactCategoriesData")->middleware('permission:view.contact-request-categories');
	Route::get("/admin/contact-request-categories/create","ContactRequestController@createContactCategories")->middleware('permission:create.contact-request-categories');
	Route::post("/admin/contact-request-categories/create","ContactRequestController@createContactCategories")->middleware('permission:create.contact-request-categories');
	
	Route::get("/admin/contact-request-category/{category_id}/{locale?}","ContactRequestController@updateContactCategory")->middleware('permission:update.contact-requests-categories');
	Route::post("/admin/contact-request-category/{category_id}/{locale?}","ContactRequestController@updateContactCategory")->middleware('permission:update.contact-requests-categories');
	
	Route::get("/admin/contact-request-categories/update-language/{category_id}/{locale?}","ContactRequestController@updateContactCategoryLanguage")->middleware('permission:update.contact-requests-categories');
	Route::post("/admin/contact-request-categories/update-language/{category_id}/{locale?}","ContactRequestController@updateContactCategoryLanguage")->middleware('permission:update.contact-requests-categories');
	
	Route::delete("/admin/delete-contact-request-category/{category_id}","ContactRequestController@deleteContactCategory")->middleware('permission:delete.contact-requests-categories');
	Route::delete("/admin/delete-selected-contact-request-category/{category_id}","ContactRequestController@deleteSelectedContactCategory")->middleware('permission:delete.contact-requests-categories');;
});