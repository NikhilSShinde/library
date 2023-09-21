<?php

Route::group(array('module' => 'loan', 'namespace' => 'App\PiplModules\loan\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    
    Route::get("/admin/loan-list", "LoanController@index");
    Route::get("/admin/loan-list-data", "LoanController@getLoanInfomation");
    
    Route::get("/admin/loan/update/{loan_id}", "LoanController@updateLoanInfomation");
    Route::post("/admin/loan/update/{loan_id}", "LoanController@updateLoanInfomation");
    
    Route::get("/admin/loan/add", "LoanController@addLoanInfomation");
    Route::post("/admin/loan/add", "LoanController@addLoanInfomation");
    
    Route::delete("/admin/loan/delete-selected/{loan_id}", "LoanController@deleteSelectedLoan");
    
    Route::get("/admin/loan-emi-list/{loan_id}", "LoanController@getLoanEMIInfomation");
    Route::get("/admin/loan-emi-list-data/{loan_id}", "LoanController@getLoanEMIDataInfomation");
    
    Route::get("/admin/loan-pay-emi/{loan_id}/{loan_emi_id}", "LoanController@payLoanEMIInfomation");
    Route::post("/admin/loan-pay-emi/{loan_id}/{loan_emi_id}", "LoanController@payLoanEMIInfomation");
    
    Route::post('/admin/dopayment', 'LoanController@dopayment')->name('dopayment');
});
