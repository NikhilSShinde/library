<?php
Route::group(array('module'=>'SocialConnect','namespace' => 'App\PiplModules\SocialConnect\Controllers','middleware'=>'web'), function() {
    //Your routes belong to this module.
	
	Route::get('/auth/facebook','SocialConnectController@redirectToFacebook');
	Route::get('/auth/facebook/callback','SocialConnectController@handleFacebookCallback');

	Route::get('/auth/twitter','SocialConnectController@redirectToTwitter');
	Route::get('/auth/twitter/callback','SocialConnectController@handleTwitterCallback');

	Route::get('/auth/google','SocialConnectController@redirectToGoogle');
	Route::get('/auth/google/callback','SocialConnectController@handleGoogleCallback');

	Route::get('/auth/instagram','SocialConnectController@redirectToInstagram');
	Route::get('/auth/instagram/callback','SocialConnectController@handleInstagramCallback');

	Route::get('/redirect-social/{social_type}','SocialConnectController@socialMessage');
	
});
