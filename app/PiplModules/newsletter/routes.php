<?php
Route::group(array('module'=>'newsletter','namespace' => 'App\PiplModules\newsletter\Controllers','middleware'=>'web'), function() {

		Route::get("/newsletter","NewsletterController@index");
		Route::post("/newsletter/subscribe","NewsletterController@subscribeToNewsLetter");
		
		Route::get("/admin/newsletters","NewsletterController@listNewsletters");
		Route::get("/admin/newsletter-data","NewsletterController@listNewslettersData");
		
		Route::get("/admin/newsletter/create","NewsletterController@createNewsletter");
		Route::post("/admin/newsletter/create","NewsletterController@createNewsletter");
		
		Route::get("/admin/newsletter/{newletter_id}","NewsletterController@updateNewsletter");
		Route::post("/admin/newsletter/{newletter_id}","NewsletterController@updateNewsletter");

		Route::delete("/admin/newsletter/delete/{newletter_id}","NewsletterController@deleteNewsletter");
		
		Route::get("/admin/send-newsletter/{newletter_id}","NewsletterController@distributeNewsLetters");
});