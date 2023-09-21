<?php

Route::group(array('module'=>'EmailTemplate','namespace' => 'App\PiplModules\emailtemplate\Controllers','middleware'=>'web','as'=>'emailtemplate::'), function() {
    //Your routes belong to this module.
	
	Route::get("/admin/email-templates/list","EmailTemplateController@index")->middleware('permission:view.email-templates');
	Route::get("/admin/email-templates-data","EmailTemplateController@getEmailTemplateData")->middleware('permission:view.email-templates');
	Route::get("/admin/email-templates/update/{template_id}","EmailTemplateController@showUpdateEmailTemplateForm")->middleware('permission:update.email-templates');
	Route::post("/admin/email-templates/update/{template_id}","EmailTemplateController@showUpdateEmailTemplateForm")->middleware('permission:update.email-templates');

});