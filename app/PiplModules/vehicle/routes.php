<?php

Route::group(array('module' => 'vehicle', 'namespace' => 'App\PiplModules\vehicle\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    
    Route::get("/admin/vehicle-list/{user_id?}", "VehicleController@index")->middleware('permission:view.vehicle-list');
    Route::get("/admin/vehicle-list-data/{user_id}", "VehicleController@getVehicleInfomation")->middleware('permission:view.vehicle-list');
    
    Route::get("/admin/vehicle/update/{vehicle_id}", "VehicleController@updateVehicleInfomation")->middleware('permission:update.vehicle-list');
    Route::post("/admin/vehicle/update/{vehicle_id}", "VehicleController@updateVehicleInfomation")->middleware('permission:update.vehicle-list');
    
    Route::get("/admin/vehicle/add/{user_id}", "VehicleController@addVehicleInfomation")->middleware('permission:add.vehicle-list');
    Route::post("/admin/vehicle/add/{user_id}", "VehicleController@addVehicleInfomation")->middleware('permission:add.vehicle-list');
    
    Route::delete("/admin/vehicle/delete-selected/{vehicle_id}", "VehicleController@deleteSelectedVehicle")->middleware('permission:delete.vehicle-list');
});
