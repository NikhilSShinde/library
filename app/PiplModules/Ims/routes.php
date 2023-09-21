<?php
Route::group(array('module'=>'Ims','namespace' => 'App\PiplModules\Ims\Controllers','middleware'=>'web'), function() {
    //Your routes belong to this module.
	
	Route::get("/ims","InternalMessageController@index")/*->middleware('permission:view.ims')*/;
	Route::get("/ims/sent","InternalMessageController@sentFolder")/*->middleware('permission:view.ims')*/;
	
	Route::get("/ims/compose","InternalMessageController@compose");
	Route::post("/ims/compose","InternalMessageController@compose");
	
	Route::get("/ims/conversation/{conversation_id}","InternalMessageController@conversationMessages");
	Route::post("/ims/conversation/{conversation_id}","InternalMessageController@conversationMessages");
	
	Route::post("/ims/trash-message","InternalMessageController@trashMessage");
	Route::get("/ims/trash","InternalMessageController@trashFolder");
	
	Route::post("/ims/restore-message","InternalMessageController@restoreMessage");
	Route::post("/ims/delete-message","InternalMessageController@deleteMessagePermanently");
	
	Route::post("/ims/trash-conversation","InternalMessageController@trashConversation");
});