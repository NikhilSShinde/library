<?php

Route::group(array('module' => 'WalletHistory', 'namespace' => 'App\PiplModules\wallethistory\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    Route::get("/admin/wallet-history", "WalletController@index")->middleware('permission:view.wallet.history');
    Route::get("/admin/wallet-history/{user_id}", "WalletController@index")->middleware('permission:view.wallet.history');
    Route::get("/admin/wallet-history-data", "WalletController@getWalletHistoryData")->middleware('permission:view.wallet.history');
    Route::get("/admin/wallet-history-data/{user_id}", "WalletController@getWalletHistoryData")->middleware('permission:view.wallet.history');
});
