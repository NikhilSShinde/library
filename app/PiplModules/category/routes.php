<?php
Route::group(array('module'=>'Category','namespace' => 'App\PiplModules\category\Controllers','middleware'=>'web'), function() {
        //Your routes belong to this module.
	Route::get("/admin/categories-list","CategoryController@listCategories")->middleware('permission:view.categories');
	Route::get("/admin/categories-list-data","CategoryController@listCategoriesData")->middleware('permission:view.categories');
	Route::get("/admin/category/create","CategoryController@createCategories")->middleware('permission:create.categories');
	Route::post("/admin/category/create","CategoryController@createCategories")->middleware('permission:create.categories');
	Route::get("/admin/category/{category_id}/{locale?}","CategoryController@updateCategory")->middleware('permission:update.categories');
	Route::post("/admin/category/{category_id}/{locale?}","CategoryController@updateCategory")->middleware('permission:update.categories');
	Route::delete("/admin/category/{category_id}","CategoryController@deleteCategory")->middleware('permission:delete.categories');
	Route::delete("/admin/category-delete-selected/{category_id}","CategoryController@deleteSelectedCategory")->middleware('permission:delete.categories');
});