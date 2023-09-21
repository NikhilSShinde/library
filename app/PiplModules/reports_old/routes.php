<?php

Route::group(array('module' => 'reports', 'namespace' => 'App\PiplModules\reports\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.
    Route::post("/download", "ReportController@exportToExcel");
    Route::post("/download/revenue", "ReportController@exportToExcelRevenueReport");
    Route::post("/download/star-users", "ReportController@exportStarUsersToExcel");

    Route::get("/admin/reports/order-report-list/{country_id?}", "ReportController@index")->middleware('permission:view.report.list');
    Route::get("/admin/report-order-data/{country_id?}", "ReportController@getOrderData")->middleware('permission:view.report.list');

    Route::get("/admin/reports/revenue/{country_id?}", "ReportController@revenueReportIndex")->middleware('permission:view.revenuereport.list');
    Route::get("/admin/revenue-data/{country_id?}", "ReportController@getRevenueData")->middleware('permission:view.revenuereport.list');

    Route::get("/admin/order-view-report/{order_id}", "ReportController@orderDetails")->middleware('permission:view.report.list');

    Route::get("/admin/star-users-report-data", "ReportController@listStarUsersData")->middleware('permission:view.starreport.list');
    Route::get("/admin/reports/star-user-report/{agent_id?}", "ReportController@getStarUsers")->middleware('permission:view.starreport.list');
});
