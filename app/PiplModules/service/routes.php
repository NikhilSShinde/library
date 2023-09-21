<?php
Route::group(array('module'=>'Service','namespace' => 'App\PiplModules\service\Controllers','middleware'=>'web'), function() {
        //Your routes belong to this module.
	Route::get("/admin/services-list","ServiceController@listServices")->middleware('permission:view.service');
	Route::get("/admin/services-list-data","ServiceController@listServicesData")->middleware('permission:view.service');
	Route::get("/admin/service/create","ServiceController@createService")->middleware('permission:create.service');
	Route::post("/admin/service/create","ServiceController@createService")->middleware('permission:create.service');
	Route::get("/admin/service/{service_id}/{locale?}","ServiceController@updateCategory")->middleware('permission:update.service');
	Route::post("/admin/service/{service_id}/{locale?}","ServiceController@updateCategory")->middleware('permission:update.service');
	Route::delete("/admin/service/{service_id}","ServiceController@deleteCategory")->middleware('permission:delete.service');
	Route::get("/admin/service_details/get-service-by-category/{category_id}","ServiceController@getServicesByCategory");
	
});