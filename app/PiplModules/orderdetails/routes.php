<?php

Route::group(array('module' => 'OrderController', 'namespace' => 'App\PiplModules\orderdetails\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    Route::get("/admin/order-list/{status?}", "OrderController@index")->middleware('permission:view.order-list');
    Route::get("/admin/order-data/{status?}", "OrderController@getOrderData")->middleware('permission:view.order-list');
    Route::get("/admin/order-view/{order_id}", "OrderController@orderDetails")->middleware('permission:view.order-list');
    /* added by vishal on  19-01-2017 */
    Route::get("/admin/view-order-notifications/{order_id}", "OrderController@ListNotification")->middleware('permission:view.order-list');
    Route::get("/admin/load-notifications/{order_id}", "OrderController@notificationDetails")->middleware('permission:view.order-list');
    
    Route::get("/admin/view-order-rejected/{order_id}", "OrderController@ListRejection")->middleware('permission:view.order-list');
    Route::get("/admin/load-rejected-orders/{order_id}", "OrderController@rejectionDetails")->middleware('permission:view.order-list');
    /* added by vishal on  19-01-2017 */
    Route::get("/admin/assign-star/order-view/{order_id}", "OrderController@orderDetails")->middleware('permission:view.order-list');
    Route::get("/admin/order-view-quotes/{order_id}", "OrderController@orderViewQuotes")->middleware('permission:view.order-list');
    Route::get("/admin/order-quotes-data/{order_id}", "OrderController@orderViewQuotesData")->middleware('permission:view.order-list');
    Route::get("/admin/assign-star/{order_id}", "OrderController@getStarForOrderPage")->middleware('permission:view.order-list');

    Route::post("/admin/order-assign-star", "OrderController@getStarForOrder")->middleware('permission:view.order-list');
    Route::get("/admin/assign-star/assign-star-to-order/{order_id}/{driver_id}", "OrderController@assignStarForOrder")->middleware('permission:view.order-list');
    Route::post("/admin/assign-star-to-order", "OrderController@assignStarForOrder")->middleware('permission:view.order-list');

    Route::delete("/admin/order/delete-selected/{order_id}", "OrderController@deleteSelectedOrder")->middleware('permission:delete.order-list');
    Route::delete("/admin/notification/delete-selected/{notification_id}", "OrderController@deleteSelectedNotification")->middleware('permission:delete.order-list');
    Route::delete("/admin/order/delete-selected-quotes/{order_id}", "OrderController@deleteSelectedOrderQuotes")->middleware('permission:delete.order-list');
});
