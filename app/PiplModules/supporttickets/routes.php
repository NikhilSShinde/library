<?php

Route::group(array('module' => 'SupportTicket', 'namespace' => 'App\PiplModules\supporttickets\Controllers', 'middleware' => 'web'), function() {
    //Your routes belong to this module.

    Route::get("/admin/support-tickets", "SupportController@index");
    Route::get("/admin/suppor-ticket-data", "SupportController@getSupportTicketData");
    
    Route::get("/admin/suppor-ticket-details/{ticket_id}", "SupportController@getSupportTicketDetails");

    Route::get("/admin/support-ticket/agent-user", "SupportController@getAgentUser");
    
    Route::get("/admin/support-ticket/assign-to-agent", "SupportController@assignTicket");
    Route::post("/admin/support-ticket/assign-to-agent", "SupportController@assignTicket");
    
    Route::post("/admin/suppot-ticket-reply", "SupportController@supportTicketReply");

    Route::delete("/admin/support-tickets/delete-selected/{ticket_id}", "SupportController@deleteSelectedTicket");
});
