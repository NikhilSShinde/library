<?php

Route::group(array('module' => 'Coupon', 'namespace' => 'App\PiplModules\coupon\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    Route::get("/admin/coupons", "Coupons@index")->middleware('permission:view.coupon-list');
    Route::get("/admin/coupon-data", "Coupons@getCouponData")->middleware('permission:view.coupon-list');
    Route::delete("/admin/coupon/delete-selected/{coupon_id}", "Coupons@deleteSelectedCoupon")->middleware('permission:delete.coupon-list');
    
    Route::get("/admin/coupon/create", "Coupons@createCoupon")->middleware('permission:add.coupon-list');
    Route::post("/admin/coupon/create", "Coupons@createCoupon")->middleware('permission:add.coupon-list');
    
    Route::get("/admin/coupon/update/{coupon_id}", "Coupons@updateCoupon")->middleware('permission:update.coupon-list');
    Route::post("/admin/coupon/update/{coupon_id}", "Coupons@updateCoupon")->middleware('permission:update.coupon-list');
    
    Route::get("/admin/coupon/view/{coupon_id}", "Coupons@viewCoupon")->middleware('permission:view.coupon-list');
});
